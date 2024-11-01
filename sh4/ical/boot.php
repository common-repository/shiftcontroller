<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class SH4_Ical_Boot
{
	public function __construct(
		HC3_Acl $acl,
		HC3_Router $router,
		HC3_Hooks $hooks
	)
	{
		$hooks
			->add( 'sh4/users/html/user/view/profile::menu::after', function( $return ){
				$return['ical'] = array( 'user/profile/ical', '__iCal Sync__' );
				return $return;
				})
			;

		$router
			->register( 'get:ical/{token}', array('SH4_Ical_View', 'render') )
			->register( 'get:ical/{token}/{calendar}', array('SH4_Ical_View', 'render') )
			->register( 'get:ical/{token}/{calendar}/{employee}', array('SH4_Ical_View', 'render') )

		// v3 compat
			->register( 'get:ical/export/{token}', array('SH4_Ical_View', 'render') )
			->register( 'get:ical/export/{token}/{calendar}', array('SH4_Ical_View', 'render') )

			->register( 'get:user/profile/ical', array('SH4_Ical_Html_User_View_Profile_Ical', 'render') )
			->register( 'post:user/profile/ical', array('SH4_Ical_Html_User_View_Profile_Ical', 'post') )
			;

		$acl
			->register( 'get:user/profile/ical', array('SH4_App_Acl', 'checkUserProfile') )
			;
	}
}