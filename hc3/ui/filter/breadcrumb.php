<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class HC3_Ui_Filter_Breadcrumb
{
	public $ui;

	public function __construct(
		HC3_Ui $ui
		)
	{
		$this->ui = $ui;
	}

	public function process( $element )
	{
		$tags = $element->getTags();
		if( ! in_array('breadcrumb', $tags) ){
			return $element;
		}

		$children = $element->getChildren();
		$keys = array_keys($children);

		$count = count($keys);
		for( $ii = 1; $ii < $count; $ii++ ){
			$element
				->addBefore($keys[$ii], 'brd-' . $ii, '&raquo;')
				;
		}

		return $element;
	}
}