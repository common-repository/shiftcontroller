<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
#[AllowDynamicProperties]
class SH4_Shifts_Controller_Calendar
{
	public function __construct(
		HC3_Hooks $hooks,
		HC3_Post $post,

		SH4_Shifts_Query $query,
		SH4_Shifts_Command $command,
		SH4_Calendars_Query $calendars
		)
	{
		$this->post = $post;

		$this->query = $hooks->wrap($query);
		$this->command = $hooks->wrap($command);
		$this->calendars = $hooks->wrap($calendars);
	}

	public function execute( $id, $calendarId )
	{
		$calendar = $this->calendars->findById( $calendarId );

		$shift = $this->query->findById( $id );
		$this->command->changeCalendar( $shift, $calendar );

		$to = 'schedule';

		$msg = '__Calendar Changed__';

		$return = array( $to, $msg );
		return $return;
	}
}