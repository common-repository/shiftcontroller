<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
#[AllowDynamicProperties]
class SH4_Calendars_Html_Admin_Controller_Edit
{
	public function __construct(
		HC3_Hooks $hooks,
		HC3_Post $post,
		SH4_Calendars_Command $command,
		SH4_Calendars_Query $query
		)
	{
		$this->post = $hooks->wrap($post);
		$this->command = $hooks->wrap($command);
		$this->query = $hooks->wrap($query);
	}

	public function execute( $id )
	{
		$model = $this->query->findById( $id );

		$title = $this->post->get('title');
		$color = $this->post->get('color');
		$description = $this->post->get('description');
		$type = $this->post->get('calendar_type');

		$this->command->changeTitle( $model, $title );
		$this->command->changeColor( $model, $color );
		$this->command->changeDescription( $model, $description );
		$this->command->changeType( $model, $type );

		$return = array( 'admin/calendars', '__Calendar Updated__' );
		return $return;
	}
}