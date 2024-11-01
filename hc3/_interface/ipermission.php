<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
interface HC3_IPermission
{
	public function findAdmins();
	public function isAdmin( HC3_Users_Model $user );
}