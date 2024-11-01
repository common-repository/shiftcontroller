<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class HC3_Ui_Filter_Uri_Form
{
	public $uri;

	public function __construct( HC3_UriAction $uri )
	{
		$this->uri = $uri;
	}

	public function process( $element )
	{
		$uiType = ( method_exists($element, 'getUiType') ) ? $element->getUiType() : '';
		if( $uiType != 'form' ){
			return $element;
		}

		$to = $element->getAction();

		$to = $this->uri->makeUrl( $to );
		$element->setAction( $to );

		return $element;
	}
}