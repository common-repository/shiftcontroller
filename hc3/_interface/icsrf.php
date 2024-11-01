<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
interface HC3_ICsrf
{
	public function checkInput();
	public function prepareOutput( $output );
}