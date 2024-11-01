<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class HC3_Ui_Element_Input_Text extends HC3_Ui_Abstract_Input
{
	protected $el = 'input';
	protected $uiType = 'input/text';
	protected $bold = false;

	public function bold( $set = TRUE )
	{
		$this->bold = $set;
		return $this;
	}

	public function render()
	{
		$out = $this->htmlFactory->makeElement('input')
			->addAttr('type', 'text' )
			->addAttr('name', $this->htmlName() )
			->addAttr('class', 'hc-field')
			// ->addAttr('class', 'hc-block')
			->addAttr('class', 'hc-full-width')
			;

		if( strlen($this->value) ){
			$out->addAttr('value', $this->value);
		}

		$attr = $this->getAttr();
		foreach( $attr as $k => $v ){
			$out->addAttr( $k, $v );
		}

		$out->addAttr('id', $this->htmlId());

		if( (null !== $this->label) && strlen($this->label) ){
			$placeHolder = $this->label;
			$placeHolder = strip_tags( $placeHolder );

			$out
				->addAttr('placeholder', $placeHolder)
				;
		}

		if( $this->bold ){
			$out
				->addAttr('class', 'hc-fs5')
				;
		}

		if( (null !== $this->label) && strlen($this->label) && (! $this->bold) ){
			$out = $this->htmlFactory->makeLabelled( $this->label, $out, $this->htmlId() );
		}

		return $out;
	}
}