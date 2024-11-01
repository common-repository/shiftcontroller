<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
define( 'HC3_VERSION', 103 );

abstract class HC3_Abstract_Plugin
{
	public static $instance;

	public $translate;
	public $slug;
	public $label;
	public $prfx;
	public $prefix;

	protected $modules = array();
	public $dirs = array();
	public $libDirs = array();

	protected $dic = NULL;
	protected $actionResult = NULL;
	protected $hcs = 'hcs'; // get/post param to intercept
	protected $hcj = 'hcj'; // get/post param to intercept

	protected $file;
	protected $menuIcon = NULL;

	protected $requireCap = 'read';

	public function __construct( $file )
	{
		self::$instance = $this;

		$this->libDirs = array();
		$this->file = $file;

		$this->prefix = $this->prfx . '_';

		spl_autoload_register( array($this, 'autoload') );

		add_action(	'init', array($this, '_init') );
		add_action( 'init', array($this, 'intercept') );
		add_action( 'admin_init', array($this, 'adminInit') );
		add_action( 'admin_menu', array($this, 'adminMenu') );
		add_filter( 'parent_file', array($this, 'setCurrentAppMenu') );
		if( $this->isMeAdmin() ){
			add_action( 'admin_enqueue_scripts', array($this, 'scripts') );
		}
	}

	public function _init()
	{
		$pluginDir = dirname($this->file);

		$filter_name = $this->prefix . 'modules';
		$this->modules = apply_filters( $filter_name, $this->modules );

		$dir =trim( $this->prefix, '_' );
		$this->dirs = array( $pluginDir . '/' . $dir );

		$filter_name = $this->prefix . 'dirs';
		$this->dirs = apply_filters( $filter_name, $this->dirs );

		$profiler = new HC3_Profiler;
		$profiler->mark( 'total_start' );

		$dic = new HC3_Dic;
		$dic->bind( $profiler );

		$hooks = new HC3_Hooks( $dic, $this->slug, $this->prefix );
		$dic->bind( $hooks );

		$crudFactory = new HC3_CrudFactory( $this->prefix );
		$settings = new HC3_Settings( $this->prefix );

		$dic
			->bind( $crudFactory )
			// ->bind( $translate )
			->bind( $settings )
			;

		$lang = $settings->get('lang');

	// Polylang plugin
		if( function_exists('pll_current_language') ){
			$lang = pll_current_language( 'locale' );
		}

		// $langDomain = $this->translate;
		// $dir = dirname($this->file);
		// $langDir = plugin_basename($dir) . '/languages'; 
		// $langFullDir = $dir . '/languages';
		// load_plugin_textdomain( $langDomain, '', $langDir );

		$translate = new HC3_Translate( $this->translate, $pluginDir, $lang );
		$dic
			->bind( $translate )
			;

		reset( $this->modules );
		foreach( $this->modules as $moduleName ){
			$moduleBootClass = $this->prefix . $moduleName . '_Boot';
			$module = $dic->make( $moduleBootClass );
		}

		$uri = $dic->make('HC3_Uri');
		$currentUrl = parse_url( $uri->currentUrl() );
		$currentPort = isset( $currentUrl['port'] ) ? $currentUrl['port'] : null;

		$url = parse_url( site_url('/') );
// _print_r( $url );

		$baseUrl = $url['scheme'] . '://'. $url['host'];
		$basePort = isset( $url['port'] ) ? $url['port'] : null;

		if( $basePort && (80 != $basePort) && ($basePort == $currentPort) ){
			$baseUrl .= ':' . $basePort;
		}

		$baseUrl .= $url['path'];

		$actionUrl = (isset($url['query']) && $url['query']) ? '?' . $url['query'] . '&' : '?';
		$actionUrl .= $this->hcs . '=' . $this->prfx;
		$actionUrl = $baseUrl . $actionUrl;

		$uriAction = $dic->make('HC3_UriAction');
// echo "SETTING '$actionUrl'<br>";
		$uriAction->fromUrl( $actionUrl );

		$this->dic = $dic;

		$action_name = $this->prefix . 'init';
		do_action( $action_name, $this );

		add_filter( $this->slug, array($this, 'api'), 10, 4 );
	}

