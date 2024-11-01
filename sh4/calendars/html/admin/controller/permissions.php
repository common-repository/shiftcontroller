<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
#[AllowDynamicProperties]
class SH4_Calendars_Html_Admin_Controller_Permissions
{
	public function __construct(
		HC3_Hooks $hooks,
		HC3_Post $post,

		SH4_Calendars_Permissions $calendarsPermissions,
		SH4_Calendars_Query $calendarsQuery
		)
	{
		$this->post = $hooks->wrap( $post );

		$this->calendarsPermissions = $hooks->wrap( $calendarsPermissions );
		$this->calendarsQuery = $hooks->wrap( $calendarsQuery );
	}

	public function execute( $calendarId )
	{
		$calendar = $this->calendarsQuery->findById( $calendarId );

		$current = $this->calendarsPermissions->getAll( $calendar );
		foreach( $current as $key => $value ){
			$newValue = $this->post->get($key) ? 1 : 0;
			$this->calendarsPermissions->set( $calendar, $key, $newValue );
		}

		$return = array( 'admin/calendars/' . $calendarId . '/prm', '__Calendar Updated__' );
		return $return;
	}
}