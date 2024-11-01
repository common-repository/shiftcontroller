<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
#[AllowDynamicProperties]
class SH4_Employees_Html_Admin_Controller_Calendars
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

	public function execute( $employeeId )
	{
		$employee = $this->employeesQuery->findById( $employeeId );

		$current = $this->appQuery->findCalendarsForEmployee( $employee );
		$currentIds = array_keys( $current );

		$newIds = $this->post->get('calendar');
		if( ! $newIds ){
			$newIds = array();
		}

		$toAddIds = array_diff( $newIds, $currentIds );
		$toRemoveIds = array_diff( $currentIds, $newIds );

		if( $toAddIds ){
			$toAdd = $this->calendarsQuery->findManyActiveById( $toAddIds );
			foreach( $toAdd as $calendar ){
				$this->appCommand->addEmployeeToCalendar( $employee, $calendar );
			}
		}

		if( $toRemoveIds ){
			$toRemove = $this->calendarsQuery->findManyActiveById( $toRemoveIds );
			foreach( $toRemove as $calendar ){
				$this->appCommand->removeEmployeeFromCalendar( $employee, $calendar );
			}
		}

		$return = array( 'admin/employees', '__Employee Updated__' );
		return $return;
	}
}