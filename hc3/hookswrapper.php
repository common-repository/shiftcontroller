<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class HC3_HooksWrapper
{
	private $obj = NULL;
	private $h = NULL;

	public function __construct( $obj, HC3_IHooks $hooks )
	{
		$this->obj = $obj;
		$this->h = $hooks;
	}

	public function _getClass()
	{
		return get_class( $this->obj );
	}

	public function __call( $method, $args )
	{
		$hook = get_class($this->obj) . '::' . $method;
		$hook = strtolower( $hook );
		$hook = str_replace( '_', '/', $hook );

		$hookBefore = $hook . '::before';
		$hookAfter = $hook . '::after';

		if( method_exists($this->obj, $method) OR $this->h->exists($hook) OR $this->h->exists($hookAfter) OR $this->h->exists($hookBefore) ){
		// before hook, may alter args or stop execution
			$args = $this->h->apply( $hookBefore, $args );
			if( $args === FALSE ){
				// echo "ESCAPE EXECUTION!";
				// exit;
				return;
				// return $return;
			}

		// own object method
			$return = NULL;
			if( method_exists($this->obj, $method) ){
				$return = call_user_func_array(
					array($this->obj, $method), $args
					);
			}

		// just listen
			$this->h->apply( $hook, $args );

		// after hook, may alter return value
			$return = $this->h->apply( $hookAfter, $return, $args );

			if( $return === $this->obj ){
				return $this;
			}
			else {
				return $return;
			}
		}
		else {
			echo 'Undefined method - ' . get_class($this->obj) . '::' . $method;
			exit;
		}
	}
}