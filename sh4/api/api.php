<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
#[AllowDynamicProperties]
class SH4_Api_Api
{
	public $employeePresenter;
	public $appQuery;

	public function __construct(
		HC3_Hooks $hooks,
		HC3_Time $t,

		HC3_IPermission $permission,
		HC3_Translate $translate,

		SH4_Employees_Query	$employeesQuery,
		SH4_Employees_Command	$employeesCommand,
		SH4_Calendars_Query	$calendarsQuery,
		SH4_Calendars_Presenter $calendarPresenter,
		SH4_Shifts_Presenter $shiftsPresenter,

		SH4_App_Query $appQuery,
		SH4_App_Command $appCommand,
		SH4_Employees_Presenter $employeePresenter,

		SH4_Shifts_Conflicts $conflicts,
		// SH4_Shifts_Acl	$shiftsAcl,
		SH4_Shifts_Query $shiftsQuery,
		SH4_Shifts_Command $shiftsCommand
		)
	{
		$this->t = $t;
		$this->translate = $translate;
		$this->permission = $hooks->wrap( $permission );

		$this->employeesQuery = $hooks->wrap( $employeesQuery );
		$this->employeesCommand = $hooks->wrap( $employeesCommand );
		$this->calendarsQuery = $hooks->wrap( $calendarsQuery );
		$this->calendarPresenter = $hooks->wrap( $calendarPresenter );
		$this->employeePresenter = $hooks->wrap( $employeePresenter );
		$this->appQuery = $hooks->wrap( $appQuery );
		$this->appCommand = $hooks->wrap( $appCommand );

		$this->shiftsPresenter = $hooks->wrap( $shiftsPresenter );
		$this->conflicts = $hooks->wrap($conflicts);
		$this->shiftsQuery = $hooks->wrap( $shiftsQuery );
		$this->shiftsCommand = $hooks->wrap( $shiftsCommand );
		// $this->shiftsAcl = $hooks->wrap( $shiftsAcl );
		$this->self = $hooks->wrap( $this );

		add_filter( 'shiftcontroller4/api/shifts/get',			array($this->self, 'shiftsGet'), 10, 1 );
		add_filter( 'shiftcontroller4/api/shifts/getbyid',		array($this->self, 'shiftsGetById'), 10, 1 );
		add_filter( 'shiftcontroller4/api/shifts/create',		array($this->self, 'shiftsCreate'), 10, 1 );
		add_filter( 'shiftcontroller4/api/shifts/deletebyid',	array($this->self, 'shiftsDeleteById'), 10, 1 );
		add_filter( 'shiftcontroller4/api/shifts/updatebyid',	array($this->self, 'shiftsUpdateById'), 10, 2 );

		add_filter( 'shiftcontroller4/api/employees/get', array($this->self, 'employeesGet'), 10, 1 );
		add_filter( 'shiftcontroller4/api/employees/getbyid', array($this->self, 'employeesGetById'), 10, 1 );
		add_filter( 'shiftcontroller4/api/employees/create', array($this->self, 'employeesCreate'), 10, 1 );
		add_filter( 'shiftcontroller4/api/employees/getbyuserid', array($this->self, 'employeesGetByUserId'), 10, 1 );
		add_filter( 'shiftcontroller4/api/employees/createbyuserid', array($this->self, 'employeesCreateByUserId'), 10, 1 );

		add_filter( 'shiftcontroller4/api/calendars/get', array($this->self, 'calendarsGet'), 10, 1 );
		add_filter( 'shiftcontroller4/api/employees/getbycalendarid', array($this->self, 'employeesGetByCalendarId'), 10, 1 );
		add_filter( 'shiftcontroller4/api/employees/addtocalendar', array($this->self, 'employeesAddToCalendar'), 10, 2 );
		add_filter( 'shiftcontroller4/api/employees/removefromcalendar', array($this->self, 'employeesRemoveFromCalendar'), 10, 2 );

		add_filter( 'shiftcontroller4/api/calendars/getbyemployeeid', array($this->self, 'calendarsGetByEmployeeId'), 10, 1 );
	}

	public function toArrayEmployee( array $employeeList )
	{
		$out = array();

		foreach( $employeeList as $employee ){
			$thisOut = $this->employeePresenter->export( $employee, true );

			$keys = array_keys( $thisOut );
			reset( $keys );
			foreach( $keys as $k ){
				$thisOut[$k] = $this->translate->translate( $thisOut[$k] );
			}

			$out[] = $thisOut;
		}

		return $out;
	}