	public function api( $handler, $arg1 = NULL, $arg2 = NULL, $arg3 = NULL )
	{
		$return = NULL;

		$handler = trim( $handler );
		$handler = strtolower( $handler );
		if( FALSE === strpos($handler, '@') ){
			$method = '__invoke';
		}
		else {
			list( $handler, $method ) = explode( '@', $handler );
		}

		$handler = $this->dic->make( $handler );

		try {
			$args = array();
			if( NULL !== $arg1 ){
				$args[] = $arg1;
			}
			if( NULL !== $arg2 ){
				$args[] = $arg2;
			}
			if( NULL !== $arg3 ){
				$args[] = $arg3;
			}
			$return = call_user_func_array( array($handler, $method), $args );
		}
		catch( Exception $e ){
		}

		return $return;
	}

	public function adminMenu()
	{
		$mainMenuSlug = $this->slug;
		$mainLabel = get_site_option( $this->slug . '_menu_title' );

		if( ! strlen($mainLabel) ){
			$mainLabel = $this->label;
		}

		$page = add_menu_page(
			$mainLabel,
			$mainLabel,
			$this->requireCap,
			$mainMenuSlug,
			array($this, 'render'),
			$this->menuIcon,
			30
			);

	// submenu
		$root = $this->root();

		$topmenu = $root->make('HC3_Ui_Topmenu');
		$uri = $root->make('HC3_Uri');
		$mainHref = get_admin_url() . 'admin.php?page=' . $this->slug;
		$uri->fromUrl( $mainHref );

		$ui = $root->make('HC3_Ui');
		$translate = $root->make('HC3_Translate');
		$filter = $root->make('HC3_Ui_Filter');

		$menuItems = $topmenu->getChildren();

		$mySubmenuCount = 0;
		global $submenu;

		foreach( $menuItems as $menuItem ){
			list( $slug, $label ) = $menuItem;

			$ahref = $ui->makeAhref( $slug, $label );
			$ahref = $filter->filter( $ahref );

			$strAhref = '' . $ahref;
			if( ! strlen($strAhref) ){
				continue;
			}

			if( $uri->isFullUrl($slug) ){
				$ahref->newWindow();
			}

			$href = $ahref->getAttr('href');
			$label = $translate->translate( $label );

			$pageTitle = $label;
			$menuTitle = strip_tags( $label );

			remove_submenu_page( $mainMenuSlug, $href );

			$childMenuSlug = $mainMenuSlug . '-' . ($mySubmenuCount + 1);

			$ret = add_submenu_page(
				$mainMenuSlug,		// parent
				$pageTitle,			// page_title
				$menuTitle,			// menu_title
				$this->requireCap,	// capability
				$childMenuSlug,		// menu_slug
				array($this, 'render')
				// '__return_null'
				);

			if( ! array_key_exists($mainMenuSlug, $submenu) ){
				continue;
			}

			$mySubmenu = $submenu[$mainMenuSlug];
			$mySubmenuIds = array_keys($mySubmenu);
			$mySubmenuId = array_pop($mySubmenuIds);

			$submenu[$mainMenuSlug][$mySubmenuId][2] = $href;
			$mySubmenuCount++;
		}

		if( isset($submenu[$mainMenuSlug][0]) && ($submenu[$mainMenuSlug][0][2] == $mainMenuSlug) ){
			unset($submenu[$mainMenuSlug][0]);
		}

		if( ! $mySubmenuCount ){
			remove_menu_page( $mainMenuSlug );
		}
	}

