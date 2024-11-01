<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class SH4_Upgrade3_Boot
{
	public function __construct(
		HC3_Hooks $hooks,
		HC3_Router $router
	)
	{
		$router
			->register( 'get:upgrade3', array('SH4_Upgrade3_Controller', 'get') )
			->register( 'get:upgrade3-1', array('SH4_Upgrade3_Controller', 'get1') )
			->register( 'get:upgrade3-2', array('SH4_Upgrade3_Controller', 'get2') )
			->register( 'get:upgrade3-3', array('SH4_Upgrade3_Controller', 'get3') )
			->register( 'get:upgrade3-4', array('SH4_Upgrade3_Controller', 'get4') )
			;
	}
}