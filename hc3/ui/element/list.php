<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class HC3_Ui_Element_List extends HC3_Ui_Abstract_Collection
{
	protected $htmlFactory = NULL;
	protected $striped = FALSE;

	public function __construct( 
		HC3_Ui $htmlFactory,
		array $items = array()
		)
	{
		$this->htmlFactory = $htmlFactory;
		parent::__construct( $items );
	}

	public function setStriped( $striped = TRUE )
	{
		$this->striped = $striped;
		return $this;
	}

	public function render()
	{
		$out = NULL;

	// content
		$children = '';
		$rri = 0;
		foreach( $this->children as $child ){
			$child = '' . $child;
			if( ! strlen($child) ){
				continue;
			}

			$child = $this->htmlFactory->makeBlock( $child );
			if( $this->gutter && $rri && (! $this->striped) ){
				$child->addAttr('class', 'hc-mt' . $this->gutter);
			}
			$rri++;

			if( $this->striped ){
				$child->addAttr('class', 'hc-p' . $this->gutter);
				if( defined('WPINC') ){
					if( is_admin() ){
						if( $rri % 2 ){
							$child->addAttr('class', 'hc-bg-wpsilver');
						}
						else {
							$child->addAttr('class', 'hc-bg-white');
						}
					}
				}
				else {
					if( $rri % 2 ){
						$child->addAttr('class', 'hc-bg-lightsilver');
					}
				}
			}

			$children .= '' . $child;
		}

		if( strlen($children) ){
			$out = $this->htmlFactory->makeBlock( $children );
		}

		return $out;
	}
}