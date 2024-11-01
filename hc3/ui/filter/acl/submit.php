<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class HC3_Ui_Filter_Acl_Submit
{
	public $acl;

	public function __construct( HC3_Acl $acl )
	{
		$this->acl = $acl;
	}

	public function process( $element )
	{
		$uiType = ( method_exists($element, 'getUiType') ) ? $element->getUiType() : '';
		if( $uiType != 'input/submit' ){
			return $element;
		}

		$checkSlug = $element->getFormAction();
		if( ! $checkSlug ){
			return $element;
		}

		$checkSlug = 'post:' . $checkSlug;

		if( ! $this->acl->check($checkSlug) ){
			$return = NULL;
			return $return;
		}

		return $element;
	}
}