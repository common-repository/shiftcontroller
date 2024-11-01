<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class HC3_Ui_Element_LabelledHori extends HC3_Ui_Abstract_Collection
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
			;

		if( NULL !== $this->label ){
			$labelView
				->addAttr('class', 'hc-p2')
				->addAttr('class', 'hc-bg-lightsilver')
				;
		}

		$contentView = $this->htmlFactory->makeBlock( $this->content );
		$contentView
			// ->addAttr('class', 'hc-bold')
			// ->addAttr('class', 'hc-p2')
			;

		$out = $this->htmlFactory->makeGrid()
			->add( $labelView, 3 )
			->add( $contentView, 9 )
			->gutter(2)
			;

		return $out;
	}
}