	public function toArrayCalendar( array $list )
	{
		$out = array();

		foreach( $list as $e ){
			$thisOut = $this->calendarPresenter->export( $e, true );

			$keys = array_keys( $thisOut );
			reset( $keys );
			foreach( $keys as $k ){
				$thisOut[$k] = $this->translate->translate( $thisOut[$k] );
			}

			$out[] = $thisOut;
		}

		return $out;
	}

	public function toArray( array $shifts )
	{
		$out = array();

		foreach( $shifts as $shift ){
			$thisOut = $this->shiftsPresenter->export( $shift, TRUE );

			$keys = array_keys( $thisOut );
			reset( $keys );
			foreach( $keys as $k ){
				$thisOut[$k] = $this->translate->translate( $thisOut[$k] );
			}

			$out[] = $thisOut;
		}

		return $out;
	}

	public function availableEmployeesGet( $queryParams = array() )
	{
		$calendarIdList = isset( $queryParams['calendar_id'] ) ? $queryParams['calendar_id'] : array();
		if( ! is_array($calendarIdList) ) $calendarIdList = array( $calendarIdList );

		if( isset($queryParams['from']) ){
			$fromParam = $queryParams['from'];
		// datetime
			if( strlen($fromParam) >= 12 ){
				$start = substr( $fromParam, 0, 12 );
			}
		// date
			else {
				$fromDate = substr( $fromParam, 0, 8 );
				$start = $this->t->setDateDb( $fromDate )->formatDateTimeDb();
			}
		}
		else {
			$fromDate = $this->t->setNow()->formatDateDb();
			$start = $this->t->setDateDb( $fromDate )->formatDateTimeDb();
		}

		if( isset($queryParams['to']) ){
			$toParam = $queryParams['to'];
		// datetime
			if( strlen($toParam) >= 12 ){
				$end = substr( $toParam, 0, 12 );
			}
		// date
			else {
				$toDate = substr( $toParam, 0, 8 );
				$end = $this->t->setDateDb( $toDate )->modify('+1 day')->formatDateTimeDb();
			}
		}
		else {
			$end = $this->t->setDateTimeDb( $start )->modify('+1 day')->formatDateTimeDb();
		}

// echo "$start - $end, $status<br>";
		$employee2calendar = array();

		$calendarList = $calendarIdList ? $this->calendarsQuery->findManyById( $calendarIdList ) : $this->calendarsQuery->findActive();

		foreach( $calendarList as $calendar ){
		// if no explicit calendars requested then leave only shifts
			if( ! $calendarIdList ){
				if( ! $calendar->isShift() ){
					continue;
				}
			}

			$calendarId = $calendar->getId();
			$thisEmployees = $this->appQuery->findEmployeesForCalendar( $calendar );
			foreach( array_keys($thisEmployees) as $employeeId ){
				$employee2calendar[ $employeeId ][ $calendarId ] = $calendar;
			}
		}

		$employees = array();
		if( $employee2calendar ){
			$employees = $this->employeesQuery->findManyActiveById( array_keys($employee2calendar) );
		}
		unset( $employees[0] );

		foreach( array_keys($employees) as $employeeId ){
		// no calendars at all
			if( ! isset($employee2calendar[$employeeId]) ){
				unset( $employees[$employeeId] );
				continue;
			}

			$employee = $employees[ $employeeId ];
			$thisCalendarList = $employee2calendar[ $employeeId ];
			foreach( $thisCalendarList as $calendar ){
				$testModel = new SH4_Shifts_Model( null, $calendar, $start, $end, $employee );
				$conflicts = $this->conflicts->get( $testModel );
				if( $conflicts ){
					unset( $employees[$employeeId] );
					break;
				}
			}
		}

		$ret = array();
		$ret[ 'from' ] = $start;
		$ret[ 'to' ] = $end;
		$ret[ 'employees' ] = $this->self->toArrayEmployee( $employees );

		return $ret;
	}

