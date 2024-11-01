<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class HC3_Ui_Filter_BorderColor
{
	protected $htmlFactory = NULL;

	public function __construct( HC3_Ui $htmlFactory )
	{
		$this->htmlFactory = $htmlFactory;
	}
	public function process( $element )
	{
		$tags = $element->getTags();
		if( ! in_array('border-color', array_keys($tags)) ){
			return $element;
		}

		if( ! method_exists($element, 'addAttr') ){
			$element = $this->htmlFactory->makeBlock( $element );
		}

		$color = $tags['border-color'];
		$element
			->addAttr('style', 'border-color: ' . $color)
			;

		return $element;
	}
}