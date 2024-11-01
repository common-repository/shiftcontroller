<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class HC3_Ui_Filter_Uri_Submit
{
	public $uri;

	public function __construct( HC3_Uri $uri )
	{
		$this->uri = $uri;
	}

	public function process( $element )
	{
		$uiType = ( method_exists($element, 'getUiType') ) ? $element->getUiType() : '';
		if( $uiType != 'input/submit' ){
			return $element;
		}

		$to = $element->getFormAction();
		if( ! $to ){
			return $element;
		}

		$to = $this->uri->makeUrl( $to );
		$element->setFormAction( $to );

		return $element;
	}
}