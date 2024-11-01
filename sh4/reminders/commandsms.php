<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
#[AllowDynamicProperties]
class SH4_Reminders_CommandSms
{
	public function __construct(
		HC3_Settings $settings,
		HC3_Time $t,
		HC3_Translate $translate,

		SH4_Reminders_Sms $sms,

		SH4_Reminders_QueryLog		$remindersQueryLog,
		SH4_Reminders_CommandLog	$remindersCommandLog,

		HC3_Notificator $notificator,
		SH4_Notifications_Service  $notificationsService,
		SH4_Notifications_Template $notificationsTemplate,
		HC3_Hooks $hooks
	)
	{
		$this->t = $t;
		$this->translate = $translate;
		$this->settings = $hooks->wrap( $settings );

		$this->sms = $hooks->wrap( $sms );

		$this->remindersQueryLog = $hooks->wrap( $remindersQueryLog );
		$this->remindersCommandLog = $hooks->wrap( $remindersCommandLog );

		$this->notificationsService = $hooks->wrap( $notificationsService );
		$this->notificationsTemplate = $hooks->wrap( $notificationsTemplate );
		$this->notificator = $notificator;
	}

	public function send( SH4_Reminders_Model $model )
	{
	// already sent?
		if( $model->log ) return;

		$employee = $model->employee;
		$toNumber = $this->sms->getEmployeePhone( $employee->getId() );

		if( ! $toNumber ){
			$user = $model->user;
			if( $user ){
				$toNumber = $this->sms->getUserPhone( $user->getId() );
			}
		}

		if( ! $toNumber ){
			return;
		}

		$shifts = $model->shifts;

	// do send
		$wasOff = $this->notificator->isOn() ? FALSE : TRUE;
		if( $wasOff ){
			$this->notificator->setOn();
		}

		$templateSubject = $this->settings->get( 'reminders_template_subject_dailysms' );

		if( $model::RANGE_DAILY_SMS == $model->range ){
			$dateLabel = $this->t->setDateDb( $model->date )->formatDateWithWeekday();
		}

		$templateSubject = str_ireplace( '{DATELABEL}', $dateLabel, $templateSubject );

		$employeeId = $model->employee->getId();
		$employeeTitle = $model->employee->getTitle();

		$templateSubject = str_ireplace( '{EMPLOYEE_ID}', $employeeId, $templateSubject );
		$templateSubject = str_ireplace( '{EMPLOYEE_NAME}', $employeeTitle, $templateSubject );

		foreach( $shifts as $shift ){
			$msg = $templateSubject;
			$msg = $this->notificationsTemplate->parse( $msg, $shift );
			$msg = $this->translate->translate( $msg );

			$this->sms->send( $toNumber, $msg );
		}

		if( $wasOff ){
			$this->notificator->setOff();
		}

		$modelLog = new SH4_Reminders_ModelLog;
		$modelLog->range = $model->range;
		$modelLog->date = $model->date;
		$modelLog->employee_id = $model->employee->getId();

		$this->remindersCommandLog->create( $modelLog );
	}
}