<?php
/*
 * Plugin Name: ShiftController
 * Plugin URI: https://www.shiftcontroller.com/
 * Description: Staff scheduling plugin
 * Version: 4.9.69
 * Author: plainware.com
 * Author URI: https://www.shiftcontroller.com/
 * Text Domain: shiftcontroller
 * Domain Path: /languages/
*/

define( 'SH4_VERSION', 4969 );

if (! defined('ABSPATH')) exit; // Exit if accessed directly

if ( version_compare( PHP_VERSION, '5.3', '<' ) ) {
	add_action( 'admin_notices',
		create_function( '',
			"echo '<div class=\"error\"><p>" .
			__('ShiftController requires PHP 5.3 to function properly. Please upgrade PHP or deactivate ShiftController.', 'shiftcontroller') ."</p></div>';"
			)
	);
	return;
}

if( file_exists(dirname(__FILE__) . '/config.php') ){
	$conf = include( dirname(__FILE__) . '/config.php' );
}

$hc3path = defined('HC3_DEV_INSTALL') ? HC3_DEV_INSTALL : dirname(__FILE__) . '/hc3';
include_once( $hc3path . '/_wordpress/abstract/plugin.php' );

class ShiftController4 extends HC3_Abstract_Plugin
{
	public function __construct()
	{
		// $this->slug = 'shiftcontroller';
		$this->translate = 'shiftcontroller';
		$this->slug = 'shiftcontroller4';
		$this->label = 'Shift Controller';
		$this->prfx = 'sh4';
		$this->menuIcon = 'dashicons-calendar';
		// $this->requireCap = 'manage_options';

		$this->modules = array(
			'conf',
			'schedule',
			'employees',
			'calendars',
			'shifttypes',
			'shifts',
			'conflicts',
			'new',
			'users',
			'notifications',
			'ical',
			'feed',
			'app',
			// 'upgrade3',
			'platform',
			'api',
			'reminders',
			'promo',

// 'repeat',
// 'bulk'
// 'pickup'
			);

if( defined('HC3_DEV_INSTALL') ){
	$this->modules[] = 'demo';
}

		parent::__construct( __FILE__ );

		add_action(	'init', array($this, 'addRoles') );
		add_shortcode( 'shiftcontroller4', array($this, 'shortcode') );
		add_action(	'template_redirect', array($this, 'startFrontSession') );
	}

	public function adminInit()
	{
		if( is_admin() ){
			$actionUrl = get_admin_url() . 'admin.php?page=' . $this->slug;
			$uriAction = $this->dic->make('HC3_UriAction');
			$uriAction->fromUrl( $actionUrl );
		}

		parent::adminInit();
	}

	public function getOurShortcodeAtts()
	{
		$ret = false;

		$currentUrl = $_SERVER['REQUEST_URI'];
		$currentPageId = url_to_postid( $currentUrl );
		if( $currentPageId ){
			$shortcode = 'shiftcontroller4';
			$pages = HC3_Functions::wpGetIdByShortcode( $shortcode );
			if( isset($pages[$currentPageId]) ){
				$ret = $pages[ $currentPageId ];
			}
		}

		return $ret;
	}

// intercepts if in the front page our slug is given then it's ours
	public function intercept()
	{
		$shortcodeAtts = null;

// compatibility with WooCommerce Availability Scheduler 12.4
global $was_post_remover;
if( $was_post_remover ){
	$shortcodeAtts = $this->getOurShortcodeAtts();
	if( false !== $shortcodeAtts ){
		remove_action( 'posts_request', array($was_post_remover,'AS_check_if_unhide_products_and_update_cart') );
	}
}

		if( ! $this->isIntercepted() ){
			return;
		}

	// it it points to a page with our shortcode then init request with shortcode atts
		$route = '';
		if( null === $shortcodeAtts ){
			$shortcodeAtts = $this->getOurShortcodeAtts();
			if( false !== $shortcodeAtts ){
				$route = $this->initRequestByAtts( $shortcodeAtts );
			}
		}

		if( is_admin() ){
			$actionUrl = get_admin_url() . 'admin.php?page=' . $this->slug;
			$uriAction = $this->dic->make('HC3_UriAction');
			$uriAction->fromUrl( $actionUrl );
		}

		// HC3_Session::instance();

		$this->actionResult = $this->handleRequest( $route );
		echo $this->render();
		exit;
	}

