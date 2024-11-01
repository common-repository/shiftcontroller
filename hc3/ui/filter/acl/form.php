<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class HC3_Ui_Filter_Acl_Form
{
	public $acl;

	public function __construct( HC3_Acl $acl )
	{
		$this->acl = $acl;
	}

	public function process( $element )
	{
		$uiType = ( method_exists($element, 'getUiType') ) ? $element->getUiType() : '';
		if( $uiType != 'form' ){
			return $element;
		}

		$to = $element->getAction();
		if( $to == '#' ){
			return $element;
		}

		if( is_array($to) ){
			$to = array_shift($to);
		}
		$checkTo = 'post:' . $to;

		if( ! $this->acl->check($checkTo) ){
			$return = NULL;
			return $return;
		}

		return $element;
	}
}