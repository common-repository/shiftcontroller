<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class HC3_Ui_Filter_Color
{
	protected $htmlFactory = NULL;

	public function __construct( HC3_Ui $htmlFactory )
	{
		$this->htmlFactory = $htmlFactory;
	}
	public function process( $element )
	{
		$tags = $element->getTags();
		if( ! in_array('color', array_keys($tags)) ){
			return $element;
		}

		if( ! method_exists($element, 'addAttr') ){
			$element = $this->htmlFactory->makeBlock( $element );
		}

		$element
			->addAttr('class', 'hc-rounded')
			;

		$color = $tags['color'];
		if( $color != 'color' ){
			if( substr($color, 0, 1) == '#' ){
				$element->addAttr('style', 'color: ' . $color . ';');
			}
			else {
				$element->addAttr('class', 'hc-' . $color);
			}
		}

		return $element;
	}
}