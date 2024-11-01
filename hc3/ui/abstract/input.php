<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
abstract class HC3_Ui_Abstract_Input extends HC3_Ui_Abstract_Element
{
	protected $htmlFactory = NULL;
	protected $_prefix = 'hc-';
	protected $name = '';
	protected $label = '';
	protected $value = '';

	protected $htmlId = '';

	public function __construct( $htmlFactory, $name, $label = '', $value = '' )
	{
		static $useCount = 1;

		$this->htmlFactory = $htmlFactory;
		$this->name = $name;
		if( null !== $label )
			$this->label = $label;
		if( null !== $value )
			$this->setValue( $value );

		$this->htmlId = ($useCount > 1) ? $this->name . $useCount  : $this->name;
		$useCount++;
	}

	public function setValue( $value )
	{
		$this->value = $value;
		return $this;
	}

	public function htmlId()
	{
		return $this->htmlId;
	}

	public function setHtmlId( $htmlId )
	{
		$this->htmlId = $htmlId;
		return $this;
	}

	public function htmlName()
	{
		return $this->_prefix . $this->name;
	}

	public function name()
	{
		return $this->name;
	}
}