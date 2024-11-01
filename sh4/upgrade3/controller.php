<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
#[AllowDynamicProperties]
class SH4_Upgrade3_Controller
{
	public function __construct(
		HC3_Hooks $hooks,
		SH4_Upgrade3_Command $upgradeCommand,
		HC3_Time $t,

		SH4_App_Command $appCommand,
		HC3_Users_Query $usersQuery,

		SH4_ShiftTypes_Query $shiftTypes,
		SH4_ShiftTypes_Command $shiftTypesCommand,

		SH4_Calendars_Query $calendars,
		SH4_Calendars_Command $calendarsCommand,

		SH4_Employees_Query $employees,
		SH4_Employees_Command $employeesCommand,

		SH4_Shifts_Query $shifts,
		SH4_Shifts_Command $shiftsCommand

		)
	{
ini_set( 'memory_limit', '600M' );
set_time_limit( 3000 );

		$this->t = $t;
		$this->upgradeCommand = $hooks->wrap( $upgradeCommand );
		$this->self = $hooks->wrap($this);

		$this->appCommand = $hooks->wrap( $appCommand );
		$this->usersQuery = $hooks->wrap( $usersQuery );

		$this->shiftTypes = $hooks->wrap($shiftTypes);
		$this->shiftTypesCommand = $hooks->wrap($shiftTypesCommand);

		$this->calendars = $hooks->wrap($calendars);
		$this->calendarsCommand = $hooks->wrap($calendarsCommand);

		$this->employees = $hooks->wrap($employees);
		$this->employeesCommand = $hooks->wrap($employeesCommand);

		$this->shifts = $hooks->wrap($shifts);
		$this->shiftsCommand = $hooks->wrap($shiftsCommand);
	}

	public function execute()
	{
		global $wpdb;
		$prfx = $wpdb->prefix . 'shiftcontroller_v3_';

		$sql = "SELECT * FROM {$prfx}locations";
		$oldLocations = $wpdb->get_results( $sql, ARRAY_A );

		$level = 1;
		$sql = "SELECT * FROM {$prfx}users WHERE level & $level";
		$oldEmployees = $wpdb->get_results( $sql, ARRAY_A );

		$sql = "SELECT * FROM {$prfx}shifts";
		$oldShifts = $wpdb->get_results( $sql, ARRAY_A );

		$this->upgradeCommand->upgrade( $oldLocations, $oldEmployees, $oldShifts );

		return array('schedule', '__Upgraded__');
	}

	public function get()
	{
		global $wpdb;
		$prfx = $wpdb->prefix . 'shiftcontroller_v3_';

		$step = isset( $_GET['step'] ) ? $_GET['step'] : '';

		$sql = "SELECT * FROM {$prfx}locations";
		$oldLocations = $wpdb->get_results( $sql, ARRAY_A );

		$level = 1;
		$sql = "SELECT * FROM {$prfx}users WHERE level & $level";
		$oldEmployees = $wpdb->get_results( $sql, ARRAY_A );

		$sql = "SELECT * FROM {$prfx}shifts";
		$oldShifts = $wpdb->get_results( $sql, ARRAY_A );

		$this->upgradeCommand->upgrade( $oldLocations, $oldEmployees, $oldShifts );

		echo 'Locations: ' . count( $oldLocations ) . '<br>';
		echo 'Employees: ' . count( $oldEmployees ) . '<br>';
		echo 'Shifts: ' . count( $oldShifts ) . '<br>';

		// echo "__Upgraded__";
		exit;

		return array('schedule', '__Upgraded__');
	}

	public function old()
	{
		global $wpdb;
		$prfx = $wpdb->prefix . 'shiftcontroller_v3_';

		$sql = "SELECT * FROM {$prfx}locations";
		$oldLocations = $wpdb->get_results( $sql, ARRAY_A );

		$level = 1;
		$sql = "SELECT * FROM {$prfx}users WHERE level & $level";
		$oldEmployees = $wpdb->get_results( $sql, ARRAY_A );

		$sql = "SELECT * FROM {$prfx}shifts";
		$oldShifts = $wpdb->get_results( $sql, ARRAY_A );

		$ret = array( $oldLocations, $oldEmployees, $oldShifts );
		return $ret;
	}

