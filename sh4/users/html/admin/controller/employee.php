<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
#[AllowDynamicProperties]
class SH4_Users_Html_Admin_Controller_Employee
{
	public function __construct(
		HC3_Hooks $hooks,

		SH4_App_Query $appQuery,
		SH4_App_Command $appCommand,
		SH4_Employees_Query $employeesQuery,
		HC3_Users_Query $usersQuery
		)
	{
		$this->appQuery = $hooks->wrap($appQuery);
		$this->appCommand = $hooks->wrap($appCommand);
		$this->usersQuery = $hooks->wrap($usersQuery);
		$this->employeesQuery = $hooks->wrap($employeesQuery);
	}

	public function execute( $userId, $employeeId )
	{
		$employee = NULL;
		if( $employeeId ){
			$employee = $this->employeesQuery->findById( $employeeId );
		}
		$user = $this->usersQuery->findById( $userId );

		if( $employee ){
			$this->appCommand->linkEmployeeToUser( $employee, $user );
			$return = array( 'admin/users', '__Employee Linked To User Account__' );
		}
		else {
			$employee = $this->appQuery->findEmployeeByUser( $user );
			if( $employee ){
				$this->appCommand->unlinkEmployeeFromUser( $employee );
			}
			$return = array( 'admin/users', '__Employee Unlinked From User Account__' );
		}

		return $return;
	}
}