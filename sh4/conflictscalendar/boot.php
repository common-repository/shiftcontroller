<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class SH4_ConflictsCalendar_Boot
{
	public function __construct(
		SH4_Shifts_Conflicts $conflicts
		)
	{
		$conflicts
			->register( 'SH4_ConflictsCalendar_Overlap' )
			;
	}
}