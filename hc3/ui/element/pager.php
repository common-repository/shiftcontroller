<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class HC3_Ui_Element_Pager extends HC3_Ui_Abstract_Collection
{
	public function __construct( 
		HC3_Ui $ui,
		$to,
		$totalCount,
		$perPage,
		$currentPage = 1
		)
	{
		$this->ui = $ui;
		// parent::__construct( $items );

		$this->to = $to;

		$this->_totalCount = $totalCount;
		$this->_perPage = $perPage;
		$this->_currentPage = $currentPage;

		$parts = array();
		$disable = array();

		if( $this->currentPage() == 1 ){
			$disable[] = 'first';
			$disable[] = 'previous';
		}
		if( $this->currentPage() == 2 ){
			$disable[] = 'first';
		}
		if( $this->currentPage() == $this->numberOfPages() ){
			$disable[] = 'next';
			$disable[] = 'last';
		}
		if( $this->currentPage() == ($this->numberOfPages() - 1) ){
			$disable[] = 'last';
		}

		$parts_config = array(
			'first'		=> array( '&lt;&lt;',	array('page' => 1) ),
			'previous'	=> array( '&lt;',		array('page' => ($this->currentPage() - 1)) ),
			'next'		=> array( '&gt;',		array('page' => ($this->currentPage() + 1)) ),
			'last'		=> array( '&gt;&gt;',	array('page' => $this->numberOfPages()) ),
			);

		foreach( $parts_config as $k => $a ){
			if( in_array($k, $disable) ){
				$parts[$k] = $this->ui->makeBlockInline( $a[0] )
					->tag('border')
					->tag('muted')
					;
			}
			else {
				$parts[$k] = $this->ui->makeAhref( array($this->to, $a[1]), $a[0] )
					// ->tag('border')
					->tag('secondary')
					;
			}
		}

		$parts['current'] = $this->ui->makeBlockInline( $this->currentPage() . ' / ' . $this->numberOfPages() )
			->tag('border')
			->tag('secondary')
			;

		parent::__construct( $parts );
	}

	public function render()
	{
		$out = $this->ui->makeListInline()
			->gutter(1)
			;

		$parts = $this->getChildren();
		$show_order = array('first', 'previous', 'current', 'next', 'last');

		foreach( $show_order as $k ){
			if( ! isset($parts[$k]) ){
				continue;
			}
			$out->add( $parts[$k] );
		}

		return $out;
	}

	public function numberOfPages()
	{
		if( ($this->_perPage == 0) || ($this->_totalCount == 0) ){
			$return = 1;
		}
		else {
			$return = ceil( $this->_totalCount / $this->_perPage );
		}

		return $return;
	}

	public function currentPage()
	{
		return $this->_currentPage;
	}	
}
