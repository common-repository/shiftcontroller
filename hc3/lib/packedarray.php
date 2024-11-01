<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class HC3_Lib_PackedArray implements ArrayAccess
{
	private $container = '';

	public function __construct( $size, $fill = 0 )
	{
		$this->container = str_repeat( chr($fill), $size );
	}

	public function offsetSet( $i, $v )
	{
		$this->container[$i] = chr( $v & 0xff ); // store value $v into $x[$i]
	}

	public function offsetExists( $i )
	{
		return TRUE;
		// return isset($this->container[$offset]);
	}

	public function offsetUnset( $i )
	{
		// unset($this->container[$offset]);
	}

	public function offsetGet( $i )
	{
		$return = ord( $this->container[$i] ); // get value $v from $x[$i]
		return $return;
	}
}