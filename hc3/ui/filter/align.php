<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class HC3_Ui_Filter_Align
{
	protected $htmlFactory = NULL;

	public function __construct( HC3_Ui $htmlFactory )
	{
		$this->htmlFactory = $htmlFactory;
	}
	public function process( $element )
	{
		$tags = $element->getTags();
		if( ! in_array('align', array_keys($tags)) ){
			return $element;
		}

		if( ! method_exists($element, 'addAttr') ){
			$element = $this->htmlFactory->makeBlock( $element );
		}

		$align = $tags['align'];
		if( $align == 'align' ){
			$align = 'left';
		}

		$element
			->addAttr('class', 'hc-align-' . $align)
			;

		return $element;
	}
}
