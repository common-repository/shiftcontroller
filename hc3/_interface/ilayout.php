<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
interface HC3_ILayout
{
	public function render( $content );
	public function head();
	public function renderPrint( $content );
}