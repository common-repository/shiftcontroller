<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class SH4_Calendars_Boot
{
	public function __construct(
		SH4_Calendars_Migration $migration,
		HC3_Hooks $hooks,
		HC3_Router $router
	)
	{
		$migration->up();

		$hooks
			->add( 'sh4/app/html/view/admin::menu::after', function( $ret ){
				$ret['11-admin/calendars'] = array( 'admin/calendars', '__Calendars__' );
				return $ret;
			})
			;

		$router
			->register( 'get:admin/calendars', array('SH4_Calendars_Html_Admin_View_Index', 'render') )
			->register( 'post:admin/calendars', array('SH4_Calendars_Html_Admin_Controller_Index', 'execute') )

			->register( 'get:admin/calendars/new', array('SH4_Calendars_Html_Admin_View_New', 'render') )
			->register( 'post:admin/calendars/new', array('SH4_Calendars_Html_Admin_Controller_New', 'execute') )

			->register( 'get:admin/calendars/sort', array('SH4_Calendars_Html_Admin_View_Sort', 'get') )
			->register( 'post:admin/calendars/sort', array('SH4_Calendars_Html_Admin_View_Sort', 'post') )

			->register( 'post:admin/calendars/{id}/archive', array('SH4_Calendars_Html_Admin_Controller_Archive', 'execute') )
			->register( 'post:admin/calendars/{id}/restore', array('SH4_Calendars_Html_Admin_Controller_Restore', 'execute') )

			->register( 'get:admin/calendars/{id}/delete', array('SH4_Calendars_Html_Admin_View_Delete', 'render') )
			->register( 'post:admin/calendars/{id}/delete', array('SH4_Calendars_Html_Admin_Controller_Delete', 'execute') )

			->register( 'get:admin/calendars/{id}', array('SH4_Calendars_Html_Admin_View_Edit', 'render') )
			->register( 'post:admin/calendars/{id}', array('SH4_Calendars_Html_Admin_Controller_Edit', 'execute') )

			->register( 'get:admin/calendars/{id}/managers', array('SH4_Calendars_Html_Admin_View_Managers', 'render') )
			->register( 'post:admin/calendars/{id}/managers/{userid}/add', array('SH4_Calendars_Html_Admin_Controller_Managers', 'add') )
			->register( 'post:admin/calendars/{id}/managers/{userid}/remove', array('SH4_Calendars_Html_Admin_Controller_Managers', 'remove') )

			->register( 'post:admin/calendars/{id}/viewers/{userid}/add', array('SH4_Calendars_Html_Admin_Controller_Managers', 'addViewer') )
			->register( 'post:admin/calendars/{id}/viewers/{userid}/remove', array('SH4_Calendars_Html_Admin_Controller_Managers', 'removeViewer') )

			->register( 'get:admin/calendars/{id}/managers-new', array('SH4_Calendars_Html_Admin_View_ManagersNew', 'render') )

			->register( 'get:admin/calendars/{id}/employees', array('SH4_Calendars_Html_Admin_View_Employees', 'render') )
			->register( 'post:admin/calendars/{id}/employees', array('SH4_Calendars_Html_Admin_Controller_Employees', 'execute') )

			->register( 'get:admin/calendars/{id}/shifttypes', array('SH4_Calendars_Html_Admin_View_ShiftTypes', 'render') )
			->register( 'post:admin/calendars/{id}/shifttypes', array('SH4_Calendars_Html_Admin_Controller_ShiftTypes', 'execute') )

			->register( 'get:admin/calendars/{id}/shifttypes/{new}', array('SH4_Calendars_Html_Admin_View_ShiftTypes', 'render') )
			->register( 'post:admin/calendars/{id}/shifttypes/{new}', array('SH4_Calendars_Html_Admin_Controller_ShiftTypes', 'execute') )

			->register( 'get:admin/calendars/{id}/prm', array('SH4_Calendars_Html_Admin_View_Permissions', 'render') )
			->register( 'post:admin/calendars/{id}/prm', array('SH4_Calendars_Html_Admin_Controller_Permissions', 'execute') )
			;
	}
}