<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
#[AllowDynamicProperties]
class SH4_Api_Html_Admin
{
	public function __construct(
		HC3_Hooks $hooks,

		HC3_Ui $ui,
		HC3_Ui_Layout1 $layout
	)
	{
		$this->ui = $ui;
		$this->layout = $layout;

		$this->self = $hooks->wrap($this);
	}

	public function post()
	{
		$app = 'sh4-rest';

		if( isset($_POST[$app . '_submit']) ){
			if( isset($_POST[$app]) ){
				foreach( (array)$_POST[$app] as $key => $value ){
					$option_name = $app . '_' . $key;
					$value = sanitize_text_field( $value );
					update_option( $option_name, $value );
				}
			}

			if( ! isset($_POST[$app]['enabled']) ){
				$k = $app . '_enabled';
				$value = 0;
				update_option( $k, $value );
			}
		}

		$ret = array( 'admin/api', '__Settings Updated__' );
		return $ret;
	}

	public function render()
	{
		ob_start();
		require( dirname(__FILE__) . '/view.html.php' );
		$out = ob_get_contents();
		ob_end_clean();

		$this->layout
			->setContent( $out )
			->setBreadcrumb( $this->self->breadcrumb() )
			->setHeader( $this->self->header() )
			// ->setMenu( $this->self->menu() )
			;

		$out = $this->layout->render();
		return $out;
	}

	public function menu()
	{
		$return = array();
		return $return;
	}

	public function header()
	{
		$out = '__REST API__';
		return $out;
	}

	public function breadcrumb()
	{
		$return = array();
		$return['admin'] = array( 'admin', '__Administration__' );
		return $return;
	}
}