	public function shiftsGet( $queryParams = array() )
	{
		$status = isset($queryParams['status_id']) ? $queryParams['status_id'] : null;

		$calendarId = isset( $queryParams['calendar_id'] ) ? $queryParams['calendar_id'] : null;
		$employeeId = isset( $queryParams['employee_id']) ? $queryParams['employee_id'] : null;

		if( isset($queryParams['from']) ){
			$fromParam = $queryParams['from'];
		// datetime
			if( strlen($fromParam) >= 12 ){
				$start = substr( $fromParam, 0, 12 );
			}
		// date
			else {
				$fromDate = substr( $fromParam, 0, 8 );
				$start = $this->t->setDateDb( $fromDate )->formatDateTimeDb();
			}
		}
		else {
			$fromDate = $this->t->setNow()->formatDateDb();
			$start = $this->t->setDateDb( $fromDate )->formatDateTimeDb();
		}

		if( isset($queryParams['to']) ){
			$toParam = $queryParams['to'];
		// datetime
			if( strlen($toParam) >= 12 ){
				$end = substr( $toParam, 0, 12 );
			}
		// date
			else {
				$toDate = substr( $toParam, 0, 8 );
				$end = $this->t->setDateDb( $toDate )->modify('+1 day')->formatDateTimeDb();
			}
		}
		else {
			$end = $this->t->setDateTimeDb( $start )->modify('+1 year')->formatDateTimeDb();
		}

// echo "$start - $end, $status<br>";

		$this->shiftsQuery
			->setStart( $start )
			->setEnd( $end )
			;

		$shifts = $this->shiftsQuery->find();

		$default = array( 'status_id', 'calendar_id', 'employee_id', 'from', 'to', 'X-WP-ShiftController-AuthCode' );
		foreach( $default as $k ) unset( $queryParams[$k] );

	// filter shifts
		$ids = array_keys( $shifts );
		foreach( $ids as $id ){
			$shift = $shifts[$id];

			if( $shift->getStart() >= $end ){
				unset( $shifts[$id] );
				continue;
			}

			if( $shift->getEnd() <= $start ){
				unset( $shifts[$id] );
				continue;
			}

			if( NULL !== $status ){
				$shiftStatus = $shift->getStatus();
				if( $shiftStatus != $status ){
					unset( $shifts[$id] );
					continue;
				}
			}

			$shiftCalendar = $shift->getCalendar();
			$shiftCalendarId = $shiftCalendar->getId();

			$shiftEmployee = $shift->getEmployee();
			$shiftEmployeeId = $shiftEmployee->getId();

			if( NULL !== $calendarId ){
				if( 't' == $calendarId ){
					if( ! $shiftCalendar->isTimeoff() ){
						unset( $shifts[$id] );
					}
				}
				elseif( 's' == $calendarId ){
					if( ! $shiftCalendar->isShift() ){
						unset( $shifts[$id] );
					}
				}
				elseif( 'x' != $calendarId ){
					if( $shiftCalendarId != $calendarId ){
						unset( $shifts[$id] );
					}
				}
			}

			if( (NULL !== $employeeId) && ('x' != $employeeId) ){
				if( $shiftEmployeeId != $employeeId ){
					unset( $shifts[$id] );
				}
			}

		// check additional args if we have any
			if( $queryParams ){
				$a = $this->shiftsPresenter->export( $shift );

				reset( $queryParams );
				foreach( $queryParams as $k => $v ){
					if( ! array_key_exists($k, $a) ){
						continue;
					}

					if( $a[$k] != $v ){
						unset( $shifts[$id] );
						break;
					}

					// if( ! array_key_exists($k, $a) ){
						// unset( $shifts[$id] );
						// break;
					// }
					// else {
						// if( $a[$k] != $v ){
							// unset( $shifts[$id] );
							// break;
						// }
					// }
				}
			}
		}

		$ret = $this->self->toArray( $shifts );
		return $ret;
	}

	public function shiftsGetById( $id )
	{
		$shift = $this->shiftsQuery->findById( $id );
		if( ! $shift ){
			return new WP_Error( 'no_shift', 'Not Found', array('status' => 404) );
		}

		$shifts = array( $shift );
		$ret = $this->self->toArray( $shifts );
		$ret= array_shift( $ret );

		return $ret;
	}

