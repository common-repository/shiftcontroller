<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class HC3_Ui_Filter_Font_Style
{
	protected $htmlFactory = NULL;

	public function __construct( HC3_Ui $htmlFactory )
	{
		$this->htmlFactory = $htmlFactory;
	}

	public function process( $element )
	{
		$tags = $element->getTags();

		if( ! in_array('font-style', array_keys($tags)) ){
			return $element;
		}

		if( ! method_exists($element, 'addAttr') ){
			$element = $this->htmlFactory->makeBlock( $element );
		}

		$style = $tags['font-style'];

		if( $style == 'bold' ){
			$element->addAttr('class', 'hc-bold');
		}
		elseif( $style == 'italic' ){
			$element->addAttr('class', 'hc-italic');
		}
		elseif( $style == 'underline' ){
			$element->addAttr('class', 'hc-underline');
		}
		elseif( $style == 'line-through' ){
			$element->addAttr('class', 'hc-line-through');
		}

		return $element;
	}
}