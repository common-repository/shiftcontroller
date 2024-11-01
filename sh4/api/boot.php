<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class SH4_Api_Boot
{
	public function __construct(
		HC3_Hooks $hooks,
		HC3_Router $router,
		HC3_Acl $acl,
		SH4_Api_Rest	$rest,
		SH4_Api_Api		$api
		)
	{
		$hooks
			->add( 'sh4/app/html/view/admin::menu::after', function( $ret ){
				$ret['81-admin/api'] = array( 'admin/api', '__REST API__' );
				return $ret;
			})
			;

		$router
			->register( 'get:admin/api', array('SH4_Api_Html_Admin', 'render') )
			->register( 'post:admin/api', array('SH4_Api_Html_Admin', 'post') )
			;

		register_setting( 'sh4-rest', 'sh4-rest_auth_code' );
		register_setting( 'sh4-rest', 'sh4-rest_enabled' );

		$this->initOption();
	}

	public function initOption()
	{
		$authOptionName = 'sh4-rest_auth_code';
		$v = get_option( $authOptionName, '' );

		if( ! strlen($v) ){
			$salt = '123456789';
			$len = 12;

			$v = array();
			$i = 1;
			while ( $i <= $len ){
				$num = rand() % strlen($salt);
				$tmp = substr($salt, $num, 1);
				$v[] = $tmp;
				$i++;
			}
			shuffle( $v );
			$v = join( '', $v );

			update_option( $authOptionName, $v );
		}

		$enabledOptionName = 'sh4-rest_enabled';
		$v = get_option( $enabledOptionName, 1 );
	}
}