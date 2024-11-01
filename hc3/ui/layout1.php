<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
interface HC3_Ui_Layout1_
{
	public function setMenu( $set );
	public function setSidebar( $set );
	public function setBreadcrumb( $set );
	public function setHeader( $set );
	public function setContent( $set );
	public function render();
}

class HC3_Ui_Layout1 implements HC3_Ui_Layout1_
{
	public $partialBreadcrumb = array();
	public $partialBreadcrumbMultiline = false;
	public $partialHeader = null;
	public $partialContent = null;
	public $partialMenu = array();
	public $partialSidebar = array();

	public $ui;

	public function __construct( HC3_Ui $ui )
	{
		$this->ui = $ui;

		$this->partialBreadcrumb = array();
		$this->partialBreadcrumbMultiline = false;

		$this->partialHeader = null;
		$this->partialContent = null;
		$this->partialMenu = array();
		$this->partialSidebar = array();
	}

	public function setSidebar( $set )
	{
		$this->partialSidebar = $set;
		return $this;
	}

	public function setMenu( $set )
	{
		$this->partialMenu = $set;
		return $this;
	}

	public function setBreadcrumb( $set, $multiline = FALSE )
	{
		$this->partialBreadcrumb = $set;
		$this->partialBreadcrumbMultiline = $multiline;
		return $this;
	}

	public function setHeader( $set )
	{
		$this->partialHeader = $set;
		return $this;
	}

	public function setContent( $set )
	{
		$this->partialContent = $set;
		return $this;
	}

	public function render()
	{
		$out = array();

		$header = array();

		if( $this->partialBreadcrumb ){
			$breadcrumb = array();

			$breadcrumbParts = array();
			if( $this->partialBreadcrumbMultiline ){
				$breadcrumbParts = $this->partialBreadcrumb;
			}
			else {
				$breadcrumbParts = array( $this->partialBreadcrumb );
			}

			foreach( $breadcrumbParts as $thisBreadcrumbParts ){
				$thisBreadcrumb = array();
				foreach( $thisBreadcrumbParts as $item ){
					if( is_array($item) ){
						list( $href, $hrefLabel ) = $item;
						$thisBreadcrumb[] = $this->ui->makeAhref( $href, $hrefLabel );
					}
					else {
						$thisBreadcrumb[] = $this->ui->makeSpan( $item )
							// ->tag('font-size', 4)
							// ->tag('font-style', 'bold')
							;
					}
				}
				$thisBreadcrumb = $this->ui->makeListInline( $thisBreadcrumb )
					->tag('breadcrumb');
					;
				$breadcrumb[] = $thisBreadcrumb;
			}

			if( $this->partialBreadcrumbMultiline ){
				$breadcrumb = $this->ui->makeList( $breadcrumb )
					->gutter(1)
					;
			}
			else {
				$breadcrumb = array_shift( $breadcrumb );
			}

			$header[] = $breadcrumb;
		}

		if( (null !== $this->partialHeader) && strlen($this->partialHeader) ){
			$label = $this->ui->makeBlock( $this->partialHeader )
				->tag('page-header')
				;

			$links = array();
			if( $this->partialMenu ){
				foreach( $this->partialMenu as $item ){
					if( is_array($item) ){
						list( $href, $hrefLabel ) = $item;
						$item = $this->ui->makeAhref( $href, $hrefLabel )
							->tag('secondary')
							;
					}
					$links[] = $item;
				}
			}

			if( $links ){
				$links = $this->ui->makeListInline( $links );
				$label = $this->ui->makeListInline( array($label, $links) );
			}

			$header[] = $label;
		}

		$content = $this->partialContent;

		if( $this->partialSidebar ){
			$sidebar = $this->ui->makeList( $this->partialSidebar );
			$content = $this->ui->makeGrid()
				->add( $content, 9 )
				->add( $sidebar, 3 )
				->gutter(3)
				;
		}

		$out[] = $content;

		$out = $this->ui->makeList( $out );

		if( $header ){
			$header = $this->ui->makeList( $header )->gutter(0);
			$out = $this->ui->makeList( array($header,$out) )->gutter(1);
		}

		return $out;
	}
}