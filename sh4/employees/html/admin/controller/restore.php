<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
#[AllowDynamicProperties]
class SH4_Employees_Html_Admin_Controller_Restore
{
	public function __construct(
		HC3_Hooks $hooks,
		SH4_Employees_Query $query,
		SH4_Employees_Command $command
		)
	{
		$this->query = $hooks->wrap($query);
		$this->command = $hooks->wrap($command);
	}

	public function execute( $id )
	{
		$object = $this->query->findById( $id );

		$this->command->restore( $object );

		$return = array( 'admin/employees', '__Employee Restored__' );
		return $return;
	}
}