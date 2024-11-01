<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
#[AllowDynamicProperties]
class SH4_Notifications_Html_Admin_Controller_Enable
{
	public function __construct(
		HC3_Hooks $hooks,
		HC3_Post $post,

		SH4_Calendars_Query $calendarsQuery,
		SH4_Notifications_Service $notificationsService
		)
	{
		$this->calendarsQuery = $hooks->wrap( $calendarsQuery );
		$this->notificationsService = $hooks->wrap( $notificationsService );
	}

	public function execute( $calendarId, $notificationId )
	{
		$calendar = $this->calendarsQuery->findById( $calendarId );
		$this->notificationsService->setOn( $calendar, $notificationId );

		$to = '-referrer-';
		$return = array( $to, '__Notification Enabled__' );
		return $return;
	}
}