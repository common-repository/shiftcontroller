<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class SH4_Employees_Boot
{
	public function __construct(
		SH4_Employees_Migration $migration,
		HC3_Hooks $hooks,
		HC3_Router $router
	)
	{
		$migration->up();

		$hooks
			->add( 'sh4/app/html/view/admin::menu::after', function( $ret ){
				$ret['12-employees'] = array( array('admin/employees', array('status' => NULL, 'calendar' => NULL)), '__Employees__' );
				return $ret;
			})
			;

		$router
			->register( 'get:admin/employees', array('SH4_Employees_Html_Admin_View_Index', 'render') )
			->register( 'post:admin/employees', array('SH4_Employees_Html_Admin_Controller_Index', 'execute') )

			->register( 'get:admin/employees/new', array('SH4_Employees_Html_Admin_View_New', 'render') )
			->register( 'post:admin/employees/new', array('SH4_Employees_Html_Admin_Controller_New', 'execute') )

			->register( 'get:admin/employees/sort', array('SH4_Employees_Html_Admin_View_Sort', 'get') )
			->register( 'post:admin/employees/sort', array('SH4_Employees_Html_Admin_View_Sort', 'post') )

			->register( 'get:admin/employees/settings', array('SH4_Employees_Html_Admin_View_Settings', 'get') )
			->register( 'post:admin/employees/settings', array('SH4_Employees_Html_Admin_View_Settings', 'post') )

			->register( 'get:admin/employees/importwp', array('SH4_Employees_Html_Admin_View_ImportWp', 'get') )
			->register( 'post:admin/employees/importwp', array('SH4_Employees_Html_Admin_View_ImportWp', 'post') )

			->register( 'post:admin/employees/{id}/archive', array('SH4_Employees_Html_Admin_Controller_Archive', 'execute') )
			->register( 'post:admin/employees/{id}/restore', array('SH4_Employees_Html_Admin_Controller_Restore', 'execute') )

			->register( 'get:admin/employees/{id}/delete', array('SH4_Employees_Html_Admin_View_Delete', 'render') )
			->register( 'post:admin/employees/{id}/delete', array('SH4_Employees_Html_Admin_Controller_Delete', 'execute') )

			->register( 'get:admin/employees/{id}', array('SH4_Employees_Html_Admin_View_Edit', 'render') )
			->register( 'post:admin/employees/{id}', array('SH4_Employees_Html_Admin_Controller_Edit', 'execute') )

			->register( 'get:admin/employees/{id}/user', array('SH4_Employees_Html_Admin_View_User', 'render') )
			->register( 'post:admin/employees/{id}/user/{userid}', array('SH4_Employees_Html_Admin_Controller_User', 'execute') )

			->register( 'get:admin/employees/{id}/calendars', array('SH4_Employees_Html_Admin_View_Calendars', 'render') )
			->register( 'post:admin/employees/{id}/calendars', array('SH4_Employees_Html_Admin_Controller_Calendars', 'execute') )
			;
	}
}