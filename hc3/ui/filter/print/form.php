<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class HC3_Ui_Filter_Print_Form
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
		if( $uiType != 'form' ){
			return $element;
		}

		if( ! $this->request->isPrintView() ){
			return $element;
		}

		$element = NULL;
		return $element;
	}
}