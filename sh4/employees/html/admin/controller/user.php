<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
#[AllowDynamicProperties]
class SH4_Employees_Html_Admin_Controller_User
{
	public function __construct(
		HC3_Hooks $hooks,

		SH4_App_Command $appCommand,
		SH4_Employees_Query $query,
		HC3_Users_Query $usersQuery
		)
	{
		$this->appCommand = $hooks->wrap($appCommand);
		$this->usersQuery = $hooks->wrap($usersQuery);
		$this->query = $hooks->wrap($query);
	}

	public function execute( $id, $userId )
	{
		$employee = $this->query->findById( $id );
		$user = $this->usersQuery->findById( $userId );

		if( $user && $userId ){
			$this->appCommand->linkEmployeeToUser( $employee, $user );
			$return = array( 'admin/employees', '__Employee Linked To User Account__' );
		}
		else {
			$this->appCommand->unlinkEmployeeFromUser( $employee );
			$return = array( 'admin/employees', '__Employee Unlinked From User Account__' );
		}

		return $return;
	}
}