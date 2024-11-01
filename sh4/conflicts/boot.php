<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class SH4_Conflicts_Boot
{
	public function __construct(
		HC3_Router $router,
		HC3_Acl $acl
		)
	{
		$router
			->register( 'get:conflicts/{shift}/{calendar}/{start}/{end}/{employee}', array('SH4_Conflicts_View_Index', 'render') )
			;

		$acl
			->register( 'get:shifts/{id}/conflicts', array('SH4_Conflicts_Acl', 'checkView') )
			;
	}
}