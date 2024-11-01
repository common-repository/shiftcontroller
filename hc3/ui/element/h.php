<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class HC3_Ui_Element_H extends HC3_Ui_Abstract_Element
{
	public function __construct( $level, $content )
	{
		if( $level > 4 OR $level < 0 ){
			$level = 1;
		}
		$el = 'h' . $level;
		parent::__construct( $el, $content );
	}
}