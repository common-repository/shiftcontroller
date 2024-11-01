<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
interface HC3_Interface_AclChecker
{
	public function check( $slug );
}
