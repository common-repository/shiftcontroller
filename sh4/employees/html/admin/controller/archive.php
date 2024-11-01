<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
#[AllowDynamicProperties]
class SH4_Employees_Html_Admin_Controller_Archive
{
	public function __construct(
		HC3_Hooks $hooks,

		SH4_Employees_Command $command,
		SH4_Employees_Query $query
		)
	{
		$this->command = $hooks->wrap($command);
		$this->query = $hooks->wrap($query);
	}

	public function execute( $id )
	{
		$model = $this->query->findById( $id );
		$this->command->archive( $model );

		$return = array( 'admin/employees', '__Employee Archived__' );
		return $return;
	}
}