	public function shiftsCreate( $values )
	{
		$errors = array();

		$start = isset( $values['start'] ) ? $values['start'] : NULL;
		if( NULL === $start ){
			$errors['start'] = 'Required Field';
		}

		$start = trim( $start );
		if( ! isset($errors['start']) ){
			if( 12 !== strlen($start) ){
				$errors['start'] = 'Format: YYYYMMDDHHMM';
			}
		}

		$end = isset( $values['end'] ) ? $values['end'] : NULL;
		if( NULL === $end ){
			$errors['end'] = 'Required Field';
		}

		$end = trim( $end );
		if( ! isset($errors['end']) ){
			if( 12 !== strlen($end) ){
				$errors['end'] = 'Format: YYYYMMDDHHMM';
			}
		}

		$breakStart = isset( $values['break_start'] ) ? $values['break_start'] : NULL;
		if( NULL !== $breakStart ){
			$breakStart = trim( $breakStart );
			if( ! isset($errors['break_start']) ){
				if( 12 !== strlen($breakStart) ){
					$errors['break_start'] = 'Format: YYYYMMDDHHMM';
				}
			}
		}

		$breakEnd = isset( $values['break_end'] ) ? $values['break_end'] : NULL;
		if( NULL !== $breakEnd ){
			$breakEnd = trim( $breakEnd );
			if( ! isset($errors['break_end']) ){
				if( 12 !== strlen($breakEnd) ){
					$errors['break_end'] = 'Format: YYYYMMDDHHMM';
				}
			}
		}

		if( ! isset($values['calendar_id']) ){
			$errors['calendar_id'] = 'Required Field';
		}
		else {
			$calendarId = $values['calendar_id'];
			$calendar = $this->calendarsQuery->findById( $calendarId );
			if( ! $calendar ){
				$errors['calendar'] = 'Not Found';
			}
		}

		if( ! isset($values['employee_id']) ){
			$errors['employee_id'] = 'Required Field';
		}
		else {
			$employeeId = $values['employee_id'];
			$employee = $this->employeesQuery->findById( $employeeId );
			if( ! $employee ){
				$errors['employee_id'] = 'Not Found';
			}
		}

		$status = isset( $values['status_id'] ) ? $values['status_id'] : SH4_Shifts_Model::STATUS_DRAFT;
		if( ! in_array($status, array(SH4_Shifts_Model::STATUS_DRAFT, SH4_Shifts_Model::STATUS_PUBLISH)) ){
			$errors['status_id'] = 'Wrong Value';
		}

		if( $errors ){
			$errorStr = array();
			foreach( $errors as $k => $v ) $errorStr[] = $k . ': ' . $v;
			$errorStr = join( ', ', $errorStr );
			return new WP_Error( 'error', $errorStr, array('status' => 500) );
		}

		$shift = new SH4_Shifts_Model( NULL, $calendar, $start, $end, $employee, $breakStart, $breakEnd );

		$allowConflict = isset( $values['conflict'] ) ? $values['conflict'] : FALSE;
		if( ! $allowConflict ){
			$conflicts = $this->conflicts->get( $shift );
			if( $conflicts ){
				$errors['conflict'] = 'Conflict';
			}
		}

		if( $errors ){
			$errorStr = array();
			foreach( $errors as $k => $v ) $errorStr[] = $k . ': ' . $v;
			$errorStr = join( ', ', $errorStr );
			return new WP_Error( 'error', $errorStr, array('status' => 500) );
		}

		$shiftId = $this->shiftsCommand->createNew( $shift );
		$shift->setId( $shiftId );

		// $token = $this->self->getToken( $request );
		// $currentUser = $this->auth->getUserByToken( $token );

		if( SH4_Shifts_Model::STATUS_PUBLISH == $status ){
			// $can = $this->shiftsAcl->checkCreatePublished( $shiftId, $currentUser );
			// if( ! $can ){
				// $this->shiftsCommand->delete( $shift );
				// return new WP_Error( 'error', 'Access Denied', array('status' => 500) );
			// }

			$this->shiftsCommand->publish( $shift );
		}

		if( SH4_Shifts_Model::STATUS_DRAFT == $status ){
			// $can = $this->shiftsAcl->checkCreateDraft( $shiftId, $currentUser );
			// if( ! $can ){
				// $this->shiftsCommand->delete( $shift );
				// return new WP_Error( 'error', 'Access Denied', array('status' => 500) );
			// }

			$this->shiftsCommand->draft( $shift );
		}

		return $shiftId;
	}

	public function shiftsDeleteById( $id )
	{
		$shift = $this->shiftsQuery->findById( $id );

		if( ! $shift ){
			return new WP_Error( 'no_shift', 'Not Found', array('status' => 404) );
		}

		// $admins = $this->permission->findAdmins();
		// $currentUser = current( $admins );
		// $can = $this->shiftsAcl->checkDelete( $shift->getId(), $currentUser );

		// if( ! $can ){
			// return new WP_Error( 'error', 'Access Denied', array('status' => 500) );
		// }

		$this->shiftsCommand->delete( $shift );
		return;
	}

