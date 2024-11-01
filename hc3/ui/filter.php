<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class HC3_Ui_Filter
{
	protected $filters = array();

	public $acl, $uri, $uriAction, $htmlFactory;

	public function __construct(
		HC3_Dic $dic,
		HC3_Uri $uri,
		HC3_UriAction $uriAction,
		HC3_Acl $acl,
		HC3_Ui $htmlFactory
		)
	{
		$this->acl = $acl;
		$this->uri = $uri;
		$this->uriAction = $uriAction;
		$this->htmlFactory = $htmlFactory;

		$this->filters = array();

		$this->filters[] = new HC3_Ui_Filter_Acl_Ahref( $this->acl );
		$this->filters[] = new HC3_Ui_Filter_Acl_Form( $this->acl );
		$this->filters[] = new HC3_Ui_Filter_Acl_Submit( $this->acl );

		$this->filters[] = new HC3_Ui_Filter_Buttons( $this->htmlFactory );

		$this->filters[] = $dic->make('HC3_Ui_Filter_Print_Ahref');
		$this->filters[] = $dic->make('HC3_Ui_Filter_Print_Form');
		$this->filters[] = $dic->make('HC3_Ui_Filter_Print_Collapse');

		$this->filters[] = new HC3_Ui_Filter_Uri_Ahref( $this->uri );
		$this->filters[] = new HC3_Ui_Filter_Uri_Form( $this->uriAction );
		$this->filters[] = new HC3_Ui_Filter_Uri_Ajax( $this->uriAction );
		$this->filters[] = new HC3_Ui_Filter_Uri_Submit( $this->uriAction );

		$this->filters[] = new HC3_Ui_Filter_Input_Fill();
		$this->filters[] = new HC3_Ui_Filter_Input_Error( $this->htmlFactory );
		$this->filters[] = new HC3_Ui_Filter_SubHeader( $this->htmlFactory );
		$this->filters[] = new HC3_Ui_Filter_PageHeader( $this->htmlFactory );
		$this->filters[] = new HC3_Ui_Filter_Breadcrumb( $this->htmlFactory );
		$this->filters[] = new HC3_Ui_Filter_Border( $this->htmlFactory );
		$this->filters[] = new HC3_Ui_Filter_BorderColor( $this->htmlFactory );

		$this->filters[] = new HC3_Ui_Filter_Padding( $this->htmlFactory );
		$this->filters[] = new HC3_Ui_Filter_Margin( $this->htmlFactory );

		$this->filters[] = new HC3_Ui_Filter_Color( $this->htmlFactory );
		$this->filters[] = new HC3_Ui_Filter_BgColor( $this->htmlFactory );

		$this->filters[] = new HC3_Ui_Filter_Block( $this->htmlFactory );
		$this->filters[] = new HC3_Ui_Filter_Align( $this->htmlFactory );
		$this->filters[] = new HC3_Ui_Filter_Valign( $this->htmlFactory );
		$this->filters[] = new HC3_Ui_Filter_Nowrap( $this->htmlFactory );
		$this->filters[] = new HC3_Ui_Filter_Hide( $this->htmlFactory );
		$this->filters[] = new HC3_Ui_Filter_Muted( $this->htmlFactory );

		$this->filters[] = new HC3_Ui_Filter_Font_Size( $this->htmlFactory );
		$this->filters[] = new HC3_Ui_Filter_Font_Style( $this->htmlFactory );

		$this->filters[] = new HC3_Ui_Filter_AutoDismiss( $this->htmlFactory );
	}

	public function filter( $element )
	{
		if( ! is_object($element) ){
			return $element;
		}

		if( method_exists($element, 'getChildren') ){
			$children = $element->getChildren();
			$keys = array_keys($children);
			foreach( $keys as $key ){
				$child = $children[$key];
				$child = $this->filter( $child );
				$element->setChild( $key, $child );
			}
		}

	// keep tags
		$tags = $element->getTags();

		reset( $this->filters );
		foreach( $this->filters as $filter ){
			if( ! is_object($element) ){
				continue;
			}
			$element = $filter->process($element);
			if( $element === NULL ){
				break;
			}

	// restore tags
			// foreach( $tags as $tag => $param ){
				// if( is_object($element) ){
					// $element->tag( $tag, $param );
				// }
			// }
		}

		return $element;
	}
}