	public function setCurrentAppMenu( $parent_file )
	{
		global $submenu_file, $current_screen, $pagenow;

		$menuSlug = $this->slug;
		$typePrefix = $this->prefix;

		$my = FALSE;
		if( $current_screen->base == 'toplevel_page_' . $menuSlug ){
			$my = TRUE;
		}

		if( substr($current_screen->post_type, 0, strlen($typePrefix)) == $typePrefix ){
			$my = TRUE;
		}

		if( ! $my ){
			return $parent_file;
		}

		switch( $pagenow ){
			case 'post-new.php':
				$submenu_file = 'edit.php?post_type=' . $current_screen->post_type;
				break;

			case 'edit-tags.php':
				$submenu_file = 'edit-tags.php?taxonomy=' . $current_screen->taxonomy . '&post_type=' . $current_screen->post_type;
				break;

			case 'admin.php':
				$root = $this->root();

				$uri = $root->make('HC3_Uri');
				$currentUrl = $uri->currentUrl();
				$shortCurrentUrl = basename( $currentUrl );

				global $submenu;
				if( array_key_exists($menuSlug, $submenu) ){
					foreach( $submenu[$menuSlug] as $sbm ){
						if( substr($sbm[2], -strlen($shortCurrentUrl)) == $shortCurrentUrl ){
							$submenu_file = $sbm[2];
							break;
						}
					}
				}
				break;

			default:
				break;
		}

		$parent_file = $menuSlug;
		return $parent_file;
	}

	public function scripts()
	{
		$root = $this->root();
		$enqueuer = $root->make('HC3_Enqueuer');
	}

	public function handleRequest( $defaultSlug = '' )
	{
		$result = NULL;
		$root = $this->root();

		$profiler = $root->make('HC3_Profiler');

		$profiler->mark('action_start');

		$uri = $root->make('HC3_Uri');
		$uri->setAssetsPath( plugins_url('', $this->file) );

		// if( $this->isIntercepted() ){
			// $mainHref = get_admin_url() . 'admin.php?page=' . $this->slug;
			// $uri->fromUrl( $mainHref );
		// }

		$request = $root->make('HC3_Request');
		$csrf = $root->make('HC3_Csrf');
		$router = $root->make('HC3_Router');

		$session = HC3_Session::instance();

		$requestMethod = $request->getMethod();
		$requestParams = $request->getParams();
		$slug = $request->getSlug();

		$ajax = false;
		if( substr($slug, 0, strlen('ajax/')) == 'ajax/' ){
			$ajax = true;
		}
		else {
			$ajax = $request->isAjax() ? true : false;
		}

		if( ! $slug ){
			$slug = $defaultSlug;
		}

		$request->setRealSlug( $slug );

		$slug = $requestMethod . ':' . $slug;

// first acl
		$acl = $root->make('HC3_Acl');
		if( ! $acl->check($slug, $requestParams) ){
			// if not logged in then it may need to log in
			if( ! get_current_user_id() ){
				$returnTo = $uri->currentUrl();
				$to = wp_login_url( $returnTo );

				if( ! headers_sent() ){
					if( 0 && defined('HC3_PROFILER') && HC3_PROFILER ){
						$html = "REDIRECT<br><a href=\"$to\">$to</a>";
						echo $html;
						exit;
					}
					else {
						wp_redirect( $to );
					}
				}
				else {
					if( defined('HC3_PROFILER') && HC3_PROFILER ){
						$html = "REDIRECT<br><a href=\"$to\">$to</a>";
						echo $html;
						exit;
					}
					else {
						$html = "<META http-equiv=\"refresh\" content=\"0;URL=$to\">";
						echo $html;
						exit;
					}
				}
			}
			else {
				$result = 'not allowed';
				return $result;
			}
		}

		list( $handler, $args ) = $router->getHandlerArgs( $slug );

		if( 'post' == $requestMethod ){
			$csrf->checkInput();
		}

// echo "HANDLER = '$handler' FOR SLUG = '$slug'<br>";
		try {
			list( $handler, $method ) = is_array($handler) ? $handler : array( $handler, 'execute' );
// echo "HANDLER = $handler";
			if( $handler ){
				$handler = $root->make( $handler );
				$result = call_user_func_array( array($handler, $method), $args );
			}
			else {
				$result = "nothing to handle this request: '$slug'";
				$result = esc_html( $result );
			}
		}
		catch( HC3_ExceptionArray $e ){
			$errors = $e->getErrors();
			$session->setFlashdata('form_errors', $errors);
			$result = array('-referrer-', NULL);
			$post = $root->make('HC3_Post')
				->get()
				;
			$session->setFlashdata('post', $post);
		}

	// message and redirect
		if( is_array($result) ){
			$isError = false;

			if( count($result) > 2 ){
				list( $to, $msg, $isError ) = $result;
			}
			else {
				list( $to, $msg ) = $result;
			}
	
			$post = $root->make('HC3_Post')
				->get()
				;

			if( $isError ){
				$session->setFlashdata('error', $msg);
			}
			else {
				$session->setFlashdata('message', $msg);
			}

			// $session->setFlashdata('post', $post);

			$to = $uri->makeUrl( $to );

			$notificator = $root->make('HC3_Notificator');
			$notificator->send();

			if( $ajax ){
				$out = array('redirect' => $to);
				$out = json_encode( $out );
				echo $out;
			}
			else {
				if( ! headers_sent() ){
					if( 0 && defined('HC3_PROFILER') && HC3_PROFILER ){
						$html = "REDIRECT<br><a href=\"$to\">$to</a>";
						echo $html;
						exit;
					}
					else {
						wp_redirect( $to );
					}
				}
				else {
					if( 0 && defined('HC3_PROFILER') && HC3_PROFILER ){
						$html = "REDIRECT<br><a href=\"$to\">$to</a>";
						echo $html;
						exit;
					}
					else {
						$html = "<META http-equiv=\"refresh\" content=\"0;URL=$to\">";
					}
					echo $html;
				}
			}
			exit;
		}

		$enqueuer = $root->make('HC3_Enqueuer');
		$enqueuer
			->addStyle('hc', 'hc3/assets/css/hc.css?hcver=' . HC3_VERSION)
			;
		$profiler->mark('action_end');

		if( $request->isPrintView() ){
			$this->actionResult = $result;
			echo $this->render();
			exit;
		}

		$enqueuer
			->addScript('hc', 'hc3/assets/js/hc2.js?hcver=' . HC3_VERSION)
			;

		return $result;
	}

