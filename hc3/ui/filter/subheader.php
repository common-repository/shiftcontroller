<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class HC3_Ui_Filter_SubHeader
{
	protected $htmlFactory = NULL;

	public function __construct( HC3_Ui $htmlFactory )
	{
		$this->htmlFactory = $htmlFactory;
	}

	public function process( $element )
	{
		$tags = $element->getTags();
		if( ! in_array('sub-header', $tags) ){
			return $element;
		}

		$element = $this->htmlFactory->makeBlock( $element )
			->addAttr('class', 'hc-py1')
			->addAttr('class', 'hc-border-bottom')
			->addAttr('class', 'hc-border-gray')
			->addAttr('class', 'hc-border-bottom-dotted')
			;

		return $element;
	}
}