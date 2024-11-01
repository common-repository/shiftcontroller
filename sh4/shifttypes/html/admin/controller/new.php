<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class SH4_ShiftTypes_Html_Admin_Controller_New
{
	public $post, $command;

	public function __construct(
		HC3_Hooks $hooks,
		HC3_Post $post,

		SH4_ShiftTypes_Command $command
		)
	{
		$this->post = $hooks->wrap($post);

		$this->command = $hooks->wrap($command);
	}

	public function executeHours()
	{
		$title = $this->post->get('title');

		$time = $this->post->get('time');
		list( $start, $end ) = explode( '-', $time );

		$startBreak = $endBreak = NULL;
		$breakOn = $this->post->get('break_on');
		if( $breakOn ){
			$break = $this->post->get('break');
			list( $startBreak, $endBreak ) = explode( '-', $break );
		}

		$this->command->createHours( $title, $start, $end, $startBreak, $endBreak );

		$return = array( 'admin/shifttypes', '__New Shift Time Added__' );
		return $return;
	}

	public function executeDays()
	{
		$title = $this->post->get('title');
		$min = $this->post->get('start');
		$max = $this->post->get('end');

		$this->command->createDays( $title, $min, $max );

		$return = array( 'admin/shifttypes', '__New Shift Time Added__' );
		return $return;
	}
}