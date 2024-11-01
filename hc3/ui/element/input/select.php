<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class HC3_Ui_Element_Input_Select extends HC3_Ui_Abstract_Input
{
	protected $el = 'select';
	protected $uiType = 'input/select';
	protected $options = array();

	public function __construct( $htmlFactory, $name, $label = NULL, $options = array(), $value = NULL )
	{
		parent::__construct( $htmlFactory, $name, $label, $value );
		$this->options = $options;
	}

	public function render()
	{
		$options = array();
		foreach( $this->options as $k => $v ){
			if( ! strlen($k) ){
				if( $this->getAttr('required') ){
				}
				else {
					$k = ' ';
				}
			}

			$this_input =  $this->htmlFactory->makeElement('option', $v)
				->addAttr('value', $k )
				;

			if( $k == $this->value ){
				$this_input
					->addAttr('selected', 'selected')
					;
			}
			$options[] =  $this_input;
		}

		$options = $this->htmlFactory->makeCollection( $options );

		$out = $this->htmlFactory->makeElement('select', $options)
			->addAttr('name', $this->htmlName() )
			->addAttr('class', 'hc-field')
			// ->addAttr('class', 'hc-block')
			->addAttr('class', 'hc-full-width')
			;

		$attr = $this->getAttr();
		foreach( $attr as $k => $v ){
			$out->addAttr( $k, $v );
		}

		if( strlen($this->label) ){
			$out = $this->htmlFactory->makeLabelled( $this->label, $out, $this->htmlId() );
		}

		return $out;
	}
}