	public function startFrontSession()
	{
		if( is_admin() ) return;

		$shortcode = 'shiftcontroller4';
		$pages = HC3_Functions::wpGetIdByShortcode( $shortcode );
		if( ! $pages ) return;

		$currentPageId = get_queried_object_id();
		if( ! isset($pages[$currentPageId]) ) return;

		HC3_Session::instance();
	}

	public function addRoles()
	{
		$adminRole = 'sh4_admin';
		$r = get_role( $adminRole );
		if( $r ){
			return;
		}

		add_role(
			$adminRole,
			'ShiftController Administrator',
			array(
				'read' => TRUE,
				)
			);

		$capabilities = array(
			'manage_sh4',
		);

		global $wp_roles;
		foreach( $capabilities as $cap ){
			$wp_roles->add_cap( $adminRole, $cap );
			$wp_roles->add_cap( 'editor', $cap );
			$wp_roles->add_cap( 'administrator', $cap );
		}
	}

	public function initRequestByAtts( $atts )
	{
		$route = 'schedule';

		$root = $this->root();
		$request = $root->make('HC3_Request');
		$slug = $request->getSlug();

		// $processParams = array( 'type', 'groupby', 'start', 'end', 'time', 'pickup', 'conflict' );
		// if( $slug != 'myschedule' ){
			// $processParams = array_merge( $processParams, array('calendar', 'employee', 'hideui') );
		// }
		$processParams = array( 'type', 'groupby', 'start', 'end', 'time', 'pickup', 'conflict', 'hideui' );
		if( $slug != 'myschedule' ){
			$processParams = array_merge( $processParams, array('calendar', 'employee') );
		}
		$arrayFor = array( 'calendar', 'employee', 'hideui' );

		$allowedRoutes = array('schedule', 'myschedule');

		foreach( $atts as $k => $v ){
			if( $k == 'route' ){
				if( in_array($v, $allowedRoutes) ){
					$route = $v;
				}
			}

			if( ! in_array($k, $processParams) ){
				continue;
			}

			if( in_array($k, $arrayFor) ){
				if( strpos($v, ',') ){
					$v = explode(',', $v);
					for( $ii = 0; $ii < count($v); $ii++ ){
						$v[$ii] = trim( $v[$ii] );
					}
				}
				else {
					$v = array($v);
				}
			}
			$request->initParam( $k, $v );
		}

		if( ('myschedule' == $route) && ('schedule' == $slug) ){
			$request->setSlug('myschedule');
		}

		return $route;
	}

	public function shortcode( $shortcodeAtts )
	{
		if( is_admin() OR hc_is_rest() ){
			$return = 'ShiftController shortcode is rendered in front end only.';
			return $return;
		}

		$route = 'schedule';

		if( $shortcodeAtts && is_array($shortcodeAtts) ){
			$route = $this->initRequestByAtts( $shortcodeAtts );
		}

		$this->actionResult = $this->handleRequest( $route );

		ob_start();
		echo $this->render();
		$return = ob_get_contents();
		ob_end_clean();
		return $return;
	}

	public function handleRequest( $defaultSlug = '' )
	{
		if( ! $defaultSlug ){
			$defaultSlug = 'schedule';
		}

		$return = parent::handleRequest( $defaultSlug );

		$root = $this->root();
		$enqueuer = $root->make('HC3_Enqueuer');
		$enqueuer
			->addScript('sh4', 'sh4/app/assets/js/sh4.js')
			;

		return $return;
	}
}

$hcsh4 = new ShiftController4();

if (!function_exists('hc_is_rest')) {
	/**
	* Checks if the current request is a WP REST API request.
	* 
	* Case #1: After WP_REST_Request initialisation
	* Case #2: Support "plain" permalink settings
	* Case #3: URL Path begins with wp-json/ (your REST prefix)
	*          Also supports WP installations in subfolders
	* 
	* @returns boolean
	* @author matzeeable
	*/
	function hc_is_rest() {
	  $prefix = rest_get_url_prefix( );
	  if (defined('REST_REQUEST') && REST_REQUEST // (#1)
			|| isset($_GET['rest_route']) // (#2)
				 && strpos( trim( $_GET['rest_route'], '\\/' ), $prefix , 0 ) === 0)
			return true;

	  // (#3)
	  $rest_url = wp_parse_url( site_url( $prefix ) );
	  $current_url = wp_parse_url( add_query_arg( array( ) ) );
	  return strpos( $current_url['path'], $rest_url['path'], 0 ) === 0;
	}
}