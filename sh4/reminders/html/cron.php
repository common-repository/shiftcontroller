<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
#[AllowDynamicProperties]
class SH4_Reminders_Html_Cron
{
	public function __construct(
		HC3_Time $t,
		HC3_Settings $settings,

		SH4_Reminders_Query		$remindersQuery,
		SH4_Reminders_Command	$remindersCommand,
		SH4_Reminders_CommandSms $remindersCommandSms,

		HC3_Hooks $hooks
		)
	{
		$this->t = $t;
		$this->settings = $hooks->wrap( $settings );

		$this->remindersQuery = $hooks->wrap( $remindersQuery );
		$this->remindersCommand = $hooks->wrap( $remindersCommand );
		$this->remindersCommandSms = $hooks->wrap( $remindersCommandSms );

		$this->self = $hooks->wrap( $this );
	}

	public function run()
	{
		$date = $this->t->setNow()->modify('+1 day')->setStartDay()->formatDateDb();

		$sendDaily = $this->settings->get( 'reminders_daily' );
		$sendWeekly = $this->settings->get( 'reminders_weekly' );
		$sendMonthly = $this->settings->get( 'reminders_monthly' );

		$sendDailySms = $this->settings->get( 'reminders_daily_sms' );

	// daily
		if( $sendDaily ){
			$date = $this->t->setNow()->modify('+1 day')->setStartDay()->formatDateDb();
			$range = SH4_Reminders_Model::RANGE_DAILY;
			$reminders = $this->remindersQuery->find( $range, $date );
			foreach( $reminders as $reminder ){
				if( $reminder->log ) continue;
				$this->remindersCommand->send( $reminder );
			}
		}

	// daily sms
		if( $sendDailySms ){
			$date = $this->t->setNow()->modify('+1 day')->setStartDay()->formatDateDb();
			$range = SH4_Reminders_Model::RANGE_DAILY_SMS;
			$reminders = $this->remindersQuery->find( $range, $date );
			foreach( $reminders as $reminder ){
				if( $reminder->log ) continue;
				$this->remindersCommandSms->send( $reminder );
			}
		}

	// weekly
		if( $sendWeekly ){
			$date = $this->t->setNow()->modify('+1 week')->setStartWeek()->formatDateDb();
			$range = SH4_Reminders_Model::RANGE_WEEKLY;
			$reminders = $this->remindersQuery->find( $range, $date );
			foreach( $reminders as $reminder ){
				if( $reminder->log ) continue;
				$this->remindersCommand->send( $reminder );
			}
		}

	// monthly
		if( $sendMonthly ){
			$date = $this->t->setNow()->modify('+1 month')->setStartMonth()->formatDateDb();
			$range = SH4_Reminders_Model::RANGE_MONTHLY;
			$reminders = $this->remindersQuery->find( $range, $date );
			foreach( $reminders as $reminder ){
				if( $reminder->log ) continue;
				$this->remindersCommand->send( $reminder );
			}
		}
	}
}