<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class HC3_Ui_Filter_Uri_Ahref
{
	public $uri;

	public function __construct( HC3_Uri $uri )
	{
		$this->uri = $uri;
	}

	public function process( $element )
	{
		$uiType = ( method_exists($element, 'getUiType') ) ? $element->getUiType() : '';
		if( $uiType != 'ahref' ){
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
			;

		$dataHref = $element->getAttr('data-href');
		if( $dataHref ){
			$dataHref = array_shift( $dataHref );
			$dataHref = $this->uri->makeUrl( $dataHref );

			$element
				->setAttr('data-href', $dataHref)
				;
		}

		return $element;
	}
}