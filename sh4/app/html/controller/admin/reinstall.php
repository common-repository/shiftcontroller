<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
#[AllowDynamicProperties]
class SH4_App_Html_Controller_Admin_Reinstall
{
	public function __construct(
		HC3_Hooks $hooks,
		HC3_Post $post,

		SH4_Shifts_Command $shiftsCommand,
		SH4_App_Command $appCommand
		)
	{
		$this->shiftsCommand = $hooks->wrap( $shiftsCommand );
		$this->appCommand = $hooks->wrap( $appCommand );
	}

	public function execute()
	{
		global $wpdb;
		$dbPrefix = $wpdb->prefix;
		$sql = 'DELETE FROM ' . $dbPrefix . 'posts WHERE post_type LIKE "sh4_%"';
		$wpdb->query( $sql );

		$this->appCommand->uninstall();

		$to = 'admin';
		$return = array( $to, '__Reinstalled__' );
		return $return;
	}

	public function executeShifts()
	{
		$this->shiftsCommand->deleteAll();

		$to = '';
		$return = array( $to, '__All Shifts Deleted__' );
		return $return;
	}
}