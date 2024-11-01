<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class HC3_Ui_Element_Input_TimeRange extends HC3_Ui_Abstract_Input
{
	protected $el = 'select';
	protected $uiType = 'input/timerange';
	protected $timeFormatOptions = array();

	public function __construct( $htmlFactory, $name, $label = NULL, $timeFormatOptions = array(), $value = array() )
	{
		parent::__construct( $htmlFactory, $name, $label, $value );
		$this->timeFormatOptions = $timeFormatOptions;
	}

	public function setValue( $value )
	{
		if( ! is_array($value) ){
			$value = explode( '-', $value );
		}
		$this->value = $value;
		return $this;
	}

	public function render()
	{
		$input_value = NULL;
		if( $this->value && is_array($this->value) ){
			$input_value = array();
			foreach( $this->value as $v ){
				$input_value[] = $v;
			}
			$input_value = join('-', $input_value);
		}
		$hidden = $this->htmlFactory->makeInputHidden( $this->name(), $input_value );

		$display = $this->htmlFactory->makeBlock()
			->addAttr('class', 'hcj-display')
			;

		$out = $this->htmlFactory->makeCollection( array($hidden, $display) );
		$out = $this->htmlFactory->makeBlock( $out )
			->addAttr('class', 'hcj-timerange-input')
			;

	// time options
		$data_atts = array(
			'time-format'	=> $this->timeFormatOptions,
			);
		foreach( $data_atts as $k => $v ){
			$out->addAttr('data-' . $k, htmlentities(json_encode($v)));
		}

		if( strlen($this->label) ){
			$out = $this->htmlFactory->makeLabelled( $this->label, $out, $this->htmlId() );
		}

		return $out;
	}
}
