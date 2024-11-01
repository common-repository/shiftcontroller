<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class HC3_Ui_Filter_Valign
{
	protected $htmlFactory = NULL;

	public function __construct( HC3_Ui $htmlFactory )
	{
		$this->htmlFactory = $htmlFactory;
	}
	public function process( $element )
	{
		$tags = $element->getTags();
		if( ! in_array('valign', array_keys($tags)) ){
			return $element;
		}

		if( ! method_exists($element, 'addAttr') ){
			$element = $this->htmlFactory->makeBlock( $element );
		}

		$valign = $tags['valign'];
		if( $valign == 'valign' ){
			$valign = 'middel';
		}

		$element
			->addAttr('class', 'hc-valign-' . $valign)
			;

		return $element;
	}
}
