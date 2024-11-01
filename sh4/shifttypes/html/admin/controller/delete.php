<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class SH4_ShiftTypes_Html_Admin_Controller_Delete
{
	public $query, $command;

	public function __construct(
		HC3_Hooks $hooks,
		SH4_ShiftTypes_Query $query,
		SH4_ShiftTypes_Command $command
		)
	{
		$this->command = $hooks->wrap($command);
		$this->query = $hooks->wrap($query);
	}

	public function execute( $id )
	{
		$model = $this->query->findById( $id );

		$this->command->delete( $model );

		$return = array( 'admin/shifttypes', '__Deleted__' );
		return $return;
	}
}