<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
interface HC3_IHooks
{
	public function apply( $hook, $thing );
	public function add( $hook, $callable );
	public function wrap( $obj );
	public function exists( $hook );
}