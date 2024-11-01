<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class HC3_Ui_Element_Grid extends HC3_Ui_Abstract_Collection
{
	protected $htmlFactory = NULL;
	protected $widths = array();
	protected $bordered = FALSE;
	protected $segments = array();

	protected $attr = array();
	private $no_array = array('id', 'action', 'href', 'cols', 'rows');

	public function __construct( HC3_Ui $htmlFactory, array $items = array(), $itemWidth = NULL )
	{
		$this->htmlFactory = $htmlFactory;

		$count = count($items);

		for( $i = 0; $i < $count; $i++ ){
			if( ! is_array($items[$i]) ){
				$items[$i] = array( $items[$i] );
			}
			if( ! isset($items[$i][1]) ){ // desktop width
				$thisItemWidth = ( null == $itemWidth ) ? '1-' . $count : (12 / $itemWidth);
				$items[$i][1] = $thisItemWidth;
			}
			if( ! isset($items[$i][2]) ){ // mobile width
				$items[$i][2] = 12;
			}

			$this->add( $items[$i][0], $items[$i][1], $items[$i][2] );
		}
	}

	public function setSegments( array $set )
	{
		$this->segments = $set;
		return $this;
	}

	public function setBordered( $set = TRUE )
	{
		$this->bordered = $set;
		return $this;
	}

	public function add( $child, $width = 12, $mobile_width = 12, $offset = 0 )
	{
		parent::add( $child );
		$this->widths[] = array( $width, $mobile_width, $offset );
		return $this;
	}

	public function render()
	{
		$taken_width = 0;
		$taken_mobile_width = 0;
		$current_row = 0;
		$current_mobile_row = 0;

		$children = array();
		$key = 0;

// $this->segments = array( 8, 15, 22, 29 );

		$ii = -1;
		foreach( $this->children as $child ){
			$ii++;
			list( $width, $mobile_width, $offset ) = $this->widths[$key];
			$key++;

			$classes = array();
			if( $mobile_width == 12 ){
				if( $this->gutter ){
					$classes[] = 'hc-xs-mb' . $this->gutter;
				}
			}
			else {
				if( ! $this->bordered ){
					$classes[] = 'hc-xs-col';
				}

				if( (strpos($mobile_width, '%') === false) && (strpos($mobile_width, 'em') === false) ){
					$classes[] = 'hc-xs-col-' . $mobile_width;
				}
			}

			if( ! $this->bordered ){
				$classes[] = 'hc-col';
			}
			else {
				$classes[] = 'hc-table-cell';
				$classes[] = 'hc-border';
				if( $this->bordered !== TRUE ){
					$classes[] = 'hc-border-'. $this->bordered;
				}
			}

			if( (strpos($width, '%') === false) && (strpos($width, 'em') === false) ){
				$widthClass = 'hc-col-' . $width;
				$classes[] = $widthClass;
			}

			if( $this->gutter ){
				$classes[] = 'hc-px' . $this->gutter;
			}

			if( $this->gutter ){
				if( $current_row > 0 ){
					$classes[] = 'hc-mt' . $this->gutter;
				}
				if( $current_mobile_row > 0 ){
					$classes[] = 'hc-xs-mt' . $this->gutter;
				}
			}

			$slot = $this->htmlFactory->makeBlock( $child );
			foreach( $classes as $class ){
				$slot->addAttr('class', $class);
			}

		// more border, for weeks for example
			if( in_array($key, $this->segments) ){
				$slot
					->addAttr('class', 'hc-lg-prominent-border-left')
					// ->addAttr('style', 'border-left: gray 2px solid;')
					;
			}

			if( (strpos($width, '%') !== false) OR (strpos($width, 'em') !== false) ){
				$slot
					->addAttr('style', 'width: ' . $width . ';')
					// ->addAttr('style', 'border-left: gray 2px solid;')
					;
			}

			if( $offset ){
				$slot
					->addAttr('style', 'margin-left: ' . $offset . ';')
					;
			}

			if( strpos($width, 'em') !== false ){
				if( ! $ii ){
					$slot
						->addAttr('class', 'hc-table-row-header')
						;
					;
				}
			}

			$children[] = $slot;

			if( (strpos($width, '-') === FALSE) && (strpos($width, '%') === false) && (strpos($width, 'em') === false) ){
				$taken_width += $width;
				if( $taken_width >= 12 ){
					$current_row++;
					$taken_width = 0;

					$sep = $this->htmlFactory->makeBlock()
						->addAttr('class', 'hc-clearfix')
						;
					$children[] = $sep;
				}

				if( $mobile_width < 12 ){
					$taken_mobile_width += $mobile_width;
					if( $taken_mobile_width >= 12 ){
						$current_mobile_row++;
						$taken_mobile_width = 0;

						$sep = $this->htmlFactory->makeBlock()
							->addAttr('class', 'hc-xs-clearfix')
							;
						$children[] = $sep;
					}
				}
			}
		}

		$out = $this->htmlFactory->makeCollection( $children );
		$out = $this->htmlFactory->makeBlock($out)
			->addAttr('class', 'hc-clearfix')
			;

		if( $this->gutter ){
			$out->addAttr('class', 'hc-mxn' . $this->gutter);
		}

		if( $this->bordered ){
			$out
				->addAttr('class', 'hc-table-row')
				;
		}

		foreach( $this->attr as $k => $v ){
			$out->addAttr( $k, $v );
		}

		return $out;
	}

	public function setAttr( $key, $value )
	{
		unset( $this->attr[$key] );
		return $this->addAttr( $key, $value );
	}

	public function addAttr( $key, $value, $escape = TRUE )
	{
		if( is_array($value) ){
			foreach( $value as $v ){
				$this->addAttr( $key, $v );
			}
			return $this;
		}

		switch( $key ){
			case 'title':
				if( is_string($value) ){
					$value = strip_tags($value);
					$value = trim($value);
				}
				break;

			case 'class':
				if( isset($this->attr[$key]) && in_array($value, $this->attr[$key]) ){
					return $this;
				}
				break;
		}

		if( $value === NULL ){
			return $this;
		}

		if( ! in_array($key, $this->no_array) ){
			if( ! is_array($value) )
				$value = array( $value ); 
		}

		if( $escape && in_array($key, array('alt', 'value', 'title')) ){
			for( $ii = 0; $ii < count($value); $ii++ ){
				$value[$ii] = $this->esc_attr( $value[$ii] );
			}
		}

		if( in_array($key, $this->no_array) ){
			$this->attr[$key] = $value;
		}
		else {
			if( isset($this->attr[$key]) ){
				$this->attr[$key] = array_merge( $this->attr[$key], $value );
			}
			else {
				$this->attr[$key] = $value;
			}
		}

		return $this;
	}

	public function esc_attr( $value )
	{
		if( function_exists('esc_attr') ){
			return esc_attr( $value );
		}
		else {
			$return = htmlspecialchars( $value );
			return $return;
		}
	}
}