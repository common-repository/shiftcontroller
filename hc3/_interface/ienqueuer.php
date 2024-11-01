<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
interface HC3_IEnqueuer
{
	public function addScript( $handle, $path );
	public function addStyle( $handle, $path );
	public function getScripts();
	public function getStyles();
}