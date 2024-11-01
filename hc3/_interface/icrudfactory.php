<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
interface HC3_ICrudFactory
{
	public function make( $entity, $multi = TRUE );
}