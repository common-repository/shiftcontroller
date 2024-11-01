<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class SH4_ShiftTypes_Html_Admin_Controller_Edit
{
	public $post, $query, $command;

	public function __construct(
		HC3_Hooks $hooks,
		HC3_Post $post,

		SH4_ShiftTypes_Query $query,
		SH4_ShiftTypes_Command $command
		)
	{
		$this->post = $hooks->wrap($post);

		$this->query = $hooks->wrap($query);
		$this->command = $hooks->wrap($command);
	}

	public function execute( $id )
	{
		$model = $this->query->findById( $id );

		$title = $this->post->get('title');
		$this->command->changeTitle( $model, $title );

		switch( $model->getRange() ){
			case $model::RANGE_DAYS:
				$start = $this->post->get('start');
				$end = $this->post->get('end');
				$this->command->changeTime( $model, $start, $end );
				break;

			default:
				$time = $this->post->get('time');
				list( $start, $end ) = explode( '-', $time );

				$startBreak = $endBreak = NULL;
				$breakOn = $this->post->get('break_on');

				if( $breakOn ){
					$break = $this->post->get('break');
					list( $startBreak, $endBreak ) = explode( '-', $break );
				}

				$this->command->changeTime( $model, $start, $end, $startBreak, $endBreak );
				break;
		}

		$return = array( 'admin/shifttypes', '__Updated__' );
		return $return;
	}
}