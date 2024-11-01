<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class HC3_Ui_Element_LabelledInline extends HC3_Ui_Abstract_Collection
{
	protected $htmlFactory = NULL;
	protected $label = NULL;
	protected $content = NULL;

	public function __construct( HC3_Ui $html, $label, $content, $labelFor = NULL )
	{
		$this->htmlFactory = $html;
		$this->label = $label;
		$this->content = $content;
		$this->labelFor = $labelFor;

		$this->add( $this->label );
		$this->add( $this->content );
	}

	public function render()
	{
		// $labelView = $this->htmlFactory->makeElement('label', $this->label);
		$labelView = $this->htmlFactory->makeBlock( $this->label );

		// if( strlen($this->labelFor) ){
			// $labelView->addAttr('for', $this->labelFor );
		// }

		$labelView
			->addAttr('class', 'hc-fs2')
			->addAttr('class', 'hc-muted2')
			// ->addAttr('class', 'hc-mt1')
			;

		$contentView = $this->htmlFactory->makeBlock( $this->content );
		// $contentView
			// ->addAttr('class', 'hc-bold')
			// ;

		$out = $this->htmlFactory->makeListInline( array($labelView, $contentView) )
			->gutter(1)
			;

		return $out;
	}
}