	public function shiftsUpdateById( $id, $values )
	{
		$shift = $this->shiftsQuery->findById( $id );

		if( ! $shift ){
			return new WP_Error( 'no_shift', 'Not Found', array('status' => 404) );
		}

		$calendar = $shift->getCalendar();
		$employee = $shift->getEmployee();
		$start = $shift->getStart();
		$end = $shift->getEnd();
		$breakStart = $shift->getBreakStart();
		$breakEnd = $shift->getBreakEnd();

		// $admins = $this->permission->findAdmins();
		// $currentUser = current( $admins );
		// $can = $this->shiftsAcl->checkDelete( $shift->getId(), $currentUser );

		// if( ! $can ){
			// return new WP_Error( 'error', 'Access Denied', array('status' => 500) );
		// }

		$errors = array();

		$newStart = NULL;
		if( isset($values['start']) ){
			$newStart = $values['start'];
			$newStart = trim( $newStart );
			if( ! isset($errors['start']) ){
				if( 12 !== strlen($newStart) ){
					$errors['start'] = 'Format: YYYYMMDDHHMM';
				}
			}

			if( $newStart == $start ) $newStart = NULL;
		}

		$newEnd = NULL;
		if( isset($values['end']) ){
			$newEnd = $values['end'];
			$newEnd = trim( $newEnd );
			if( ! isset($errors['end']) ){
				if( 12 !== strlen($newEnd) ){
					$errors['end'] = 'Format: YYYYMMDDHHMM';
				}
			}

			if( $newEnd == $end ) $newEnd = NULL;
		}

		$newBreakStart = NULL;
		if( isset($values['break_start']) ){
			$newBreakStart = $values['break_start'];
			$newBreakStart = trim( $newBreakStart );
			if( ! isset($errors['break_start']) ){
				if( 12 !== strlen($newBreakStart) ){
					$errors['break_start'] = 'Format: YYYYMMDDHHMM';
				}
			}

			if( $newBreakStart == $breakStart ) $newBreakStart = NULL;
		}

		$newBreakEnd = NULL;
		if( isset($values['break_end']) ){
			$newBreakEnd = $values['break_end'];
			$newBreakEnd = trim( $newBreakEnd );
			if( ! isset($errors['break_end']) ){
				if( 12 !== strlen($newBreakEnd) ){
					$errors['break_end'] = 'Format: YYYYMMDDHHMM';
				}
			}

			if( $newBreakEnd == $breakEnd ) $newBreakEnd = NULL;
		}

		$newCalendar = NULL;
		if( isset($values['calendar_id']) ){
			$calendarId = $values['calendar_id'];
			$newCalendar = $this->calendarsQuery->findById( $calendarId );

			if( ! $newCalendar ){
				$errors['calendar'] = 'Not Found';
			}

			if( $newCalendar && $calendar && ($newCalendar->getId() == $calendar->getId()) ) $newCalendar = NULL;
		}

		$newEmployee = NULL;
		if( isset($values['employee_id']) ){
			$employeeId = $values['employee_id'];
			$newEmployee = $this->employeesQuery->findById( $employeeId );
			if( ! $newEmployee ){
				$errors['employee_id'] = 'Not Found';
			}
			if( $newEmployee && $employee && ($newEmployee->getId() == $employee->getId()) ) $newEmployee = NULL;
		}

		$newStatus = NULL;
		if( isset($values['status_id']) ){
			$newStatus = $values['status_id'];
			if( ! in_array($newStatus, array(SH4_Shifts_Model::STATUS_DRAFT, SH4_Shifts_Model::STATUS_PUBLISH)) ){
				$errors['status_id'] = 'Wrong Value';
				$newStatus = NULL;
			}
		}

		if( $errors ){
			$errorStr = array();
			foreach( $errors as $k => $v ) $errorStr[] = $k . ': ' . $v;
			$errorStr = join( ', ', $errorStr );
			return new WP_Error( 'error', $errorStr, array('status' => 500) );
		}

	// check conflicts
		$allowConflict = isset( $values['conflict'] ) ? $values['conflict'] : FALSE;

		if( (! $allowConflict) && ($newCalendar OR $newEmployee OR $newStart OR $newEnd) ){
			$calendar = $newCalendar ? $newCalendar : $shift->getCalendar();
			$employee = $newEmployee ? $newEmployee : $shift->getEmployee();
			$start = $newStart ? $newStart : $shift->getStart();
			$end = $newEnd ? $newEnd : $shift->getEnd();
			$breakStart = $newBreakStart ? $newBreakStart : $shift->getBreakStart();
			$breakEnd = $newBreakEnd ? $newBreakEnd : $shift->getBreakEnd();

			$newShift = new SH4_Shifts_Model( $id, $calendar, $start, $end, $employee, $breakStart, $breakEnd );

			$conflicts = $this->conflicts->get( $newShift );
			if( $conflicts ){
				$errors['conflict'] = 'Conflict';
			}

			if( $errors ){
				$errorStr = array();
				foreach( $errors as $k => $v ) $errorStr[] = $k . ': ' . $v;
				$errorStr = join( ', ', $errorStr );
				return new WP_Error( 'error', $errorStr, array('status' => 500) );
			}
		}

		if( $newCalendar ){
			$this->shiftsCommand->changeCalendar( $shift, $newCalendar );
		}

		if( $newEmployee ){
			$this->shiftsCommand->changeEmployee( $shift, $newEmployee );
		}

		if( $newStart OR $newEnd OR $newBreakStart OR $newBreakEnd ){
			$this->shiftsCommand->reschedule( $shift, $start, $end, $breakStart, $breakEnd );
		}

		if( $newStatus && (SH4_Shifts_Model::STATUS_PUBLISH == $newStatus) && ( ! $shift->isPublished() ) ){
			$this->shiftsCommand->publish( $shift );
		}

		if( $newStatus && (SH4_Shifts_Model::STATUS_DRAFT == $newStatus) && ( ! $shift->isDraft() ) ){
			$this->shiftsCommand->unpublish( $shift );
		}

		return;
	}

