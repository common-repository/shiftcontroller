<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
interface SH4_Notifications_INotification
{
	public function getDefaultTemplate();
	public function execute( SH4_Shifts_Model $shift, $template );
	public function getTitle();
}