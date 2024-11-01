<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
#[AllowDynamicProperties]
class SH4_Calendars_Html_Admin_Controller_Employees
{
	public function __construct(
		HC3_Hooks $hooks,
		HC3_Post $post,

		SH4_Employees_Query $employeesQuery,
		SH4_Calendars_Query $calendarsQuery,

		SH4_App_Query $appQuery,
		SH4_App_Command $appCommand
		)
	{
		$this->post = $hooks->wrap( $post );

		$this->calendarsQuery = $hooks->wrap( $calendarsQuery );
		$this->employeesQuery = $hooks->wrap( $employeesQuery );

		$this->appQuery = $hooks->wrap( $appQuery );
		$this->appCommand = $hooks->wrap( $appCommand );
	}

	public function execute( $calendarId )
	{
		$calendar = $this->calendarsQuery->findById( $calendarId );

		$currentEmployees = $this->appQuery->findEmployeesForCalendar( $calendar );
		$currentEmployeesIds = array_keys( $currentEmployees );

		$employeesIds = $this->post->get('employee');
		if( ! $employeesIds ){
			$employeesIds = array();
		}

		$toAddIds = array_diff( $employeesIds, $currentEmployeesIds );
		$toRemoveIds = array_diff( $currentEmployeesIds, $employeesIds );

		if( $toAddIds ){
			$toAdd = $this->employeesQuery->findManyActiveById( $toAddIds );

			foreach( $toAdd as $employee ){
				$this->appCommand->addEmployeeToCalendar( $employee, $calendar );
			}
		}

		if( $toRemoveIds ){
			$toRemove = $this->employeesQuery->findManyActiveById( $toRemoveIds );
			foreach( $toRemove as $employee ){
				$this->appCommand->removeEmployeeFromCalendar( $employee, $calendar );
			}
		}

		$return = array( 'admin/calendars', '__Calendar Updated__' );
		return $return;
	}
}