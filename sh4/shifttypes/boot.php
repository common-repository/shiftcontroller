<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class SH4_ShiftTypes_Boot
{
	public function __construct(
		SH4_ShiftTypes_Migration $migration,
		HC3_Settings $settings,
		HC3_Hooks $hooks,
		HC3_Router $router
	)
	{
		$migration->up();

		$settings
			->init( 'shifttypes_show_title', 1 )
			->init( 'shifttypes_default_duration', 4*60*60 )
			->init( 'shifttypes_nobreak', FALSE )
			;

		$hooks
			->add( 'sh4/app/html/view/admin::menu::after', function( $ret ){
				$ret['13-admin/shifttypes'] = array( 'admin/shifttypes', '__Shift Time__' );
				return $ret;
				})
			;

		$router
			->register( 'get:admin/shifttypes', array('SH4_ShiftTypes_Html_Admin_View_Index', 'render') )
			->register( 'post:admin/shifttypes', array('SH4_ShiftTypes_Html_Admin_Controller_Index', 'execute') )

			->register( 'get:admin/shifttypes/{id}/delete', array('SH4_ShiftTypes_Html_Admin_View_Delete', 'render') )
			->register( 'post:admin/shifttypes/{id}/delete', array('SH4_ShiftTypes_Html_Admin_Controller_Delete', 'execute') )

			->register( 'get:admin/shifttypes/{id}/edit', array('SH4_ShiftTypes_Html_Admin_View_Edit', 'render') )
			->register( 'post:admin/shifttypes/{id}/edit', array('SH4_ShiftTypes_Html_Admin_Controller_Edit', 'execute') )

			->register( 'get:admin/shifttypes/new/hours', array('SH4_ShiftTypes_Html_Admin_View_New', 'renderHours') )
			->register( 'get:admin/shifttypes/new/days', array('SH4_ShiftTypes_Html_Admin_View_New', 'renderDays') )
			->register( 'post:admin/shifttypes/new/hours', array('SH4_ShiftTypes_Html_Admin_Controller_New', 'executeHours') )
			->register( 'post:admin/shifttypes/new/days', array('SH4_ShiftTypes_Html_Admin_Controller_New', 'executeDays') )

			->register( 'get:admin/shifttypes/settings', array('SH4_ShiftTypes_Html_Admin_View_Settings', 'render') )
			->register( 'post:admin/shifttypes/settings', array('SH4_ShiftTypes_Html_Admin_Controller_Settings', 'execute') )
			;
	}
}