<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
#[AllowDynamicProperties]
class SH4_Upgrade3_Command
{
	public function __construct(
		HC3_Hooks $hooks,
		HC3_Dic $dic,

		HC3_Time $t,

		SH4_App_Command $appCommand,

		HC3_Users_Query $usersQuery,

		SH4_ShiftTypes_Presenter $shiftTypesPresenter,
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
		$this->t = $t;
		$this->dic = $dic;

		$this->appCommand = $hooks->wrap( $appCommand );
		$this->usersQuery = $hooks->wrap( $usersQuery );

		$this->shiftTypesPresenter = $hooks->wrap($shiftTypesPresenter);
		$this->shiftTypes = $hooks->wrap($shiftTypes);
		$this->shiftTypesCommand = $hooks->wrap($shiftTypesCommand);

		$this->calendars = $hooks->wrap($calendars);
		$this->calendarsCommand = $hooks->wrap($calendarsCommand);

		$this->employees = $hooks->wrap($employees);
		$this->employeesCommand = $hooks->wrap($employeesCommand);

		$this->shifts = $hooks->wrap($shifts);
		$this->shiftsCommand = $hooks->wrap($shiftsCommand);

		$this->self = $hooks->wrap($this);
	}

	public function upgrade( $oldLocations, $oldEmployees, $oldShifts, $importUsers = FALSE )
	{
// echo "OLD SHIFTS: " . count( $oldShifts );
// exit;
ini_set( 'memory_limit', '600M' );
set_time_limit( 3000 );

$onlyCalendars = [];

		$out = array();

		$this->shiftsCommand->deleteAll();

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

	// employees
		$this->employeesCommand->deleteAll();

		$newEmployeesIds = array();
		$oldToNew_Employees = array();
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

		if( $importUsers ){
			$usersCommand = $this->dic->make('HC3_Users_Command');
			$usersCommand->deleteAll();

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

				if( ! $e['username'] ){
					$e['username'] = $e['email'];
				}

				$array = array();
				$array['display_name'] = $title;
				$array['username'] = $e['username'];
				$array['email'] = $e['email'];
				$array['token'] = $e['token'];
				$array['status'] = $e['active'] ? 'active' : NULL;
				$array['role'] = ($e['level'] == 3) ? 'admin' : NULL;
				$array['id'] = $e['id'];
				$array['hashed_password'] = $e['password'];

				try {
					$newId = $usersCommand->create( $array['display_name'], $array['username'], $array['email'], $array['role'], $array );
				}
				catch( HC3_ExceptionArray $e ){
					echo "ERROR!";
					_print_r( $e->getErrors() );
					exit;
					continue;
				}

				$newUsersIds[] = $newId;
			}
		}
		else {
			$newUsersIds = array_keys( $oldToNew_Employees );
		}

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
		reset( $newEmployees );
		foreach( $newEmployees as $employee ){
			if( ! $employee->isActive() ){
				continue;
			}
			$this->appCommand->addEmployeeToCalendar( $employee, $newTimeoffCalendar );
		}

// echo count( $oldShifts );
// exit;
		// $oldShifts = [];
		// $oldShifts = array_slice( $oldShifts, 0, 20000 );

	// shifts
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

			if( ! array_key_exists($newCalendarId, $newCalendars) ){
				continue;
			}
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
				$this->shiftsCommand->create( $calendar, $start, $end, $employee, NULL, NULL, $status );
			}
			catch( HC3_ExceptionArray $e ){
				echo "ERROR!";
				_print_r( $e->getErrors() );
				exit;
			}
		}
	}
}