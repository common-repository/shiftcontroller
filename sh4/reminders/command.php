<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
interface SH4_Reminders_Command_
{
	public function send( SH4_Reminders_Model $model );
}

#[AllowDynamicProperties]
class SH4_Reminders_Command implements SH4_Reminders_Command_
{
	public function __construct(
		HC3_Settings $settings,
		HC3_Time $t,

		SH4_Reminders_QueryLog		$remindersQueryLog,
		SH4_Reminders_CommandLog	$remindersCommandLog,

		HC3_Notificator $notificator,
		SH4_Notifications_Service  $notificationsService,
		SH4_Notifications_Template $notificationsTemplate,
		HC3_Hooks $hooks
	)
	{
		$this->t = $t;
		$this->settings = $hooks->wrap( $settings );

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

		$user = $model->user;
		$shifts = $model->shifts;

	// do send
		$wasOff = $this->notificator->isOn() ? FALSE : TRUE;
		if( $wasOff ){
			$this->notificator->setOn();
		}

		$templateSubjectDaily = $this->settings->get( 'reminders_template_subject_daily' );
		$templateSubjectWeekly = $this->settings->get( 'reminders_template_subject_weekly' );
		$templateSubjectMonthly = $this->settings->get( 'reminders_template_subject_monthly' );

		$templateSubject = NULL;

		if( $model::RANGE_DAILY == $model->range ){
			$templateSubject = $templateSubjectDaily;
			$dateLabel = $this->t->setDateDb( $model->date )->formatDateWithWeekday();
		}

		if( $model::RANGE_WEEKLY == $model->range ){
			$templateSubject = $templateSubjectWeekly;
			$startDate = $this->t->setDateDb( $model->date )->setStartWeek()->formatDateDb();
			$endDate = $this->t->modify('+1 week')->modify('-1 day')->formatDateDb();
			$dateLabel = $this->t->formatDateRange( $startDate, $endDate );
		}

		if( $model::RANGE_MONTHLY == $model->range ){
			$templateSubject = $templateSubjectMonthly;
			$startDate = $this->t->setDateDb( $model->date )->setStartMonth()->formatDateDb();
			$endDate = $this->t->modify('+1 month')->modify('-1 day')->formatDateDb();
			$dateLabel = $this->t->formatDateRange( $startDate, $endDate );
		}

		$templateSubject = str_ireplace( '{DATELABEL}', $dateLabel, $templateSubject );

		$employeeId = $model->employee->getId();
		$employeeTitle = $model->employee->getTitle();

		$templateSubject = str_ireplace( '{EMPLOYEE_ID}', $employeeId, $templateSubject );
		$templateSubject = str_ireplace( '{EMPLOYEE_NAME}', $employeeTitle, $templateSubject );

		$msg = array();
		$msg[] = $templateSubject;

		foreach( $shifts as $shift ){
			$calendar = $shift->getCalendar();
			$oneTemplate = $this->notificationsService->getTemplate( $calendar, 'email_employee_publish' );

		// remove subject
			$oneTemplate = explode( "\n", $oneTemplate );
			array_shift( $oneTemplate );
			$oneTemplate = join( "\n", $oneTemplate );

			$msg[] = $this->notificationsTemplate->parse( $oneTemplate, $shift );
			$msg[] = '';
		}
		$msg = join( "\n", $msg );

		$this->notificator
			->queue( $user, 'email_employee_reminder', $msg )
			;

		$this->notificator->send();

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