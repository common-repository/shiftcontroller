<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class HC3_Ui_Filter_Margin
{
	public $ui;

	public function __construct( HC3_Ui $ui )
	{
		$this->ui = $ui;
	}

	public function process( $element )
	{
		$tags = $element->getTags();
		if( ! in_array('margin', array_keys($tags)) ){
			return $element;
		}

		if( ! method_exists($element, 'addAttr') ){
			$element = $this->ui->makeBlock( $element );
		}

		$margin = $tags['margin'];
		if( ! is_array($margin) ){
			$margin = array( $margin );
		}

		foreach( $margin as $m ){
			$element->addAttr('class', 'hc-m' . $m);
		}

		return $element;
	}
}