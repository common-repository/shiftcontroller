<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class HC3_Ui_Filter_PageHeader
{
	protected $htmlFactory = NULL;

	public function __construct( HC3_Ui $htmlFactory )
	{
		$this->htmlFactory = $htmlFactory;
	}

	public function process( $element )
	{
		$tags = $element->getTags();
		if( ! in_array('page-header', $tags) ){
			return $element;
		}

		$element = $this->htmlFactory->makeH(1, $element )
			->addAttr('style', 'padding: 0 0; margin: 0 0;')
			;
		$element = $this->htmlFactory->makeBlock( $element )
			->addAttr( 'class', 'hc-py2' )
			;

		return $element;
	}
}