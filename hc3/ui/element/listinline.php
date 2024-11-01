<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class HC3_Ui_Element_ListInline extends HC3_Ui_Abstract_Collection
{
	protected $htmlFactory = NULL;
	protected $valign = 'middle';

	public function __construct( HC3_Ui $htmlFactory, array $items = array() )
	{
		$this->htmlFactory = $htmlFactory;
		parent::__construct( $items );
	}

	public function valign( $set )
	{
		$this->valign = $set;
		return $this;
	}
	
	public function render()
	{
		$out = NULL;

	// content
		$out = '';

		if( $this->separated ){
			$separator = ($this->separated === TRUE) ? '&#124;' : $this->separated; // &vert;
			$separator = $this->htmlFactory->makeBlock($separator)
				->addAttr('class', 'hc-gray')
				->render()
				;

			$children = array();
			$ii = 0;
			foreach( $this->children as $child ){
				$child = '' . $child;
				if( ! strlen($child) ){
					continue;
				}

				if( $ii ){
					$children[] = $separator;
					// $children[] = '&bull;';
				}

				$children[] = $child;
				$ii++;
			}
		}
		else {
			$children = $this->children;
		}

		$ii = 0;
		$count = count($children);
		foreach( $children as $child ){
			$child = '' . $child;
			if( ! strlen($child) ){
				continue;
			}

			$child = $this->htmlFactory->makeBlock( $child )
				// ->addAttr('class', 'hc-inline-block')
				->addAttr('class', 'hc-valign-' . $this->valign)
				;

			if( $this->gutter && ($ii < ($count-1)) ){
				// $child->addAttr('class', 'hc-mr' . $this->gutter);
				// $child->addAttr('class', 'hc-me' . $this->gutter);
			}
			$out .= '' . $child;

			$ii++;
		}

		if( strlen($out) ){
			$out = $this->htmlFactory->makeBlock( $out )
				->addAttr( 'class', 'pw-flex' )
				->addAttr( 'class', 'pw-gap' . $this->gutter )
				// ->addAttr('class', 'hc-nowrap')
				;
		}

		return $out;
	}
}