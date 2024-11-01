<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
abstract class HC3_Ui_Abstract_Collection
{
	protected $uiType = NULL;
	protected $children = array();
	protected $gutter = 2;
	protected $separated = NULL;
	protected $tags = array();

	public function __construct( $children = array() )
	{
		$this->children = $children;
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

	public function gutter( $gutter )
	{
		$this->gutter = $gutter;
		return $this;
	}

	public function separated( $separated = TRUE )
	{
		$this->separated = $separated;
		return $this;
	}

	public function __toString()
	{
		return '' . $this->render();
	}

	protected function _findKey()
	{
		$key = count($this->children);
		while( array_key_exists($key, $this->children) ){
			$key++;
		}
		return $key;
	}

	protected function _insertAtPos( $pos, $key, $child = NULL )
	{
		if( $child === NULL ){
			$child = $key;
			$key = $this->_findKey();
		}

		$this->children = array_merge( 
			array_slice( $this->children, 0, $pos ),
			array( $key => $child ),
			array_slice( $this->children, $pos )
			);

// _print_r( $this->children );
		return $this;
	}

	public function addAfter( $afterKey, $key, $child = NULL )
	{
		$index = FALSE;
		if( NULL !== $afterKey ){
			$keys = array_keys( $this->children );
			$index = array_search( $afterKey, $keys );
		}

		$pos = FALSE === $index ? count($this->children) : $index + 1;
		$this->_insertAtPos( $pos, $key, $child );

		return $this;
	}

	public function addBefore( $beforeKey, $key, $child = NULL )
	{
		$index = FALSE;
		if( NULL !== $beforeKey ){
			$keys = array_keys( $this->children );
			$index = array_search( $beforeKey, $keys );
		}
		elseif( '_end_' == $beforeKey ){
			$index = count( $this->children );
		}

		$pos = FALSE === $index ? 0 : $index;
		$this->_insertAtPos( $pos, $key, $child );

		return $this;
	}

	public function add( $key, $child = NULL )
	{
		if( $child === NULL ){
			$child = $key;
			$key = $this->_findKey();
		}

		$this->children[ $key ] = $child;

// _print_r( $this->children );
		return $this;
	}

	public function remove( $child )
	{
		$keys = array_keys($this->children);
		foreach( $keys as $k ){
			if( $this->children[$k] === $child ){
				unset( $this->children[$k] );
				break;
			}
		}
		return $this;
	}

	public function setChild( $k, $child )
	{
		if( array_key_exists($k, $this->children) ){
			$this->children[$k] = $child;
		}
		return $this;
	}

	public function getChildren()
	{
		return $this->children;
	}

	public function render()
	{
		$return = '';
		foreach( $this->children as $child ){
			if( ! is_array($child) ){
				$return .= '' . $child;
			}
		}
		return $return;
	}
}