	public function get1()
	{
		list( $oldLocations, $oldEmployees, $oldShifts ) = $this->old();

$onlyCalendars = [
];

	// shift types
		$this->shiftTypesCommand->deleteAll();
		$newShiftTypesIds = array();

		$newShiftTypesIds = array();
		try {
			$newShiftTypesIds[] = $this->shiftTypesCommand->createHours( 'Full Time', 9*60*60, 18*60*60, 13*60*60, 14*60*60 );
			$newShiftTypesIds[] = $this->shiftTypesCommand->createHours( 'Morning', 9*60*60, 13*60*60 );
			$newShiftTypesIds[] = $this->shiftTypesCommand->createHours( 'Evening', 17*60*60, 21*60*60 );
			$holidaysId = $this->shiftTypesCommand->createDays( 'Holidays', 2, 30 );
		}
		catch( HC3_ExceptionArray $e ){
			echo "ERROR!";
			_print_r( $e->getErrors() );
			exit;
		}

		$newShiftTypes = $this->shiftTypes->findManyById( $newShiftTypesIds );
		$holidaysShiftType = $this->shiftTypes->findById( $holidaysId );

	// locations
		$this->calendarsCommand->deleteAll();

		$newCalendarsIds = array();
		$oldToNew_Calendars = array();
		foreach( $oldLocations as $e ){
			if( $onlyCalendars ){
				if( ! in_array($e['name'], $onlyCalendars) ) continue;
			}

			try {
				$newId = $this->calendarsCommand->create( $e['name'], $e['color'], $e['description'] );
			}
			catch( HC3_ExceptionArray $e ){
				echo "ERROR!";
				_print_r( $e->getErrors() );
				exit;
			}

			$oldToNew_Calendars[ $e['id'] ] = $newId;
			$newCalendarsIds[] = $newId;
		}

		$newCalendars = $this->calendars->findManyActiveById( $newCalendarsIds );
		reset( $newCalendars );
		foreach( $newCalendars as $calendar ){
			reset( $newShiftTypes );
			foreach( $newShiftTypes as $shiftType ){
				$this->appCommand->addShiftTypeToCalendar( $shiftType, $calendar );
			}
		}

	// timeoff
		try {
			$newTimeoffId = $this->calendarsCommand->create( 'Time Off', '#a9a9a9' );
		}
		catch( HC3_ExceptionArray $e ){
			echo "ERROR!";
			_print_r( $e->getErrors() );
			exit;
		}

		$newTimeoffCalendar = $this->calendars->findById( $newTimeoffId );
		$this->appCommand->addShiftTypeToCalendar( $holidaysShiftType, $newTimeoffCalendar );

		$newCalendars[ $newTimeoffId ] = $newTimeoffCalendar;

		echo "NEW CALENDARS: " . count( $newCalendars ) . '<br>';
		exit;
	}

	public function get2()
	{
		list( $oldLocations, $oldEmployees, $oldShifts ) = $this->old();

		$newEmployeesIds = array();
		$oldToNew_Employees = array();

		$nextOffset = null;
		$perStep = 100;
		$totalCount = count($oldEmployees);

		$offset = 0;
		if( $totalCount > $perStep ){
			$offset = isset( $_GET['offset'] ) ? $_GET['offset'] : 0;

			if( ! $offset ){
			// employees
				$this->employeesCommand->deleteAll();
			}

			$nextOffset = $offset + $perStep;
			if( $nextOffset > $totalCount ) $nextOffset = NULL;

			$oldEmployees = array_slice( $oldEmployees, $offset, $perStep );
		}
		else {
		// employees
			$this->employeesCommand->deleteAll();
		}

		reset( $oldEmployees );
		foreach( $oldEmployees as $e ){
			$title = array();
			if( strlen($e['first_name']) ){
				$title[] = $e['first_name'];
			}
			if( strlen($e['last_name']) ){
				$title[] = $e['last_name'];
			}
			$title = join(' ', $title);

			$try = 1;
			$newId = 0;
			$srcTitle = $title;

			while( ! $newId ){
				try {
					$newId = $this->employeesCommand->create( $title );
					if( ! $e['active'] ){
						$newEmpl = $this->employees->findById( $newId );
						$this->employeesCommand->archive( $newEmpl );
					}
				}
				catch( HC3_ExceptionArray $ex ){
					$errors = $ex->getErrors();
					if( isset($errors['title']) ){
						$try++;
						$title = $srcTitle . ' (' . $try . ')';
					}
					else {
						echo "ERROR IN EMPLOYEES!";
						_print_r( $errors );
						exit;
					}
				}
			}

			$oldToNew_Employees[ $e['id'] ] = $newId;
			$newEmployeesIds[] = $newId;
		}

		$newUsersIds = array_keys( $oldToNew_Employees );

		$newUsers = $this->usersQuery->findManyById( $newUsersIds );

		$newEmployees = $this->employees->findManyActiveById( $newEmployeesIds );
		$openShiftEmployee = $this->employees->findById( 0 );

		reset( $newUsers );
		foreach( $newUsers as $user ){
			$userId = $user->getId();
			$employeeId = $oldToNew_Employees[ $userId ];
			if( ! isset($newEmployees[ $employeeId ]) ){
				continue;
			}
			$employee = $newEmployees[ $employeeId ];
			$this->appCommand->linkEmployeeToUser( $employee, $user );
		}

		echo "NEW EMPLOYEES: " . $offset . '-' . ($offset + count($newEmployeesIds) ) . '/' . $totalCount . '<br>';

		if( $nextOffset ){
			echo '<a href="admin.php?page=shiftcontroller4&hca=upgrade3-2&offset=' . $nextOffset . '">next</a>' . '<br>';
		}
		exit;
	}

