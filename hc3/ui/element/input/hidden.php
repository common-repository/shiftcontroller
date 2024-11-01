<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class HC3_Ui_Element_Input_Hidden extends HC3_Ui_Abstract_Input
{
	protected $el = 'input';
	protected $uiType = 'input/hidden';
	public $ui, $name;

	public function __construct( $ui, $name, $value = null )
	{
		$this->ui = $ui;
		$this->name = $name;
		$this->setValue( $value );
	}

	public function render()
	{
		$out = $this->ui->makeElement('input')
			->addAttr('type', 'hidden' )
			->addAttr('name', $this->htmlName() )
			;

		if( (! is_array($this->value)) && (null !== $this->value) && strlen($this->value) ){
			$out->addAttr('value', $this->value);
		}

		return $out;
	}
}