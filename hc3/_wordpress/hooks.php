<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
interface HC3_Wordpress_IHooks extends HC3_IHooks
{
	public function getPrefix();
}

class HC3_Hooks implements HC3_Wordpress_IHooks
{
	protected $pluginName = 'hitcode';
	protected $pluginPrefix = 'hc3';

	protected $_notInstantiated = array();

	public $dic;

	public function __construct( HC3_Dic $dic, $pluginName, $pluginPrefix )
	{
		$this->dic = $dic;
		$this->pluginName = $pluginName;
		$this->pluginPrefix = $pluginPrefix;
	}

	public function getPrefix()
	{
		return $this->pluginPrefix;
	}

	public function wrap( $obj )
	{
		$return = new HC3_HooksWrapper( $obj, $this );
		return $return;
	}

	public function apply( $hook, $thing, $args = NULL )
	{
		$hook = strtolower( $hook );
		if( isset($this->_notInstantiated[$hook]) ){
			foreach( $this->_notInstantiated[$hook] as $callable ){
				$callable[0] = $this->dic->make( $callable[0] );
				$this->add( $hook, $callable );
			}
			unset( $this->_notInstantiated[$hook] );
		}

		$wpHookName = $this->_prepareHookName( $hook );

// echo "FILTER: '$wpHookName'<br>";

		$thing = apply_filters( $wpHookName, $thing, $args );
		return $thing;
	}

	public function exists( $hook )
	{
		$return = FALSE;

		$wpHookName = $this->_prepareHookName( $hook );

		if( has_filter($wpHookName) ){
			$return = TRUE;
		}
		else {
			$hook = strtolower( $hook );
			if( array_key_exists($hook, $this->_notInstantiated) ){
				$return = TRUE;
			}
		}

		return $return;
	}

	public function add( $hook, $callable )
	{
		$wpHookName = $this->_prepareHookName( $hook );

		if( is_array($callable) && (! is_object($callable[0])) ){
			if( ! isset($this->_notInstantiated[$hook]) ){
				$this->_notInstantiated[$hook] = array();
			}
			$this->_notInstantiated[$hook][] = $callable;
		}
		else {
			add_filter( $wpHookName, $callable, 10, 3 );
		}

		return $this;
	}

	protected function _prepareHookName( $hook )
	{
		$hook = strtolower( $hook );
		// $hook = str_replace( '_', '/', $hook );
		$wpHookName = $this->pluginName . '/' . $hook;
		return $wpHookName;
	}
}