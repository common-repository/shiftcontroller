<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class HC3_Ui_Element_Input_CheckboxSet extends HC3_Ui_Abstract_Input
{
	protected $el = 'input';
	protected $uiType = 'input/checkbox';

	protected $options = array();

	public function __construct( $htmlFactory, $name, $label = NULL, $options = array(), $value = array() )
	{
		parent::__construct( $htmlFactory, $name, $label, $value );
		$this->options = $options;
	}

	public function render()
	{
		$options = array();
		foreach( $this->options as $k => $v ){
			if( ! strlen($k) ){
				$k = ' ';
			}
			$checked = in_array($k, $this->value) ? TRUE : FALSE;
			$name = $this->name . '[]';
			$thisInput =  $this->htmlFactory->makeInputCheckbox( $name, $v, $k, $checked );
			$options[] =  $thisInput;
		}

		$out = $this->htmlFactory->makeListInline( $options );

		if( strlen($this->label) ){
			$out = $this->htmlFactory->makeLabelled( $this->label, $out, $this->htmlId() );
		}

		return $out;
	}
}