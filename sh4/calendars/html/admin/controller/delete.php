<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
#[AllowDynamicProperties]
class SH4_Calendars_Html_Admin_Controller_Delete
{
	public function __construct(
		HC3_Hooks $hooks,
		SH4_Calendars_Query $query,
		SH4_Calendars_Command $command
		)
	{
		$this->query = $hooks->wrap($query);
		$this->command = $hooks->wrap($command);
	}

	public function execute( $id )
	{
		$model = $this->query->findById( $id );
		$this->command->delete( $model );

		$return = array( 'admin/calendars', '__Calendar Deleted__' );
		return $return;
	}
}