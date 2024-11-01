<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
#[AllowDynamicProperties]
class SH4_Employees_Html_Admin_Controller_New
{
	public function __construct(
		HC3_Hooks $hooks,
		HC3_Post $post,

		SH4_Calendars_Query $calendarsQuery,
		SH4_Employees_Query $employeesQuery,
		SH4_App_Query $appQuery,
		SH4_App_Command $appCommand,
		SH4_Employees_Command $command
		)
	{
		$this->post = $hooks->wrap($post);
		$this->command = $hooks->wrap($command);

		$this->employeesQuery = $hooks->wrap( $employeesQuery );

		$this->appQuery = $hooks->wrap( $appQuery );
		$this->appCommand = $hooks->wrap( $appCommand );
		$this->calendarsQuery = $hooks->wrap( $calendarsQuery );
	}

	public function execute()
	{
		$title = $this->post->get('title');
		$description = $this->post->get('description');

		$calendarIds = $this->post->get('calendar');
		if( ! $calendarIds ){
			$calendarIds = array();
		}

		$employeeId = $this->command->create( $title, $description );

		if( $employeeId ){
			if( ! $calendarIds ){
				$calendarIds = array();
			}
			$calendars = $calendarIds ? $this->calendarsQuery->findManyActiveById( $calendarIds ) : array();

			if( $calendars ){
				$employee = $this->employeesQuery->findById( $employeeId );
				reset( $calendars );
				foreach( $calendars as $calendar ){
					$this->appCommand->addEmployeeToCalendar( $employee, $calendar );
				}
			}
		}

		$return = array( 'admin/employees', '__New Employee Added__' );
		return $return;
	}
}