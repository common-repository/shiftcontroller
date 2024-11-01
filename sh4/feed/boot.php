<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class SH4_Feed_Boot
{
	public function __construct(
		HC3_Hooks $hooks,
		HC3_Router $router
	)
	{
		$hooks
			->add( 'sh4/app/html/view/admin::menu::after', function( $ret ){
				$ret['41-admin/feed'] = array( 'admin/feed', '__Shifts Feed__' );
				return $ret;
			})
			;

		$router
			->register( 'get:feed/{token}', array('SH4_Feed_View', 'render') )
			->register( 'get:feed/{token}/{calendar}', array('SH4_Feed_View', 'render') )
			->register( 'get:feed/{token}/{calendar}/{employee}', array('SH4_Feed_View', 'render') )
			->register( 'get:feed/{token}/{calendar}/{employee}/{from}/{to}', array('SH4_Feed_View', 'render') )

			->register( 'get:json/{token}', array('SH4_Feed_View', 'renderJson') )
			->register( 'get:json/{token}/{calendar}', array('SH4_Feed_View', 'renderJson') )
			->register( 'get:json/{token}/{calendar}/{employee}', array('SH4_Feed_View', 'renderJson') )
			->register( 'get:json/{token}/{calendar}/{employee}/{from}/{to}', array('SH4_Feed_View', 'renderJson') )

			->register( 'get:admin/feed', array('SH4_Feed_Html_Admin_Feed', 'render') )
			;
	}
}