<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class HC3_Ui_Filter_Font_Size
{
	protected $htmlFactory = NULL;

	public function __construct( HC3_Ui $htmlFactory )
	{
		$this->htmlFactory = $htmlFactory;
	}
	public function process( $element )
	{
		$tags = $element->getTags();

		if( ! in_array('font-size', array_keys($tags)) ){
			return $element;
		}

		if( ! method_exists($element, 'addAttr') ){
			$element = $this->htmlFactory->makeBlock( $element );
		}

		$size = $tags['font-size'];

		$element
			->addAttr('class', 'hc-fs' . $size)
			;

		return $element;
	}
}