<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
#[AllowDynamicProperties]
class SH4_Shifts_Controller_Publish
{
	public function __construct(
		HC3_Hooks $hooks,
		HC3_Post $post,

		SH4_Shifts_Query $shiftsQuery,
		SH4_Shifts_Command $shiftsCommand
		)
	{
		$this->post = $post;
		$this->shiftsQuery = $hooks->wrap( $shiftsQuery );
		$this->shiftsCommand = $hooks->wrap( $shiftsCommand );
	}

	public function execute( $id )
	{
		$model = $this->shiftsQuery->findById( $id );

		$this->shiftsCommand->publish( $model );

		$date = $model->getDateStart();
		$to = 'schedule';

		$return = array( $to, '__Shift Published__' );
		return $return;
	}
}