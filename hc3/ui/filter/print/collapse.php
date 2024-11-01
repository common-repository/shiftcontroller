<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class HC3_Ui_Filter_Print_Collapse
{
	public $request;

	public function __construct(
		HC3_Request $request
	)
	{
		$this->request = $request;
	}

	public function process( $element )
	{
		$uiType = ( method_exists($element, 'getUiType') ) ? $element->getUiType() : '';

		if( $uiType != 'collapse' ){
			return $element;
		}

		if( ! $this->request->isPrintView() ){
			return $element;
		}

		$element
			->setContent( NULL )
			;

		return $element;
	}
}