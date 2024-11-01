<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class HC3_Ui_Element_Input_Password extends HC3_Ui_Abstract_Input
{
	protected $el = 'input';
	protected $uiType = 'input/password';

	public function render()
	{
		$out = $this->htmlFactory->makeElement('input')
			->addAttr('type', 'password' )
			->addAttr('name', $this->htmlName() )
			->addAttr('class', 'hc-field')
			// ->addAttr('class', 'hc-block')
			->addAttr('class', 'hc-full-width')
			;

		$attr = $this->getAttr();
		foreach( $attr as $k => $v ){
			$out->addAttr( $k, $v );
		}

		$out->addAttr('id', $this->htmlId());

		if( strlen($this->label) ){
			$out
				->addAttr('placeholder', $this->label)
				;
		}

		if( strlen($this->label) ){
			$out = $this->htmlFactory->makeLabelled( $this->label, $out, $this->htmlId() );
		}

		return $out;
	}
}