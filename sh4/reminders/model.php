<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class SH4_Reminders_Model
{
	const RANGE_DAILY = 'daily';
	const RANGE_DAILY_SMS = 'dailysms';
	const RANGE_WEEKLY = 'weekly';
	const RANGE_MONTHLY = 'monthly';

	public $user;
	public $employee;

	public $shifts = array();
	public $range;
	public $date;
	public $log;
}