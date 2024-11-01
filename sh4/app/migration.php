<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class SH4_App_Migration
{
	public $migrationService, $appCommand, $upgradeQuery, $upgradeCommand, $calendarsCommand, $calendarsQuery, $shiftTypesCommand, $shiftTypesQuery, $employeesCommand, $employeesQuery;

	public function __construct(
		HC3_Hooks $hooks,
		HC3_MigrationService $migrationService,

		SH4_App_Command $appCommand,

		SH4_Upgrade3_Query $upgradeQuery,
		SH4_Upgrade3_Command $upgradeCommand,

		SH4_Calendars_Command $calendarsCommand,
		SH4_Calendars_Query $calendarsQuery,

		SH4_ShiftTypes_Command $shiftTypesCommand,
		SH4_ShiftTypes_Query $shiftTypesQuery,

		SH4_Employees_Command $employeesCommand,
		SH4_Employees_Query $employeesQuery
		)
	{
		$this->migrationService = $migrationService;

		$this->appCommand = $hooks->wrap( $appCommand );

		$this->upgradeQuery = $upgradeQuery;
		$this->upgradeCommand = $upgradeCommand;

		$this->calendarsCommand = $hooks->wrap( $calendarsCommand );
		$this->calendarsQuery = $hooks->wrap( $calendarsQuery );

		$this->shiftTypesCommand = $hooks->wrap( $shiftTypesCommand );
		$this->shiftTypesQuery = $hooks->wrap( $shiftTypesQuery );

		$this->employeesCommand = $hooks->wrap( $employeesCommand );
		$this->employeesQuery = $hooks->wrap( $employeesQuery );
	}

	public function up()
	{
		$currentVersion = $this->migrationService->getVersion( 'app' );

		if( $currentVersion < 1 ){
			$this->version1();
			$this->migrationService->saveVersion( 'app', 1 );
		}
	}

	public function version1()
	{
		try {
			$shiftTypesIds = array();
			$shiftTypesIds[] = $this->shiftTypesCommand->createHours( 'Full time', 9*60*60, 18*60*60, 13*60*60, 14*60*60 );
			$shiftTypesIds[] = $this->shiftTypesCommand->createHours( 'Morning', 9*60*60, 13*60*60 );
			$shiftTypesIds[] = $this->shiftTypesCommand->createHours( 'Evening', 17*60*60, 21*60*60 );

			$holidaysId = $this->shiftTypesCommand->createDays( 'Many days', 2, 30 );
			$allDayId = $this->shiftTypesCommand->createHours( 'Full day', 0, 24*60*60 );

			$calendarsIds = array();
			$calendarsIds[] = $this->calendarsCommand->create( 'Barista', '#cbe86b' );
			$calendarsIds[] = $this->calendarsCommand->create( 'Security', '#ffb3a7' );
			$calendarsIds[] = $this->calendarsCommand->create( 'Waiter', '#89c4f4' );
			$timeoffId = $this->calendarsCommand->create( 'Time off', '#a9a9a9', NULL, SH4_Calendars_Model::TYPE_TIMEOFF );

			$employeesIds = array();
			$employeesIds[] = $this->employeesCommand->create( 'George' );
			$employeesIds[] = $this->employeesCommand->create( 'Karen' );
			$employeesIds[] = $this->employeesCommand->create( 'Sarah' );

			$holidaysShiftType = $this->shiftTypesQuery->findById( $holidaysId );
			$allDayShiftType = $this->shiftTypesQuery->findById( $allDayId );

			$timeoffCalendar = $this->calendarsQuery->findById( $timeoffId );
			$this->appCommand->addShiftTypeToCalendar( $holidaysShiftType, $timeoffCalendar );
			$this->appCommand->addShiftTypeToCalendar( $allDayShiftType, $timeoffCalendar );

			$calendars = $this->calendarsQuery->findManyActiveById( $calendarsIds );
			$employees = $this->employeesQuery->findManyActiveById( $employeesIds );
			$shiftTypes = $this->shiftTypesQuery->findManyById( $shiftTypesIds );

			$openShiftEmployee = $this->employeesQuery->findById( 0 );
			$customTimeShiftType = $this->shiftTypesQuery->findById( 0 );

			reset( $calendars );
			foreach( $calendars as $calendar ){
				reset( $shiftTypes );
				foreach( $shiftTypes as $shiftType ){
					$this->appCommand->addShiftTypeToCalendar( $shiftType, $calendar );
				}
				$this->appCommand->addShiftTypeToCalendar( $customTimeShiftType, $calendar );
			}

			reset( $calendars );
			foreach( $calendars as $calendar ){
				reset( $employees );
				foreach( $employees as $employee ){
					$this->appCommand->addEmployeeToCalendar( $employee, $calendar );
				}
				$this->appCommand->addEmployeeToCalendar( $openShiftEmployee, $calendar );
			}

			reset( $employees );
			foreach( $employees as $employee ){
				$this->appCommand->addEmployeeToCalendar( $employee, $timeoffCalendar );
			}

			$hasVersion3 = $this->upgradeQuery->hasVersion3();

			if( $hasVersion3 ){
				list( $oldLocations, $oldEmployees, $oldShifts, $importUsers ) = $this->upgradeQuery->findOldData();
				$this->upgradeCommand->upgrade(  $oldLocations, $oldEmployees, $oldShifts, $importUsers );
			}
			else {
			}
		}
		catch( HC3_ExceptionArray $e ){
		}
	}
}