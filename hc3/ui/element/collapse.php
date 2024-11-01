<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
interface HC3_Ui_Element_ICollapse
{
	public function setContent( $content );
	public function border( $border = TRUE );
	public function arrow( $arrow );
	public function expand( $expand = TRUE );
	public function render();
	public function hideToggle( $hide = TRUE );
}

class HC3_Ui_Element_Collapse extends HC3_Ui_Abstract_Collection implements HC3_Ui_Element_ICollapse
{
	protected $uiType = 'collapse';
	protected $html = NULL;
	protected $label = NULL;
	protected $content = NULL;
	protected $expand = FALSE;
	protected $hideToggle = FALSE;

	protected $border = FALSE;
	protected $arrow = '&darr;';

	public function __construct( HC3_Ui $html, $label, $content, $expand = FALSE )
	{
		$this->html = $html;
		$this->label = $label;
		$this->content = $content;

		$this->add( $this->label );
		$this->add( $this->content );

		if( $expand ){
			$this->expand();
		}
	}

	public function setContent( $content )
	{
		$this->content = $content;
		return $this;
	}

	public function border( $border = TRUE )
	{
		$this->border = $border;
		return $this;
	}

	public function arrow( $arrow )
	{
		$this->arrow = $arrow;
		return $this;
	}

	public function expand( $expand = TRUE )
	{
		$this->expand = $expand;
		return $this;
	}

	public function hideToggle( $hideToggle = TRUE )
	{
		$this->hideToggle = $hideToggle;
		return $this;
	}

	public function render()
	{
		if( NULL === $this->content ){
			return $this->label;
		}

		$this_id = 'hc3_' . mt_rand( 100000, 999999 );

		$checkbox = $this->html->makeElement('input')
			->addAttr('id', $this_id)
			->addAttr('type', 'checkbox')
			->addAttr('class', 'hc-collapse-toggler')
			->addAttr('class', 'hc-hide')
			;
		if( $this->expand ){
			$checkbox->addAttr('checked', 'checked');
		}

		$trigger = $this->html->makeElement('label', $this->label)
			->addAttr('for', $this_id)
			->addAttr('class', 'hc-block')
			// ->addAttr('class', 'hc-py1')
			->addAttr('class', 'hc-collapse-burger')
			->addAttr('class', 'hc-regular')
			;

		if( ! is_object($this->label) ){
			$trigger
				->addAttr('title', strip_tags($this->label) )
				;
		}

		if( $this->border ){
			$trigger
				->addAttr('title', strip_tags($this->label) )
				->addAttr('class', 'hc-border-bottom-dotted')
				->addAttr('class', 'hc-border-gray')
				;
		}

		if( $this->arrow ){
			$trigger = $this->html->makeListInline( array($this->arrow, $trigger) )->gutter(1)
				;
			$trigger = $this->html->makeBlock( $trigger )
				->addAttr('class', 'hc-nowrap')
				;
		}
		$content = $this->html->makeBlock( $this->content )
			->addAttr('class', 'hc-collapse-content')
			->addAttr('class', 'hc-mt1')
			;

		$out = $this->html->makeCollection( array($checkbox, $trigger, $content) );
		$out = $this->html->makeBlock( $out )
			->addAttr('class', 'hc-collapse-container')
			;

		if( $this->hideToggle ){
			$out
				->addAttr('class', 'hc-collapse-container-hidetoggle')
				;
		}

		return $out;
	}
}