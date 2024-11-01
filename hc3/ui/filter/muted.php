<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class HC3_Ui_Filter_Muted
{
	protected $htmlFactory = NULL;

	public function __construct( HC3_Ui $htmlFactory )
	{
		$this->htmlFactory = $htmlFactory;
	}

	public function process( $element )
	{
		$tags = $element->getTags();

		if( ! in_array('muted', array_keys($tags)) ){
			return $element;
		}

		if( ! method_exists($element, 'addAttr') ){
			$element = $this->htmlFactory->makeBlock( $element );
		}

		$m = $tags['muted'];

		if( 'muted' == $m ){
			$m = 2;
		}

		$element
			->addAttr('class', 'hc-muted' . $m)
			;

		return $element;
	}
}