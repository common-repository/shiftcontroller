<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
#[AllowDynamicProperties]
class SH4_Notifications_Html_Admin_Controller_Edit
{
	public function __construct(
		HC3_Hooks $hooks,
		HC3_Post $post,

		SH4_Calendars_Query $calendarsQuery,
		SH4_Notifications_Service $notificationsService
		)
	{
		$this->post = $hooks->wrap($post);

		$this->calendarsQuery = $hooks->wrap( $calendarsQuery );
		$this->notificationsService = $hooks->wrap( $notificationsService );
		$this->self = $hooks->wrap($this);
	}

	public function execute( $calendarId, $notificationId )
	{
		$calendar = $this->calendarsQuery->findById( $calendarId );

		$subject = $this->post->get('subject');
		$body = $this->post->get('body');

		$template = join( "\n", array($subject, $body) );

		$this->notificationsService->setTemplate( $calendar, $notificationId, $template );

		$to = 'admin/notifications/' . $calendarId;
		$return = array( $to, '__Notification Updated__' );
		return $return;
	}

	public function executeReset( $calendarId, $notificationId )
	{
		$calendar = $this->calendarsQuery->findById( $calendarId );

		$template = NULL;
		$this->notificationsService->setTemplate( $calendar, $notificationId, $template );

		$to = 'admin/notifications/' . $calendarId;
		$return = array( $to, '__Notification Updated__' );
		return $return;
	}
}