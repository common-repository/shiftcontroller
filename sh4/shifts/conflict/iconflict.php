<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
interface SH4_Shifts_Conflict_IConflict
{
	/**
	* Checks if a shift has a conflict
	*
	* @return array		Array of conflict implementations.
	*/
	public function check( SH4_Shifts_Model $shift );

	/**
	* Displays the conflict details
	*
	* @return string
	*/
	public function render();
}