	public function render()
	{
		$root = $this->root();

		$request = $root->make('HC3_Request');
		$isPrintView = $request->isPrintView();

		$slug = $request->getSlug();

		$ajax = false;
		if( substr($slug, 0, strlen('ajax/')) == 'ajax/' ){
			$ajax = true;
		}
		else {
			$ajax = $request->isAjax() ? true : false;
		}

		$profiler = $root->make('HC3_Profiler');
		$profiler->mark('render_start');

		$result = $this->actionResult;

		$profiler = $root->make('HC3_Profiler');
		$csrf = $root->make('HC3_Csrf');

	// view
	// add announce
		if( ! $ajax ){
			$announce = $root->make('HC3_Ui_Announce');
			$result = $announce->render( $result );

	// add top menu
	// $layout = $root->make('HC3_Ui_Layout');
	// $result = $layout->render( $result );
		}

	// filter output
		$filter = $root->make('HC3_Ui_Filter');
		$result = $filter->filter( $result );

		if( ! $ajax ){
			$layout = $root->make('HC3_Layout');
			if( $isPrintView ){
				$result = $layout->renderPrint( $result );
			}
			else {
				$result = $layout->render( $result );
			}
		}

		$translate = $root->make('HC3_Translate');
		$result = $translate->translate( $result );

		$result = $csrf->prepareOutput( $result );

		$profiler->mark('render_end');
		$profiler->mark('total_end');

		if( defined('HC3_PROFILER') && HC3_PROFILER && (! $isPrintView) && (! $ajax) ){
			$result = $profiler->render( $result );
		}

		echo $result;
	}

	public function adminInit()
	{
		if( $this->isMeAdmin() ){
			$this->actionResult = $this->handleRequest();
		}
	}

