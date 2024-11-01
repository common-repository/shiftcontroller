<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class SH4_Users_Boot
{
	public function __construct(
		HC3_Hooks $hooks,
		HC3_Router $router
	)
	{
		$hooks
			->add( 'sh4/app/html/view/admin::menu::after', function( $ret ){
				$ret['31-admin/users'] = array( 'admin/users', '__Users__' );
				return $ret;
				})
			;

		$router
			->register( 'get:admin/users', array('SH4_Users_Html_Admin_View_Index', 'render') )

			->register( 'get:admin/users/{id}/employee', array('SH4_Users_Html_Admin_View_Employee', 'render') )
			->register( 'post:admin/users/{id}/employee/{employeeid}', array('SH4_Users_Html_Admin_Controller_Employee', 'execute') )

			->register( 'get:user/profile', array('SH4_Users_Html_User_View_Profile', 'render') )
			->register( 'get:user/profile/roles', array('SH4_Users_Html_User_View_Profile_Roles', 'render') )

			->register( 'get:login', array('SH4_Users_Html_Anon_View_Login', 'render') )
			;
	}
}