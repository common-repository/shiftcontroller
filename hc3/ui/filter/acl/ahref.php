<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class HC3_Ui_Filter_Acl_Ahref
{
	public $acl;

	public function __construct( HC3_Acl $acl )
	{
		$this->acl = $acl;
	}

	public function process( $element )
	{
		$uiType = ( method_exists($element, 'getUiType') ) ? $element->getUiType() : '';
		if( $uiType != 'ahref' ){
			return $element;
		}

		$to = $element->getTo();

		if( is_array($to) ){
			$checkSlug = $to[0];
			$checkParams = $to[1];
		}
		else {
			$checkSlug = $to;
			$checkParams = array();
		}

		if( $checkSlug == '#' ){
			return $element;
		}

		$checkSlug = 'get:' . $checkSlug;

		if( ! $this->acl->check($checkSlug, $checkParams) ){
			$return = NULL;
			return $return;
		}

		return $element;
	}
}