	public function employeesGet( $queryParams = array() )
	{
		$args = array();
		$ret = $this->employeesQuery->read();
		$ret = $this->self->toArrayEmployee( $ret );
		return $ret;
	}

	public function calendarsGet( $queryParams = array() )
	{
		$args = array();
		$ret = $this->calendarsQuery->read();
		$ret = $this->self->toArrayCalendar( $ret );
		return $ret;
	}

	public function employeesGetById( $id )
	{
		$args = array();
		$ret = $this->employeesQuery->findById( $id );
		if( ! $ret ){
			return new WP_Error( 'no_employee', 'Not Found', array('status' => 404) );
		}

		$ret = [ $ret ];
		$ret = $this->self->toArrayEmployee( $ret );
		$ret = current( $ret );
		return $ret;
	}

	public function employeesGetByUserId( $userId )
	{
		$employee = $this->appQuery->findEmployeeByUserId( $userId );
		if( ! $employee ){
			return new WP_Error( 'no_employee', 'Not Found', array('status' => 404) );
		}

		$employees = array( $employee );
		$ret = $this->self->toArrayEmployee( $employees );
		$ret = array_shift( $ret );

		return $ret;
	}

	public function employeesGetByCalendarId( $calendarId )
	{
		$calendar = $this->calendarsQuery->findById( $calendarId );
		if( ! $calendar ){
			return new WP_Error( 'no_calendar', 'Not Found', array('status' => 404) );
		}

		$employees = $this->appQuery->findEmployeesForCalendar( $calendar );
		$ret = $this->self->toArrayEmployee( $employees );

		return $ret;
	}

