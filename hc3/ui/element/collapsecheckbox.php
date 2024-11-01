<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class HC3_Ui_Element_CollapseCheckbox extends HC3_Ui_Abstract_Collection
{
	protected $html = NULL;
	protected $name = NULL;
	protected $label = NULL;
	protected $content = NULL;
	protected $expand = FALSE;

	protected $border = FALSE;
	protected $arrow = '&darr;';

	public function __construct( HC3_Ui $html, $name, $label, $content, $expand = FALSE )
	{
		$this->html = $html;
		$this->name = $name;
		$this->label = $label;
		$this->content = $content;

		$this->add( $this->label );
		$this->add( $this->content );

		$checked = $this->expand ? TRUE : FALSE;
		$value = NULL;
		$label = NULL;
		$name = $this->name;
		$checkbox = $this->html->makeInputCheckbox( $name, $label, $value, $checked );

		$this->add( $checkbox );

		if( $expand ){
			$this->expand();
		}
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

	public function expand( $expand = true )
	{
		$this->expand = $expand;

		$checkbox = $this->children[2];
		$checkbox->setChecked( $expand );

		return $this;
	}

	public function render()
	{
		$thisLabel = $this->children[0];
		$thisContent = $this->children[1];
		$checkbox = $this->children[2];

		$this_id = 'hc3_' . mt_rand( 100000, 999999 );

		$checkbox
			->addAttr('id', $this_id)
			->addAttr('class', 'hc-collapse-toggler')
			->addAttr('style', 'display: inline-block;')
			->addAttr('class', 'hc-valign-middle')
			;

		$trigger = $this->html->makeElement('label', $thisLabel)
			->addAttr('for', $this_id)
			->addAttr('class', 'hc-inline-block')
			// ->addAttr('class', 'hc-py1')
			->addAttr('class', 'hc-collapse-burger')
			->addAttr('class', 'hc-regular')
			->addAttr('class', 'hc-ml1')
			;

		if( ! is_object($thisLabel) ){
			$trigger
				->addAttr('title', strip_tags($thisLabel) )
				;
		}

		if( $this->border ){
			$trigger
				->addAttr('title', strip_tags($thisLabel) )
				->addAttr('class', 'hc-border-bottom-dotted')
				->addAttr('class', 'hc-border-gray')
				;
		}

		// $trigger = $this->html->makeBlock( $trigger )
			// ->addAttr('class', 'hc-collapse-burger')
			// ;

		$content = $this->html->makeBlock( $thisContent )
			->addAttr('class', 'hc-collapse-content')
			->addAttr('class', 'hc-mt1')
			// ->addAttr('class', 'hc-ml2')
			;

		$trigger = $this->html->makeCollection( array($checkbox, $trigger) );
		$out = $this->html->makeCollection( array($trigger, $content) );

		$out = $this->html->makeBlock( $out )
			->addAttr('class', 'hc-collapse-container')
			;

		return $out;	
	}
}