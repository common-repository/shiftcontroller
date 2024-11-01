<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
#[AllowDynamicProperties]
class SH4_Shifts_Controller_Delete
{
	public function __construct(
		HC3_Hooks $hooks,
		HC3_Post $post,

		SH4_Shifts_Query $query,
		SH4_Shifts_Command $command
		)
	{
		$this->post = $post;
		$this->query = $hooks->wrap($query);
		$this->command = $hooks->wrap($command);
	}

	public function execute( $id )
	{
		$model = $this->query->findById( $id );
		$this->command->delete( $model );

		// $to = '-referrer-';
		$to = $this->post->get( 'back' );
		if( $to ){
			$to = json_decode( $to, TRUE );
		}
		else {
			$to = array( 'schedule', array() );
		}

		$return = array( $to, '__Shift Deleted__' );
		return $return;
	}
}