	public function calendarsGetByEmployeeId( $employeeId )
	{
		$employee = $this->employeesQuery->findById( $employeeId );
		if( ! $employee ){
			return new WP_Error( 'no_employee', 'Not Found', array('status' => 404) );
		}

		$calendars = $this->appQuery->findCalendarsForEmployee( $employee );
		$ret = $this->self->toArrayCalendar( $calendars );

		return $ret;
	}

/* returns employee id */
	public function employeesCreate( $values )
	{
		$errors = array();

		$userId = isset( $values['user_id'] ) ? $values['user_id'] : null;
		$title = isset( $values['title'] ) ? $values['title'] : null;

		if( $userId ){
			// check if there's an employee linked to that user
			$employee = $this->appQuery->findEmployeeByUserId( $userId );
			if( $employee ){
				return new WP_Error( 'user_occupied', 'The user is already linked to another employee', array('status' => 500) );
			}

			$wpUser = get_user_by( 'id', $userId );
			if( ! $wpUser ){
				return new WP_Error( 'no_user', 'User not found', array('status' => 404) );
			}
		}

		if( (! $userId) && (null === $title) ){
			$errors['title'] = 'Required Field';
		}
		$description = isset( $values['description'] ) ? $values['description'] : null;

		if( $errors ){
			$errorStr = array();
			foreach( $errors as $k => $v ) $errorStr[] = $k . ': ' . $v;
			$errorStr = join( ', ', $errorStr );
			return new WP_Error( 'error', $errorStr, array('status' => 500) );
		}

		try {
		// create by user id
			if( null === $title ){
				$employeeId = $this->self->employeesCreateByUserId( $userId );
			}
			else {
				$employeeId = $this->employeesCommand->create( $title, $description );
			}
		}
		catch( HC3_ExceptionArray $e ){
			$errors = $e->getErrors();
		}

		if( $errors ){
			$errorStr = array();
			foreach( $errors as $k => $v ) $errorStr[] = $k . ': ' . $v;
			$errorStr = join( ', ', $errorStr );
			return new WP_Error( 'error', $errorStr, array('status' => 500) );
		}

		if( ! $userId ){
			return $employeeId;
		}

	// link user to employee
		if( null !== $title ){
			$this->appCommand->linkEmployeeToUserById( $employeeId, $userId );
		}

		return $employeeId;
	}

/* returns employee id */
	public function employeesCreateByUserId( $userId )
	{
		$employee = $this->appQuery->findEmployeeByUserId( $userId );
		if( $employee ){
			$ret = $employee->getId();
			return $ret;
		}

		$wpUser = get_user_by( 'id', $userId );
		if( ! $wpUser ){
			return new WP_Error( 'no_user', 'User not found', array('status' => 404) );
		}

		$title = $wpUser->display_name;
		$description = null;

		try {
			$employeeId = $this->employeesCommand->create( $title, $description );
		}
		catch( HC3_ExceptionArray $e ){
			$errors = $e->getErrors();
		}

		if( $errors ){
			$errorStr = array();
			foreach( $errors as $k => $v ) $errorStr[] = $k . ': ' . $v;
			$errorStr = join( ', ', $errorStr );
			return new WP_Error( 'error', $errorStr, array('status' => 500) );
		}

	// link user to employee
		$this->appCommand->linkEmployeeToUserById( $employeeId, $userId );

		return $employeeId;
	}

/* returns employee id */
	public function employeesAddToCalendar( $employeeId, $calendarId )
	{
		$errors = array();

		$employee = $this->employeesQuery->findById( $employeeId );
		if( ! $employee ){
			return new WP_Error( 'no_employee', 'Not found', array('status' => 404) );
		}

		$calendar = $this->calendarsQuery->findById( $calendarId );
		if( ! $calendar ){
			return new WP_Error( 'no_calendar', 'Not found', array('status' => 404) );
		}

		try {
			$this->appCommand->addEmployeeToCalendar( $employee, $calendar );
		}
		catch( HC3_ExceptionArray $e ){
			$errors = $e->getErrors();
		}

		if( $errors ){
			$errorStr = array();
			foreach( $errors as $k => $v ) $errorStr[] = $k . ': ' . $v;
			$errorStr = join( ', ', $errorStr );
			return new WP_Error( 'error', $errorStr, array('status' => 500) );
		}

		return true;
	}

	public function employeesRemoveFromCalendar( $employeeId, $calendarId )
	{
		$errors = array();

		$employee = $this->employeesQuery->findById( $employeeId );
		if( ! $employee ){
			return new WP_Error( 'no_employee', 'Not found', array('status' => 404) );
		}

		$calendar = $this->calendarsQuery->findById( $calendarId );
		if( ! $calendar ){
			return new WP_Error( 'no_calendar', 'Not found', array('status' => 404) );
		}

		try {
			$this->appCommand->removeEmployeeFromCalendar( $employee, $calendar );
		}
		catch( HC3_ExceptionArray $e ){
			$errors = $e->getErrors();
		}

		if( $errors ){
			$errorStr = array();
			foreach( $errors as $k => $v ) $errorStr[] = $k . ': ' . $v;
			$errorStr = join( ', ', $errorStr );
			return new WP_Error( 'error', $errorStr, array('status' => 500) );
		}

		return true;
	}
}