	public function get3()
	{
		$newCalendars = $this->calendars->findAll();
		$newEmployees = $this->employees->findAll();
		$openShiftEmployee = $this->employees->findById( 0 );

		reset( $newCalendars );
		foreach( $newCalendars as $calendar ){
			reset( $newEmployees );
			foreach( $newEmployees as $employee ){
				if( ! $employee->isActive() ){
					continue;
				}
				$this->appCommand->addEmployeeToCalendar( $employee, $calendar );
			}
			$this->appCommand->addEmployeeToCalendar( $openShiftEmployee, $calendar );
		}

		echo "NEW EMPLOYEES TO CALENDARS: " . count( $newEmployees ) . ' - ' . count( $newCalendars ) . '<br>';
		exit;
	}

	public function get4()
	{
		list( $oldLocations, $oldEmployees, $oldShifts ) = $this->old();

		$nextOffset = null;
		$perStep = 100;
		$totalCount = count($oldShifts);

		$offset = 0;
		if( $totalCount > $perStep ){
			$offset = isset( $_GET['offset'] ) ? $_GET['offset'] : 0;

			if( ! $offset ){
				$this->shiftsCommand->deleteAll();
			}

			$nextOffset = $offset + $perStep;
			if( $nextOffset > $totalCount ) $nextOffset = NULL;

			$oldShifts = array_slice( $oldShifts, $offset, $perStep );
		}
		else {
			$this->shiftsCommand->deleteAll();
		}

		$newCalendars = $this->calendars->findAll();
		$newEmployees = $this->employees->findAll();

		$newTimeoffId = NULL;
		foreach( $newCalendars as $e ){
			if( $e->isTimeoff() ){
				$newTimeoffId = $e->getId();
				break;
			}
		}

		$oldToNew_Calendars = array();
		foreach( $oldLocations as $e ){
			reset( $newCalendars );
			foreach( $newCalendars as $e2 ){
				if( $e2->getTitle() == $e['name'] ){
					$oldToNew_Calendars[ $e['id'] ] = $e2->getId();
					break;
				}
			}
		}

		$oldToNew_Employees = array();
		foreach( $oldEmployees as $e ){
			$title = array();
			if( strlen($e['first_name']) ){
				$title[] = $e['first_name'];
			}
			if( strlen($e['last_name']) ){
				$title[] = $e['last_name'];
			}
			$title = join(' ', $title);

			reset( $newEmployees );
			foreach( $newEmployees as $e2 ){
				if( $e2->getTitle() == $title ){
					$oldToNew_Employees[ $e['id'] ] = $e2->getId();
					break;
				}
			}
		}

	// shifts
		$newShiftIds = array();
		foreach( $oldShifts as $e ){
		// timeoff
			if( 2 == $e['type'] ){
				$newCalendarId = $newTimeoffId;
			}
			else {
				if( ! isset($oldToNew_Calendars[$e['location_id']]) ){
					continue;
				}
				$newCalendarId = $oldToNew_Calendars[$e['location_id']];
			}

			if( ! $newCalendarId ) continue;
			$calendar = $newCalendars[ $newCalendarId ];

			if( $e['user_id'] ){
				if( ! isset($oldToNew_Employees[$e['user_id']]) ){
					continue;
				}
				$newEmployeeId = $oldToNew_Employees[$e['user_id']];
			}
			else {
				$newEmployeeId = 0;
			}

			if( ! array_key_exists($newEmployeeId, $newEmployees) ){
				continue;
			}
			$employee = $newEmployees[ $newEmployeeId ];

			$start = $this->t->setDateDb( $e['date'] )->modify('+' . $e['start'] . ' seconds')->formatDateTimeDb();
			$end = $this->t->setDateDb( $e['date_end'] )->modify('+' . $e['end'] . ' seconds')->formatDateTimeDb();

			$status = ( $e['status'] == 1 ) ? SH4_Shifts_Model::STATUS_PUBLISH : SH4_Shifts_Model::STATUS_DRAFT;

			try {
				$newShiftIds[] = $this->shiftsCommand->create( $calendar, $start, $end, $employee, NULL, NULL, $status );
			}
			catch( HC3_ExceptionArray $e ){
				echo "ERROR!";
				_print_r( $e->getErrors() );
				exit;
			}
		}

		echo "NEW SHIFTS: " . $offset . '-' . ($offset + count( $newShiftIds )) . '/' . $totalCount . '<br>';

		// $nextOffset = $offset + count($newShiftIds);
		// if( $nextOffset > $totalCount ) $nextOffset = NULL;

		if( $nextOffset ){
			$to = 'admin.php?page=shiftcontroller4&hca=upgrade3-4&offset=' . $nextOffset;
			$html = "<META http-equiv=\"refresh\" content=\"0;URL=$to\">";
			echo '<a href="' . $to . '">next</a>' . '<br>';
			echo $html;
		}
		exit;
	}
}