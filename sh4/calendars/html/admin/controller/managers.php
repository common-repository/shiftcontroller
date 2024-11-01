<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
#[AllowDynamicProperties]
class SH4_Calendars_Html_Admin_Controller_Managers
{
	public function __construct(
		HC3_Hooks $hooks,
		HC3_Users_Query $usersQuery,
		SH4_Calendars_Query $calendarsQuery,
		SH4_App_Command $appCommand
		)
	{
		$this->calendarsQuery = $hooks->wrap($calendarsQuery);
		$this->usersQuery = $hooks->wrap($usersQuery);
		$this->appCommand = $hooks->wrap($appCommand);
	}

	public function add( $calendarId, $userId )
	{
		$calendar = $this->calendarsQuery->findById( $calendarId );
		$user = $this->usersQuery->findById( $userId );

		$this->appCommand->addManagerToCalendar( $user, $calendar );

		$return = array( 'admin/calendars/' . $calendarId . '/managers', '__Manager Added To Calendar__' );
		return $return;
	}

	public function remove( $calendarId, $userId )
	{
		$calendar = $this->calendarsQuery->findById( $calendarId );
		$user = $this->usersQuery->findById( $userId );

		$this->appCommand->removeManagerFromCalendar( $user, $calendar );

		$return = array( '-referrer-', '__Manager Removed From Calendar__' );
		return $return;
	}

	public function addViewer( $calendarId, $userId )
	{
		$calendar = $this->calendarsQuery->findById( $calendarId );
		$user = $this->usersQuery->findById( $userId );

		$this->appCommand->addViewerToCalendar( $user, $calendar );

		$return = array( 'admin/calendars/' . $calendarId . '/managers', '__Viewer Added To Calendar__' );
		return $return;
	}

	public function removeViewer( $calendarId, $userId )
	{
		$calendar = $this->calendarsQuery->findById( $calendarId );
		$user = $this->usersQuery->findById( $userId );

		$this->appCommand->removeViewerFromCalendar( $user, $calendar );

		$return = array( '-referrer-', '__Viewer Removed From Calendar__' );
		return $return;
	}
}