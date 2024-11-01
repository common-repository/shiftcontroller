<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class HC3_Ui_Filter_Print_Ahref
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
		if( $uiType != 'ahref' ){
			return $element;
		}

		if( ! $this->request->isPrintView() ){
			return $element;
		}

		$tags = $element->getTags();
		if( array_key_exists('print', $tags) ){
			$element = $element->getContent();
		}
		else {
			$element = NULL;
		}

		return $element;
	}
}