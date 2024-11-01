<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
#[AllowDynamicProperties]
abstract class HC3_Ui_Abstract_Element
{
	protected $el = 'input';
	protected $attr = array();
	private $no_array = array('id', 'action', 'href', 'cols', 'rows');

	protected $uiType = NULL;
	protected $tags = array();
	protected $content = NULL;

	public function __construct( $el = NULL, $content = NULL )
	{
		$this->el = $el;
		$this->content = $content;
	}

	public function __toString()
	{
		return '' . $this->render();
	}

	public function tag( $tag, $details = NULL )
	{
		$details = ($details === NULL) ? $tag : $details;
		$this->tags[ $tag ] = $details;
		return $this;
	}

	public function getTags()
	{
		return $this->tags;
	}

	public function getUiType()
	{
		return $this->uiType;
	}

	public function getChildren()
	{
		$return = array( $this->content );
		return $return;
	}

	public function getContent()
	{
		return $this->content;
	}

	public function setChild( $key, $child )
	{
		$this->content = $child;
		return $this;
	}

	public function margin( $margin )
	{
		$this->addAttr('class', 'hc-m' . $margin);
		return $this;
	}

	public function marginX( $margin )
	{
		$this->addAttr('class', 'hc-mx' . $margin);
		return $this;
	}

	public function marginY( $margin )
	{
		$this->addAttr('class', 'hc-my' . $margin);
		return $this;
	}

	public function padding( $padding )
	{
		$this->addAttr('class', 'hc-p' . $padding);
		return $this;
	}

	public function paddingX( $padding )
	{
		$this->addAttr('class', 'hc-px' . $padding);
		return $this;
	}

	public function paddingY( $padding )
	{
		$this->addAttr('class', 'hc-py' . $padding);
		return $this;
	}

	public function render()
	{
		$el = $this->el;

		$return = '';
		$add_newline = FALSE;

		if( $el !== NULL ){
			$return .= '<' . $el;

			if( in_array($el, array('script', 'meta', 'link', 'head', 'body')) ){
				$add_newline = TRUE;
			}

			$attr = $this->getAttr();
			foreach( $attr as $key => $val ){
				if( is_array($val) ){
					$val = join(' ', $val);
				}

				if( strlen($val) OR ( substr($key, 0, strlen('data-')) == 'data-') OR ('option' == $el) ){
					$return .= ' ' . esc_attr($key) . '="' . esc_attr($val) . '"';
				}
			}
		}

		if( in_array($el, array('br', 'input', 'link', 'meta')) ){
			$return .= '/>';
		}
		else {
			if( $el !== NULL ){
				$return .= '>';
			}

			$return .= $this->content;

			if( $el !== NULL ){
				$return .= '</' . $el . '>';
			}
		}

		if( $add_newline ){
			$return .= "\n";
		}

		return $return;
	}

	// attribute related functions
	public function getAttr( $key = NULL )
	{
		if( $key === NULL ){
			$return = $this->attr;
		}
		elseif( isset($this->attr[$key]) ){
			$return = $this->attr[$key];
		}
		else {
			$return = array();
		}
		return $return;
	}

	public function setAttr( $key, $value )
	{
		unset( $this->attr[$key] );
		return $this->addAttr( $key, $value );
	}

	public function addAttr( $key, $value, $escape = true )
	{
		if( is_array($value) ){
			foreach( $value as $v ){
				$this->addAttr( $key, $v );
			}
			return $this;
		}

		switch( $key ){
			case 'title':
				if( is_string($value) ){
					$value = strip_tags($value);
					$value = trim($value);
				}
				break;

			case 'class':
				if( isset($this->attr[$key]) && in_array($value, $this->attr[$key]) ){
					return $this;
				}
				break;
		}

		if( $value === NULL ){
			return $this;
		}

		if( ! in_array($key, $this->no_array) ){
			if( ! is_array($value) )
				$value = array( $value ); 
		}

		if( $escape && in_array($key, array('alt', 'value', 'title')) ){
			for( $ii = 0; $ii < count($value); $ii++ ){
				$value[$ii] = $this->esc_attr( $value[$ii] );
			}
		}

		if( in_array($key, $this->no_array) ){
			$this->attr[$key] = $value;
		}
		else {
			if( isset($this->attr[$key]) ){
				$this->attr[$key] = array_merge( $this->attr[$key], $value );
			}
			else {
				$this->attr[$key] = $value;
			}
		}

		return $this;
	}

	public function esc_attr( $value )
	{
		if( function_exists('esc_attr') ){
			return esc_attr( $value );
		}
		else {
			$return = htmlspecialchars( $value );
			return $return;
		}
	}
}