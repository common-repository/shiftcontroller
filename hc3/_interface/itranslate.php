<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
interface HC3_ITranslate
{
	public function translate( $str );
	public function __( $str );
	public function _x( $str, $context );
	public function _n( $singular, $plural, $count );
	public function getLocale();
	public function getOptions();
}