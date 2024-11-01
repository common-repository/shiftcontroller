<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class HC3_Ui_Element_Input_Textarea extends HC3_Ui_Abstract_Input
{
	protected $el = 'input';
	protected $uiType = 'input/textarea';

	public function setRows( $rows )
	{
		$this->addAttr('rows', $rows);
		return $this;
	}

	public function render()
	{
		$out = $this->htmlFactory->makeElement('textarea', $this->value )
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
			// $out
			// 	->addAttr('placeholder', $this->label)
			// 	;
			$out = $this->htmlFactory->makeLabelled( $this->label, $out, $this->htmlId() );
		}

		return $out;
	}
}