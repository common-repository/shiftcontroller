<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class SH4_Reminders_Boot
{
	public function __construct(
		HC3_Dic $dic,
		HC3_Settings $settings,
		HC3_Hooks $hooks,
		HC3_Router $router,
		HC3_Acl $acl
	)
	{
		$settings
			->init( 'reminders_setup', 0 )
			->init( 'reminders_daily', 0 )
			->init( 'reminders_daily_sms', 0 )
			->init( 'reminders_weekly', 0 )
			->init( 'reminders_monthly', 0 )
			->init( 'reminders_include', 'all' )

			->init( 'reminders_template_subject_daily', "__Tomorrow Shifts__: {DATELABEL}" )
			->init( 'reminders_template_subject_weekly', "__Next Week Shifts__: {DATELABEL}" )
			->init( 'reminders_template_subject_monthly', "__Next Month Shifts__: {DATELABEL}" )
			->init( 'reminders_template_subject_dailysms', "__Shift Reminder__: {CALENDAR} {DATETIME} {EMPLOYEE}" )
			;

		$router
			->register( 'get:admin/reminders', array('SH4_Reminders_Html_Admin', 'render') )
			->register( 'post:admin/reminders', array('SH4_Reminders_Html_Admin', 'post') )

			->register( 'get:admin/reminders/review', array('SH4_Reminders_Html_Admin_Review', 'render') )
			->register( 'post:admin/reminders/review', array('SH4_Reminders_Html_Admin_Review', 'post') )
			->register( 'post:admin/reminders/review/send', array('SH4_Reminders_Html_Admin_Review', 'postSend') )
			->register( 'post:admin/reminders/review/clear', array('SH4_Reminders_Html_Admin_Review', 'postClear') )

			->register( 'get:admin/reminders/templates', array('SH4_Reminders_Html_Admin_Templates', 'render') )
			->register( 'post:admin/reminders/templates', array('SH4_Reminders_Html_Admin_Templates', 'post') )
			->register( 'post:admin/reminders/templates/reset', array('SH4_Reminders_Html_Admin_Templates', 'postReset') )

			->register( 'get:admin/reminders/sms', array('SH4_Reminders_Html_Admin_Sms', 'render') )
			->register( 'post:admin/reminders/sms', array('SH4_Reminders_Html_Admin_Sms', 'post') )
			;

		$hooks
			->add( 'sh4/app/html/view/admin::menu::after', function( $ret ){
				$ret['41-admin/reminders'] = array( 'admin/reminders', '__Reminders__' );
				return $ret;
				})
			;

		$hooks
			->add( 'hc3/cron::run::after', array('SH4_Reminders_Html_Cron', 'run') )
			;
	}
}