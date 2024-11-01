<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class SH4_Conf_Boot
{
	public function __construct(
		SH4_Conf_Migration $migration,
		HC3_Hooks $hooks,
		HC3_Router $router
	)
	{
		$migration->up();

		$hooks
			->add( 'sh4/app/html/view/admin::menu::after', function( $ret ){
				$ret['21-admin/datetime'] = array( 'admin/conf/datetime', '__Date and Time__' );
				$ret['22-admin/conflicts'] = array( 'admin/conf/conflicts', '__Conflicts__' );
				$ret['23-admin/shiftstatus'] = array( 'admin/conf/shiftstatus', '__Shift Status__' );
				$ret['99-admin/about'] = array( 'admin/about', '__About__' );
				return $ret;
				})
			;

		$router
			->register( 'get:admin/conf/datetime', array('SH4_Conf_Html_Admin_View_Datetime', 'render') )
			->register( 'post:admin/conf/datetime', array('SH4_Conf_Html_Admin_Controller_Datetime', 'execute') )

			->register( 'get:admin/conf/conflicts', array('SH4_Conf_Html_Admin_View_Conflicts', 'render') )
			->register( 'post:admin/conf/conflicts', array('SH4_Conf_Html_Admin_Controller_Conflicts', 'execute') )

			->register( 'get:admin/conf/shiftstatus', array('SH4_Conf_Html_Admin_View_ShiftStatus', 'render') )
			->register( 'post:admin/conf/shiftstatus', array('SH4_Conf_Html_Admin_Controller_ShiftStatus', 'execute') )
			;
	}
}