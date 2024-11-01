<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
#[AllowDynamicProperties]
class SH4_Employees_Html_Admin_Controller_Delete
{
	public function __construct(
		HC3_Hooks $hooks,
		HC3_Post $post,

		SH4_Employees_Command $command,
		SH4_Employees_Query $query
		)
	{
		$this->post = $post;
		$this->command = $hooks->wrap($command);
		$this->query = $hooks->wrap($query);
	}

	public function execute( $id )
	{
		$model = $this->query->findById( $id );
		$this->command->delete( $model );

		$return = array( array('admin/employees', array('status' => 'archive')), '__Employee Deleted__' );
		return $return;
	}
}