<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class HC3_Ui_Element_Table extends HC3_Ui_Abstract_Collection
{
	protected $htmlFactory = NULL;
	protected $striped = TRUE;
	protected $bordered = FALSE;
	protected $segments = array();
	protected $labelled = FALSE;

	protected $forceColWidth = null;
	protected $forceRowHeaderWidth = null;

	public function __construct( HC3_Ui $htmlFactory, $header = NULL, $rows = array(), $striped = TRUE )
	{
		$this->htmlFactory = $htmlFactory;
		$this->striped = $striped;

		$final_rows = array();

		if( NULL === $header ){
			if( isset($rows[0]) && is_array($rows[0]) ){
				$header = $rows[0];
				foreach( array_keys($header) as $k ){
					$header[$k] = NULL;
				}
			}
		}

		if( $header ){
			$final_rows[] = $this->htmlFactory->makeCollection($header);
		}

		foreach( $rows as $row ){
			$final_row = $this->htmlFactory->makeCollection($row);
			$final_rows[] = $final_row;
		}

		parent::__construct( $final_rows );
	}

	public function forceColWidth( $width )
	{
		$this->forceColWidth = $width;
		return $this;
	}

	public function forceRowHeaderWidth( $width )
	{
		$this->forceRowHeaderWidth = $width;
		return $this;
	}

	public function setBordered( $set = TRUE )
	{
		$this->bordered = $set;
		return $this;
	}

	public function setLabelled( $set = TRUE )
	{
		$this->labelled = $set;
		return $this;
	}

	public function setSegments( array $set )
	{
		$this->segments = $set;
		return $this;
	}

	public function setStriped( $striped = TRUE )
	{
		$this->striped = $striped;
		return $this;
	}

	public function render()
	{
	// header
		$show = array();

		$rows = $this->getChildren();
		if( ! $rows ) return;

		$header = current( $rows );
		// $header = array_shift( $rows );
		$header = $header->getChildren();

	// if all null then we don't need header
		$show_header = FALSE;
		foreach( $header as $k => $hv ){
			if( $hv !== NULL ){
				$show_header = TRUE;
				break;
			}
		}

		if( $this->labelled ){
			if( $this->forceColWidth ){
				$width = $this->forceColWidth;
				$firstWidth = $this->forceRowHeaderWidth ? $this->forceRowHeaderWidth : $this->forceColWidth;
			}
			else {
				$width = 'x-' . ( count($header) - 1 );
				$firstWidth = 'x-x';
				$firstWidth = $this->forceRowHeaderWidth ? $this->forceRowHeaderWidth : $firstWidth;
			}
		}
		else {
			if( $this->forceColWidth ){
				$width = $this->forceColWidth;
				$firstWidth = $this->forceRowHeaderWidth ? $this->forceRowHeaderWidth : $this->forceColWidth;
			}
			else {
				$width = '1-' . count($header);
				$firstWidth = $width;
				$firstWidth = $this->forceRowHeaderWidth ? $this->forceRowHeaderWidth : $firstWidth;
			}
		}

	// rows
		$rri = 0;

		foreach( $rows as $rid => $row ){
			$row = $row->getChildren();

			$rri++;
			$row_cells = array();

			$ii = 0;
			$grid = array();
			reset( $header );
			foreach( $header as $k => $hv ){
				$v = array_key_exists($k, $row) ? $row[$k] : NULL;
				$cell = $this->htmlFactory->makeBlock( $v );

				if( (! $ii) && $show_header ){
					if( defined('WPINC') && is_admin() ){
						$cell
							->addAttr('class', 'hc-bg-white')
							;
					}
				}

				if( (0 == $rid) && $show_header ){
					if( defined('WPINC') && is_admin() ){
						$cell
							->addAttr('class', 'hc-bg-white')
							;
					}
				}

				if( $this->gutter ){
					$cell
						->addAttr('class', 'hc-p' . $this->gutter)
						->addAttr('class', 'hc-px' . $this->gutter . '-xs')
						;
				}
				$cell
					->addAttr('class', 'hc-py1-xs')
					;

				if( $hv ){
					$cell_header = $this->htmlFactory->makeBlock($hv)
						->addAttr('class', 'hc-fs1')
						->addAttr('class', 'hc-muted2')
						->addAttr('class', 'hc-lg-hide')
						->addAttr('class', 'hc-p1-xs')
						;
					$cell = $this->htmlFactory->makeCollection( array($cell_header, $cell) );
				}

				if( $ii ){
					$grid[] = array( $cell, $width, 12 );
				}
				else {
					$grid[] = array( $cell, $firstWidth, 12 );
				}
				$ii++;
			}

			$tr = $this->htmlFactory->makeGrid( $grid )->gutter(0);
			if( $this->bordered ){
				$tr->setBordered( $this->bordered );
			}
			if( $this->segments ){
				$tr->setSegments( $this->segments );
			}

			if( $this->striped ){
				$tr = $this->htmlFactory->makeBlock( $tr );
				if( defined('WPINC') && is_admin() ){
					if( $rri % 2 ){
						$tr->addAttr('class', 'hc-bg-wpsilver');
					}
					else {
						$tr->addAttr('class', 'hc-bg-white');
					}
				}
				else {
					// if( $rri % 2 ){
						// $tr->addAttr('class', 'hc-bg-lightsilver');
					// }
				}
			}

		// header
			if( (0 == $rid) && $show_header ){
				$tr
					// ->addAttr('class', 'hc-full-width')
					->addAttr('class', 'hc-table-header')
					// ->addAttr('class', 'hc-bg-white')
					->addAttr('class', 'hc-xs-hide')
					->addAttr('class', 'hc-fs4')
					->addAttr('style', 'line-height: 1.5em;')
					;

				if( defined('WPINC') && is_admin() ){
					$tr
						->addAttr('class', 'hc-table-header-wpadmin')
						;
				}
			}

			$show[] = $tr;
		}

		if( $this->bordered ){
			$out = $this->htmlFactory->makeCollection( $show );
			$out = $this->htmlFactory->makeBlock( $out )
				->addAttr('class', 'hc-table')
				// ->addAttr('style', 'border: red 1px solid; overflow: auto;')
				;
		}
		else {
			$out = $this->htmlFactory->makeList( $show )->gutter(0);
			$out = $this->htmlFactory->makeBlock( $out )
				->addAttr('class', 'hc-border')
				;
		}

		if( $this->forceColWidth ){
			// $out = $this->htmlFactory->makeBlock( $out )
				// ->addAttr( 'style', 'overflow-x: auto;')
				// ;
		}

		return $out;
	}
}