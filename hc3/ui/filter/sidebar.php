<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class HC3_Ui_Filter_Sidebar
{
	public function __construct(
		HC3_Ui $ui
		)
	{
		$this->ui = $ui;
	}

	public function process( $element )
	{
		$tags = $element->getTags();
		if( ! in_array('sidebar', $tags) ){
			return $element;
		}

		$children = $element->getChildren();
		$keys = array_keys($children);

		foreach( $keys as $key ){
			$child = $children[$key];

			if( ! is_object($child) ){
				continue;
			}

			if( ! method_exists($child, 'getUiType') ){
				continue;
			}

			$uiType = $child->getUiType();
			$tags = $child->getTags();

			if( $uiType == 'ahref' ){
				$child
					->addAttr('class', 'hc-block')
					->addAttr('class', 'hc-theme-tab-link')
					;

				if( in_array('current', $tags) ){
					$child
						->addAttr('class', 'hc-theme-tab-link-active')
						;
				}
			}
			else {
				$child
					->addAttr('class', 'hc-border-bottom')
					->addAttr('class', 'hc-border-gray')
					->addAttr('class', 'hc-p2')
					->addAttr('class', 'hc-fs2')
					;
			}

			$element->setChild( $key, $child );
		}

		return $element;
	}
}