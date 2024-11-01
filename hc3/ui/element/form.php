<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
interface HC3_Ui_Element_IForm
{
	public function setId( $set );
	public function getId();
	public function getAction();
	public function setAction( $action );
	public function render();
	public function willCallCommand( $command );
}

class HC3_Ui_Element_Form extends HC3_Ui_Abstract_Element implements HC3_Ui_Element_IForm
{
	protected $uiType = 'form';

	protected $htmlFactory = NULL;
	protected $action = NULL;
	protected $content = NULL;
	protected $id = NULL;

	protected $willCallCommand = NULL;

	public function __construct( HC3_Ui $htmlFactory, $action, $content = NULL )
	{
		$this->htmlFactory = $htmlFactory;
		$this->content = $content;
		$this->setAction( $action );
		$this->id = 'hc3form_' . mt_rand( 100000, 999999 );
	}

	public function setId( $set )
	{
		$this->id = $set;
		return $this;
	}

	public function willCallCommand( $command )
	{
		$this->willCallCommand = $command;
		return $this;
	}

	public function getId()
	{
		return $this->id;
	}

	public function getChildren()
	{
		$return = array( $this->content );
		return $return;
	}

	public function setChild( $key, $child )
	{
		$this->content = $child;
		return $this;
	}

	public function getAction()
	{
		return $this->action;
	}

	public function setAction( $action )
	{
		$this->action = $action;
		return $this;
	}

	public function render()
	{
		$content = $this->content;

		// $nonceVal = wp_create_nonce( 'shiftcontroller' );
		// $nonceView = '<input type="hidden" name="hc_nonce" value="' . $nonceVal . '"/>';
		// $content = $this->htmlFactory->makeCollection( array($nonceView, $content) );

		$out = $this->htmlFactory->makeElement('form', $content)
			->addAttr('action', $this->action)
			->addAttr('method', 'post')
			->addAttr('accept-charset', 'utf-8')
			->addAttr('id', $this->id)
			;

		$attr = $this->getAttr();
		foreach( $attr as $k => $v ){
			$out->addAttr( $k, $v );
		}

		return $out;
	}
}