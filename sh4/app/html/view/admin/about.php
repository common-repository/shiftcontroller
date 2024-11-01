<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
#[AllowDynamicProperties]
class SH4_App_Html_View_Admin_About
{
	public function __construct(
		HC3_Hooks $hooks,
		HC3_Uri $uri,
		HC3_Ui $ui,
		HC3_Ui_Layout1 $layout
	)
	{
		$this->ui = $ui;
		$this->layout = $layout;
		$this->uri = $uri;

		$this->self = $hooks->wrap($this);
	}

	public function render()
	{
		$out = array();

// $currentUrl = $this->uri->currentUrl();
// echo "CURRENT_URL = '" . $currentUrl . "'<br>";
// echo "SITE_URL = '" . site_url('/') . "'<br>";
// echo "GET_SITE_URL = '" . get_site_url() . "'<br>";
// exit;

		$version = substr(SH4_VERSION, 0, 1) . '.' . substr(SH4_VERSION, 1, 1) . '.' . substr(SH4_VERSION, 2);
		$version = $this->ui->makeBlock( $version )
			->tag('font-size', 5)
			;
		$version = $this->ui->makeLabelled( '__Version__', $version );
		$out[] = $version;

		$addons = array();
		if( defined('SH4_PRO_VERSION') ){
			$version = substr(SH4_PRO_VERSION, 0, 1) . '.' . substr(SH4_PRO_VERSION, 1, 1) . '.' . substr(SH4_PRO_VERSION, 2);
			$version = 'Pro' . ' ' . $version;
			$version = $this->ui->makeBlock( $version )
				->tag('font-size', 5)
				;
			$addons[] = $version;
		}

		if( ! $addons ){
			$addons[] = '__None__';
		}

		$addons = $this->ui->makeList( $addons );
		$addons = $this->ui->makeLabelled( '__Addons__', $addons );
		$out[] = $addons;

	// resinstall
		$buttons = array();
		$buttons[] = $this->ui->makeInputSubmit( '__Delete All Shifts__')
			->setFormAction( 'admin/reinstall/shifts' )
			->tag('secondary')
			->tag('confirm')
			;
		$buttons[] = $this->ui->makeInputSubmit( '__Complete Reinstall__')
			->setFormAction( 'admin/reinstall' )
			->tag('secondary')
			->tag('confirm')
			;
		$buttons = $this->ui->makeListInline( $buttons );
		$form = $this->ui->makeForm(
			'admin/reinstall',
			$buttons
			);

		$reinstall = $this->ui->makeLabelled( '__Reinstall__', $form );

		$out[] = $reinstall;

		$out = $this->ui->makeList( $out );

		$this->layout
			->setContent( $out )
			->setHeader( $this->self->header() )
			->setBreadcrumb( $this->self->breadcrumb() )
			;

		$out = $this->layout->render();
		return $out;
	}

	public function header()
	{
		$out = '__About__';
		return $out;
	}

	public function breadcrumb()
	{
		$return = array();
		$return['admin'] = array( 'admin', '__Administration__' );
		return $return;
	}
}