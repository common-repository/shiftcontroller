<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class HC3_Ui_Filter_Input_Error
{
	public $ui;

	public function __construct( HC3_Ui $ui )
	{
		$this->ui = $ui;
	}

	public function session()
	{
		return HC3_Session::instance();
	}

	public function errors()
	{
		static $ret = null;
		if( null === $ret ){
			$ret = $this->session()->getFlashdata( 'form_errors' );
		}
		return $ret;
	}

	public function process( $element )
	{
		$uiType = ( method_exists($element, 'getUiType') ) ? $element->getUiType() : '';
		if( null === $uiType ) $uiType = '';
		if( substr($uiType, 0, strlen('input/')) != 'input/' ){
			return $element;
		}

		$errors = $this->errors();
		$name = $element->name();

		if( is_array($errors) && array_key_exists($name, $errors) ){
			$error = $errors[$name];
			$error = $this->ui->makeBlock( $error )
				->paddingY(2)
				->addAttr('class', 'hc-red')
				->addAttr('class', 'hc-border-top')
				->addAttr('class', 'hc-border-red')
				;

			$element = $this->ui->makeList( array($element, $error) );
		}

		return $element;
	}
}