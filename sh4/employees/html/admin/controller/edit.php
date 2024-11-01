<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
#[AllowDynamicProperties]
class SH4_Employees_Html_Admin_Controller_Edit
{
	public function __construct(
		HC3_Hooks $hooks,
		HC3_Post $post,

		SH4_Employees_Command $command,
		SH4_Employees_Query $query
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
		$description = $this->post->get('description');

		$this->command->changeTitle( $model, $title, $description );

		$return = array( 'admin/employees', '__Employee Updated__' );
		return $return;
	}
}