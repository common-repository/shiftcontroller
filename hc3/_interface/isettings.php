<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
interface HC3_ISettings
{
	public function get( $name, $wantArray = FALSE );
	public function set( $name, $value );
	public function reset( $name );
	public function init( $name, $value );
	public function resetAll();
}