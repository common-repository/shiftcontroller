<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class HC3_Ui_Filter_Buttons
{
	protected $htmlFactory = NULL;

	public function __construct( HC3_Ui $htmlFactory )
	{
		$this->htmlFactory = $htmlFactory;
	}

	public function process( $element )
	{
		if( ! method_exists($element, 'getUiType') ){
			return $element;
		}

		$uiType = $element->getUiType();
		$tags = $element->getTags();

		if( in_array('button', $tags) ){
			if( defined('WPINC') ){
				$element
					->addAttr('class', 'button')
					// ->addAttr('class', 'button-primary')
					// ->addAttr('class', 'button-large')
					;
			}
			else {
				$element
					->addAttr('class', 'hc-theme-btn-submit')
					// ->addAttr('class', 'hc-theme-btn-primary')
					;
			}
			// $element
				// ->addAttr('class', 'hc-xs-block')
				// ;
		}

		if( in_array('primary', $tags) ){
			if( defined('WPINC') ){
				$element
					->addAttr('class', 'button')
					->addAttr('class', 'button-primary')
					->addAttr('class', 'button-large')
					;
			}
			else {
				$element
					->addAttr('class', 'hc-theme-btn-submit')
					->addAttr('class', 'hc-theme-btn-primary')
					;
			}

			$element
				->addAttr('class', 'hc-xs-block')
				;
		}

		if( in_array('tab-link', $tags) ){
			$element
				->addAttr('class', 'hc-block')
				;

			if( (! defined('WPINC')) OR is_admin() ){
				$element
					->addAttr('class', 'hc-theme-tab-link')
					;
				if( in_array('current', $tags) ){
					$element
						->addAttr('class', 'hc-theme-tab-link-active')
						;
				}
			}
		}

		if( in_array('nice-link', $tags) ){
			$element
				->addAttr('class', 'hc-block')
				->addAttr('class', 'hc-nowrap')
				->addAttr('class', 'hc-theme-nice-link')
				;
			if( (! defined('WPINC')) OR is_admin() ){
				$element
					->addAttr('class', 'hc-theme-nice-link-color')
					;
			}
		}

		if( in_array('unstyled-link', $tags) ){
			$element
				->addAttr('class', 'hc-unstyled-link')
				;
		}

		if( in_array('secondary', $tags) ){
			if( defined('WPINC') ){
				if( is_admin() ){
					$element
						->addAttr('class', 'page-title-action')
						->addAttr('style', 'top: auto;');
						;
				}
			}
			else {
				$element
					->addAttr('class', 'hc-xs-block')
					->addAttr('class', 'hc-theme-btn-submit')
					->addAttr('class', 'hc-theme-btn-secondary')
					;
			}
		}

		if( in_array('danger', $tags) ){
			$element
				->addAttr('class', 'hc-xs-block')
				->addAttr('class', 'hc-theme-btn-submit')
				->addAttr('class', 'hcj2-confirm')

				->addAttr('class', 'hc-white')
				->addAttr('class', 'hc-bg-darkred')
				;
		}

		if( in_array('danger-link', $tags) ){
			$element
				->addAttr('class', 'hc-theme-nice-link')
				->addAttr('class', 'hc-nowrap')
				->addAttr('class', 'hc-darkred')
				;

			if( (! defined('WPINC')) OR is_admin() ){
				$element
					->addAttr('class', 'hc-theme-nice-link-color')
					;
			}
		}

		if( in_array('confirm', $tags) ){
			$element
				->addAttr('class', 'hcj2-confirm')
				;
		}

		return $element;
	}
}