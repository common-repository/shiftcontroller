<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class HC3_Ui_Element_Labelled extends HC3_Ui_Abstract_Collection
{
	public $ui, $label, $content, $labelFor;

	public function __construct( HC3_Ui $ui, $label, $content, $labelFor = '' )
	{
		$this->ui = $ui;
		$this->label = $label;
		$this->content = $content;
		$this->labelFor = $labelFor;

		$this->add( $this->label );
		$this->add( $this->content );
	}

	public function renderFieldset()
	{
		$label = $this->ui->makeElement( 'legend', $this->label );
		$content = $this->ui->makeCollection( array($label, $this->content) );

		$out = $this->ui->makeElement( 'fieldset', $content )
			;

		return $out;
	}

	public function render()
	{
		$label = $this->ui->makeElement('label', $this->label)
			// ->addAttr('class', 'hc-fs2')
			->addAttr('class', 'hc-bold')
			;

		if( (null !== $this->labelFor) && strlen($this->labelFor) ){
			$label
				->addAttr('for', $this->labelFor )
				;
		}

		$out = $this->ui->makeList( array($label, $this->content) )
			->gutter(1)
			;

		return $out;
	}
}