	public function isMeAdmin()
	{
		$page = isset($_GET['page']) ? sanitize_text_field($_GET['page']) : '';
		if( isset($_REQUEST['page']) ){
			$page = sanitize_text_field($_REQUEST['page']);
		}

		if( $page && ($page == $this->slug) ){
			$return = TRUE;
		}
		else {
			$return = FALSE;
		}

		return $return;
	}

	public function autoload( $inclass )
	{
		$class = strtolower( $inclass );
		// check & strip prefix
		if( substr($class, 0, strlen('hc3_')) == 'hc3_' ){
			$is_lib = TRUE;
			$class = substr($class, strlen('hc3_'));
		}
		elseif( substr($class, 0, strlen($this->prefix)) == $this->prefix ){
			$is_lib = FALSE;
			$class = substr($class, strlen($this->prefix));
		}
		else {
			return;
		}

		$class_array = explode('_', $class);
		$path = $class_array;

		$start_dirs = array();
		if( $is_lib ){
			$lib_dir = defined('HC3_DEV_INSTALL') ? HC3_DEV_INSTALL : dirname($this->file) . '/hc3';
			$start_dirs[] = $lib_dir;
			$start_dirs[] = $lib_dir . '/_interface';
			$start_dirs[] = $lib_dir . '/_wordpress';

			$libDirs = $this->libDirs;
			$filter_name = $this->prefix . 'libdirs';
			$libDirs = apply_filters( $filter_name, $libDirs );

			foreach( $libDirs as $dir ){
				$start_dirs[] = $dir;
				$start_dirs[] = $dir . '/_interface';
				$start_dirs[] = $dir . '/_wordpress';
			}
		}
		else {
			// $start_dirs = $this->dirs;
			reset( $this->dirs );
			if( count($path) > 1 ){
				$module = array_shift( $path );

				foreach( $this->dirs as $dir ){
					$start_dirs[] = $dir . '/' . $module;
					$start_dirs[] = $dir . '/_wordpress/' . $module;
					// $start_dirs[] = $dir . '/' . $module . '/common';
					// $start_dirs[] = $dir . '/' . $module . '/wordpress';
				}
			}
			else {
				foreach( $this->dirs as $dir ){
					$start_dirs[] = $dir;
					$start_dirs[] = $dir . '/_wordpress';
				}
			}
		}

		// _print_r( $start_dirs );
// exit;

	// _print_r( $path );
		$file = array_pop( $path );
		if( $path ){
			$file = join('/', $path) . '/' . $file;
		}
		$file .= '.php';

		reset( $start_dirs );
		foreach( $start_dirs as $start_dir ){
			$this_file = $start_dir . '/' . $file;
			// echo "FOR $inclass TRY $this_file<br>\n";
			if( file_exists($this_file) ){
				require $this_file;
				return;
			}
		}
	}

// intercepts if in the front page our slug is given then it's ours
	public function intercept()
	{
		if( ! $this->isIntercepted() ){
			return;
		}

		$this->actionResult = $this->handleRequest();
		echo $this->render();
		exit;
	}

	public function isIntercepted()
	{
		$ret = FALSE;

		if( array_key_exists($this->hcs, $_GET) ){
			$hcs = sanitize_text_field($_GET[$this->hcs]);

			if( ($hcs == $this->slug) OR ($hcs == $this->prfx) ){
				$ret = TRUE;
				return $ret;
			}
		}

		if( array_key_exists($this->hcj, $_GET) ){
			$hcj = sanitize_text_field($_GET[$this->hcj]);

			if( $hcj == $this->prfx ){
				$ret = TRUE;
				return $ret;
			}
		}

		return $ret;
	}

	public function root()
	{
		return $this->dic;
	}
}

if( ! function_exists('_print_r') ){
function _print_r( $thing )
{
	$wpAdmin = defined('WPINC') && is_admin();

	if( $wpAdmin ){
		echo '<div style="margin-left: 15em;">';
	}

	echo '<pre>';
	print_r( $thing );
	echo '</pre>';

	if( $wpAdmin ){
		echo '</div>';
	}
}
}