<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class HC3_Settings implements HC3_ISettings
{
	protected $prefix = '';

	protected $defaults = array();
	protected $loaded = array();

	public function __construct( $prefix )
	{
		$this->prefix = $prefix;
	}

	public function init( $name, $value )
	{
		$this->defaults[$name] = $value;
		return $this;
	}

	public function set( $name, $value )
	{
		update_option( $this->prefix . $name, $value );

		$this->loaded[ $name ] = $value;
		return $this;
	}

	public function resetAll()
	{
		$allOptions = wp_load_alloptions();
		foreach( $allOptions as $option => $value ){
			if( strpos($option, $this->prefix) === 0 ){
				delete_option( $option );
			}
		}
	}

	public function reset( $name )
	{
		delete_option( $this->prefix . $name );
		unset( $this->loaded[$name] );
		return $this;
	}

	public function get( $name, $wantArray = false )
	{
		static $repo = null;
		if( null === $repo ){
			$repo = wp_load_alloptions();
		}

		$ret = null;

		if( array_key_exists($name, $this->loaded) ){
			$ret = $this->loaded[ $name ];
		}
		else {
			$default = array_key_exists( $name, $this->defaults ) ? $this->defaults[$name] : null;

			$prefixedName = $this->prefix . $name;
			$ret = array_key_exists( $prefixedName, $repo ) ? $repo[ $prefixedName ] : $default;
			$ret = maybe_unserialize( $ret );

			if( null !== $ret ){
				$this->loaded[ $name ] = $ret;
			}
		}

		if( $wantArray && (! is_array($ret)) ){
			$ret = ( null === $ret ) ? array() : array($ret);
		}

		if( (! $wantArray) && is_array($ret) ){
			$ret = array_shift( $ret );
		}

		return $ret;
	}

	public function get_( $name, $wantArray = false )
	{
		$ret = null;

		if( array_key_exists($name, $this->loaded) ){
			$ret = $this->loaded[$name];
		}
		else {
			$default = array_key_exists($name, $this->defaults) ? $this->defaults[$name] : NULL;
			$ret = get_option( $this->prefix . $name, $default );

			if( NULL !== $ret ){
				$this->loaded[$name] = $ret;
			}
		}

		if( $wantArray && (! is_array($ret)) ){
			$ret = ( null === $ret ) ? array() : array($ret);
		}

		if( (! $wantArray) && is_array($ret) ){
			$ret = array_shift( $ret );
		}

		return $ret;
	}
}