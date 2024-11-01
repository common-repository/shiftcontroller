<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class SH4_App_Html_View_Admin
{
	public $self, $ui, $layout;

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

	public function render()
	{
		$options = $this->self->menu();
		ksort( $options );

		$out = array();
		foreach( $options as $item ){
			list( $href, $label ) = $item;
			$this_menu = $this->ui->makeAhref( $href, $label )
				->tag( 'tab-link' )
				->tag( 'border' )
				->tag( 'border-color', 'gray' )
				->tag( 'font-size', 4 )
				->tag( 'margin', 1 )
				;
			$out[] = $this_menu;
		}
		$out = $this->ui->makeListInline( $out );

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
		$out = '__Administration__';
		return $out;
	}

	public function breadcrumb()
	{
		$return = array();
		if( defined('WPINC') && (! is_admin()) ){
			$return['schedule'] = array( '', '&larr; ' . '__Schedule__' );
		}

		return $return;
	}
}