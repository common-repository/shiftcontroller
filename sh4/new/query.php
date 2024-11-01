<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
interface SH4_New_IQuery
{
	public function findAllCalendars();
	public function findAllEmployees();
}

class SH4_New_Query implements SH4_New_IQuery
{
	public $self, $auth, $appQuery, $calendarsPermissions, $calendarsQuery, $employeesQuery;

	public function __construct(
		HC3_Hooks $hooks,
		HC3_Auth $auth,

		SH4_App_Query $appQuery,

		SH4_Calendars_Permissions $calendarsPermissions,
		SH4_Calendars_Query $calendarsQuery,
		SH4_Employees_Query $employeesQuery
		)
	{
		$this->auth = $auth;

		$this->appQuery = $hooks->wrap( $appQuery );

		$this->calendarsPermissions = $hooks->wrap( $calendarsPermissions );
		$this->calendarsQuery = $hooks->wrap( $calendarsQuery );
		$this->employeesQuery = $hooks->wrap( $employeesQuery );
	}

	public function findAllEmployees()
	{
		$return = array();

		$currentUser = $this->auth->getCurrentUser();
		$currentUserId = $currentUser->getId();
		if( ! $currentUserId ){
			return $return;
		}

		$allowed = array();

	// as employee
		$employee = $this->appQuery->findEmployeeByUser( $currentUser );
		if( $employee ){
			$employeeId = $employee->getId();
			$allowed[ $employeeId ] = $employee;
		}

	// as manager
		$managedCalendars = $this->appQuery->findCalendarsManagedByUser( $currentUser );
		foreach( $managedCalendars as $calendar ){
			$thisEmployees = $this->appQuery->findEmployeesForCalendar( $calendar );
			$allowed = $allowed + $thisEmployees;
		}

		$allowedIds = array_keys( $allowed );

		if( $allowedIds ){
			$return = $this->employeesQuery->findManyActiveById( $allowedIds );
		}

		return $return;
	}

	public function findAllCalendars()
	{
		$return = array();

		$currentUser = $this->auth->getCurrentUser();
		$currentUserId = $currentUser->getId();
		if( ! $currentUserId ){
			return $return;
		}

		$allowed = array();

	// as employee
		$employee = $this->appQuery->findEmployeeByUser( $currentUser );
		if( $employee ){
			$employeeCalendars = $this->appQuery->findCalendarsForEmployee( $employee );
			foreach( $employeeCalendars as $thisCalendar ){
				$calendarId = $thisCalendar->getId();

				$checkPerms = array( 'employee_create_own_draft', 'employee_create_own_publish' );
				foreach( $checkPerms as $perm ){
					if( $this->calendarsPermissions->get($thisCalendar, $perm) ){
						$allowed[ $calendarId ] = $thisCalendar;
						break;
					}
				}
			}
		}

	// as manager
		$managedCalendars = $this->appQuery->findCalendarsManagedByUser( $currentUser );
		if( $managedCalendars ){
			$allowed = $allowed + $managedCalendars;
		}

		$allowedIds = array_keys( $allowed );

		if( $allowedIds ){
			$return = $this->calendarsQuery->findManyActiveById( $allowedIds );
		}

		return $return;
	}
}