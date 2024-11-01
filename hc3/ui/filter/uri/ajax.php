<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class HC3_Ui_Filter_Uri_Ajax
{
	public $uri;

	public function __construct( HC3_UriAction $uri )
	{
		$this->uri = $uri;
	}

	public function process( $element )
	{
		$uiType = ( method_exists($element, 'getUiType') ) ? $element->getUiType() : '';
		if( $uiType != 'ahref' ){
			return $element;
		}

		$tags = $element->getTags();
		if( ! in_array('ajax', array_keys($tags)) ){
			return $element;
		}

		// $to = $element->getTo();
		// $href = $element->getAttr('href');
		// $href = '&hcj='
		// if( $to == '#' ){
		// }
		// else {
			// $to = $this->uri->makeUrl( $to );
		// }

		$element
			->addAttr('class', 'hcj2-ajax-loader')
			;

		return $element;
	}


	public function process_old( $element )
	{
		$uiType = ( method_exists($element, 'getUiType') ) ? $element->getUiType() : '';
		if( $uiType != 'ahref' ){
			return $element;
		}

		$tags = $element->getTags();
		if( ! in_array('ajax', array_keys($tags)) ){
			return $element;
		}

		$to = $element->getTo();
		if( $to == '#' ){
		}
		else {
			$to = $this->uri->makeUrl( $to );
		}

		$element
			->addAttr('href', $to)
			->addAttr('class', 'hcj2-ajax-loader')
			;

		return $element;
	}
}