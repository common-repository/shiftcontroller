<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class HC3_Ui_Filter_Border
{
	protected $htmlFactory = NULL;

	public function __construct( HC3_Ui $htmlFactory )
	{
		$this->htmlFactory = $htmlFactory;
	}

	public function process( $element )
	{
		$tags = $element->getTags();
		if( ! in_array('border', array_keys($tags)) ){
			return $element;
		}

		if( ! method_exists($element, 'addAttr') ){
			$element = $this->htmlFactory->makeBlock( $element );
		}

		$border = $tags['border'];

		switch( $border ){
			case 'bottom':
				$element
					->addAttr('class', 'hc-border-bottom')
					;
				break;

			case 'top':
				$element
					->addAttr('class', 'hc-border-top')
					;
				break;

			default:
				$element
					->addAttr('class', 'hc-border')
					->addAttr('class', 'hc-rounded')
					;
				break;
		}

		return $element;
	}
}