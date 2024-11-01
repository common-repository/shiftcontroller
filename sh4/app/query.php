<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
interface SH4_App_IQuery
{
	public function findUserByEmployee( SH4_Employees_Model $employee );
	public function findEmployeeByUser( HC3_Users_Model $user );
	public function findAllUsersWithEmployee();

	public function findManagersForCalendar( SH4_Calendars_Model $calendar );
	public function findViewersForCalendar( SH4_Calendars_Model $calendar );

	public function findCalendarsManagedByUser( HC3_Users_Model $user );
	public function findCalendarsViewedByUser( HC3_Users_Model $user );

	public function filterEmployeesForCalendar( array $employeeList, SH4_Calendars_Model $calendar );
	public function findEmployeesForCalendar( SH4_Calendars_Model $calendar );
	public function findCalendarsForEmployee( SH4_Employees_Model $employee );

	public function findShiftTypesForCalendar( SH4_Calendars_Model $calendar );

	public function filterShiftsForUser( HC3_Users_Model $user, array $shifts );
	public function findCalendarsForChange( SH4_Shifts_Model $model );
}

class SH4_App_Query implements SH4_App_IQuery
{
	public $self, $settings, $permission, $shiftTypesQuery, $calendarsQuery, $employeesQuery, $cp, $t, $usersQuery, $crudFactory;

	public $repoEmployee = array();
	public $repoActiveEmployee = array();
	public $repoActiveCalendar = array();
	public $repoShiftType = array();
	public $repoUser = array();

	public $repoEmployeeUser = array();
	public $repoUserEmployee = array();

	public function __construct(
		HC3_Settings $settings,
		HC3_IPermission $permission,

		SH4_ShiftTypes_Query $shiftTypesQuery,
		SH4_Calendars_Query $calendarsQuery,
		SH4_Employees_Query $employeesQuery,
		SH4_Calendars_Permissions $cp,

		HC3_Time $t,
		HC3_Users_Query $usersQuery,
		HC3_CrudFactory $crudFactory,
		HC3_Hooks $hooks
		)
	{
		$this->settings = $hooks->wrap( $settings );
		$this->permission = $hooks->wrap( $permission );
		$this->crudFactory = $hooks->wrap( $crudFactory );
		$this->t = $t;

		$this->shiftTypesQuery = $hooks->wrap( $shiftTypesQuery );
		$this->employeesQuery = $hooks->wrap( $employeesQuery );
		$this->calendarsQuery = $hooks->wrap( $calendarsQuery );
		$this->usersQuery = $hooks->wrap( $usersQuery );
		$this->cp = $hooks->wrap( $cp );

		$this->self = $hooks->wrap( $this );

	// employee
		$this->repoActiveEmployee = $this->employeesQuery->findActive();
		$this->repoEmployee = $this->repoActiveEmployee;

	// calendar
		$this->repoActiveCalendar = $this->calendarsQuery->findActive();

	// shift type
		$this->repoShiftType = $this->shiftTypesQuery->findAll();

	// employee-user
		global $wpdb;
		$sql = 'SELECT post_id AS employee_id, meta_value AS user_id FROM ' . $wpdb->postmeta . ' wpostmeta'  . ' JOIN ' . $wpdb->posts . ' wposts ON wposts.ID = wpostmeta.post_id WHERE wpostmeta.meta_key="user_id" AND wposts.post_type="sh4_employee"';
		$res = $wpdb->get_results( $sql, ARRAY_A );
		foreach( $res as $e ){
			if( ! $e['employee_id'] ) continue;
			$this->repoEmployeeUser[ $e['employee_id'] ] = $e['user_id'];
			$this->repoUserEmployee[ $e['user_id'] ] = $e['employee_id'];
		}

		if( $this->repoUserEmployee ){
			$this->repoUser = $this->usersQuery->findManyById( array_keys($this->repoUserEmployee) );
		}
	}

	public function findCalendarsForChange( SH4_Shifts_Model $shift )
	{
		$employee = $shift->getEmployee();

	// find current shift type
		$startInDay = $shift->getStartInDay();
		$endInDay = $shift->getEndInDay();
		$shiftKey = $startInDay . '-' . $endInDay;

		$calendar = $shift->getCalendar();
		$calendarId = $calendar->getId();

		$needMultiDay = $shift->isMultiDay();

		$ret = $this->self->findCalendarsForEmployee( $employee );
		unset( $ret[$calendarId] );

		$ids = array_keys( $ret );
		foreach( $ids as $id ){
			if( $calendar->isTimeoff() && (! $ret[$id]->isTimeoff() ) ){
				unset( $ret[$id] );
				continue;
			}
			if( $calendar->isShift() && (! $ret[$id]->isShift() ) ){
				unset( $ret[$id] );
				continue;
			}

			$gotShiftTypes = FALSE;
			$thisShiftTypes = $this->self->findShiftTypesForCalendar( $ret[$id] );
			foreach( $thisShiftTypes as $shiftType ){
				$range = $shiftType->getRange();

			// multiday?
				if( $needMultiDay ){
					if( SH4_ShiftTypes_Model::RANGE_HOURS == $range ){
						continue;
					}

				// if this allowed days cover this duration
					$shiftEnd = $shift->getEnd();
					$this->t->setDateTimeDb( $shift->getStart() );
					$minEnd = $this->t->setDateTimeDb( $shift->getStart() )->modify( '+' . $shiftType->getStart() . ' days' )->formatDateTimeDb();
					if( $minEnd > $shiftEnd ){
						continue;
					}
					$maxEnd = $this->t->setDateTimeDb( $shift->getStart() )->modify( '+' . ($shiftType->getEnd() + 1) . ' days' )->formatDateTimeDb();
					if( $maxEnd < $shiftEnd ){
						continue;
					}

					$gotShiftTypes = TRUE;
					break;
				}
				else {
					if( SH4_ShiftTypes_Model::RANGE_DAYS == $range ){
						continue;
					}

					$thisStart = $shiftType->getStart();
					$thisEnd = $shiftType->getEnd();

				// custom time
					if( (NULL === $thisStart) && (NULL === $thisEnd) ){
						$gotShiftTypes = TRUE;
						break;
					}

					$thisKey = $thisStart . '-' . $thisEnd;
					if( $thisKey == $shiftKey ){
						$gotShiftTypes = TRUE;
						break;
					}
				}
			}

			if( ! $gotShiftTypes ){
				unset( $ret[$id] );
				continue;
			}
		}

		return $ret;
	}

	public function filterShiftsForUser( HC3_Users_Model $user, array $return )
	{
		$currentUserId = $user->getId();

		if( ! $currentUserId ){
			$return = array();
			return $return;
		}

		$calendarsAsManager = $this->self->findCalendarsManagedByUser( $user );
		$calendarsAsViewer = $this->self->findCalendarsViewedByUser( $user );

		$calendarsAsEmployee = array();
		$meEmployee = $this->self->findEmployeeByUser( $user );
		if( $meEmployee ){
			$meEmployeeId = $meEmployee->getId();
			$calendarsAsEmployee = $this->self->findCalendarsForEmployee( $meEmployee );
		}

		$ids = array_keys( $return );

		foreach( $ids as $id ){
			$shift = $return[$id];

			$shiftCalendar = $shift->getCalendar();
			$shiftCalendarId = $shiftCalendar->getId();
			$shiftEmployee = $shift->getEmployee();
			$shiftEmployeeId = $shiftEmployee->getId();

			if( isset($calendarsAsManager[$shiftCalendarId]) ){
				continue;
			}

			if( isset($calendarsAsViewer[$shiftCalendarId]) ){
				continue;
			}

			if( isset($calendarsAsEmployee[$shiftCalendarId]) ){
				if( $shiftEmployeeId == $meEmployeeId ){
					if( $shift->isPublished() ){
						$perm = 'employee_view_own_publish';
					}
					else {
						$perm = 'employee_view_own_draft';
					}
				}
				else {
					if( $shift->isOpen() ){
						if( $shift->isPublished() ){
							$perm = 'employee_view_open_publish';
						}
						else {
							$perm = 'employee_view_open_draft';
						}
					}
					else {
						if( $shift->isPublished() ){
							$perm = 'employee_view_others_publish';
						}
						else {
							$perm = 'employee_view_others_draft';
						}
					}
				}

				if( $this->cp->get($shiftCalendar, $perm) ){
					continue;
				}
			}

			if( $shift->isOpen() ){
				if( $shift->isPublished() ){
					$perm = 'visitor_view_open_publish';
				}
				else {
					$perm = 'visitor_view_open_draft';
				}
			}
			else {
				if( $shift->isPublished() ){
					$perm = 'visitor_view_others_publish';
				}
				else {
					$perm = 'visitor_view_others_draft';
				}
			}

			if( $this->cp->get($shiftCalendar, $perm) ){
				continue;
			}

			unset( $return[$id] );
		}

		return $return;
	}

	public function filterCalendarsManagedByUser( array $ret, HC3_Users_Model $user )
	{
		$isAdmin = $this->permission->isAdmin( $user );
		if( $isAdmin ){
			return $ret;
		}

		$userId = $user->getId();
		foreach( $ret as $calendar ){
			$calendarId = $calendar->getId();
			$managers = $this->self->findManagersForCalendar( $calendar );
			if( ! isset($managers[$userId])){
				unset($ret[$calendarId]);
			}
		}

		return $ret;
	}

	public function findCalendarsManagedByUser( HC3_Users_Model $user )
	{
		// $ret = $this->calendarsQuery->findActive();
		// $ret = $this->self->filterCalendarsManagedByUser( $ret, $user );
		$ret = $this->self->filterCalendarsManagedByUser( $this->repoActiveCalendar, $user );
		return $ret;
	}

	public function filterCalendarsViewedByUser( array $ret, HC3_Users_Model $user )
	{
		$isAdmin = $this->permission->isAdmin( $user );
		if( $isAdmin ){
			return $ret;
		}

		$userId = $user->getId();
		foreach( $ret as $calendar ){
			$calendarId = $calendar->getId();
			$viewers = $this->self->findViewersForCalendar( $calendar );
			if( ! isset($viewers[$userId])){
				unset($ret[$calendarId]);
			}
		}

		return $ret;
	}

	public function findCalendarsViewedByUser( HC3_Users_Model $user )
	{
		// $ret = $this->calendarsQuery->findActive();
		// $ret = $this->self->filterCalendarsViewedByUser( $ret, $user );
		$ret = $this->self->filterCalendarsViewedByUser( $this->repoActiveCalendar, $user );
		return $ret;
	}

	public function findManagersForCalendar( SH4_Calendars_Model $calendar )
	{
		$return = $this->permission->findAdmins();

		$calendarId = $calendar->getId();
		$settingName = 'calendar_' . $calendarId . '_manager';

		$usersIds = $this->settings->get( $settingName, TRUE );
		if( $usersIds ){
			$moreReturn = $this->usersQuery->findManyById( $usersIds );
			foreach( $moreReturn as $id => $user ){
				if( ! array_key_exists($id, $return) ){
					$return[ $id ] = $user;
				}
			}
		}

		return $return;
	}

	public function findViewersForCalendar( SH4_Calendars_Model $calendar )
	{
		// $return = $this->permission->findAdmins();
		$return = array();

		$calendarId = $calendar->getId();
		$settingName = 'calendar_' . $calendarId . '_viewer';

		$usersIds = $this->settings->get( $settingName, TRUE );
		if( $usersIds ){
			$moreReturn = $this->usersQuery->findManyById( $usersIds );
			foreach( $moreReturn as $id => $user ){
				if( ! array_key_exists($id, $return) ){
					$return[ $id ] = $user;
				}
			}
		}

		return $return;
	}

	public function filterEmployeesForCalendar( array $employeeList, SH4_Calendars_Model $calendar )
	{
		$ret = array();

		$calendarId = $calendar->getId();
		$settingName = 'calendar_' . $calendarId . '_employee';
		$employeeIds = $this->settings->get( $settingName, true );

		if( ! $employeeIds ){
			return $ret;
		}

		$ret = $employeeList;

		$ids = array_keys( $ret );
		foreach( $ids as $id ){
			if( ! in_array($id, $employeeIds) ){
				unset( $ret[$id] );
			}
		}

		return $ret;
	}

	public function findEmployeesForCalendar( SH4_Calendars_Model $calendar )
	{
		$ret = array();

		$calendarId = $calendar->getId();
		$settingName = 'calendar_' . $calendarId . '_employee';

		$listEmployeeId = $this->settings->get( $settingName, true );
		if( $listEmployeeId ){
			$ret = array_intersect_key( $this->repoActiveEmployee, array_combine($listEmployeeId, $listEmployeeId) );
			// $ret = $this->employeesQuery->findManyActiveById( $listEmployeeId );
		}

		return $ret;
	}

	public function findShiftTypesForCalendar( SH4_Calendars_Model $calendar )
	{
		$ret = array();

		$calendarId = $calendar->getId();
		$settingName = 'calendar_' . $calendarId . '_shifttype';

		$listShiftTypeId = $this->settings->get( $settingName, true );
		if( $listShiftTypeId ){
			// $ret = $this->shiftTypesQuery->findManyById( $listShiftTypeId );
			$ret = array_intersect_key( $this->repoShiftType, array_combine($listShiftTypeId, $listShiftTypeId) );
		}

		return $ret;
	}

	public function filterCalendarsForEmployee( array $ret, SH4_Employees_Model $employee )
	{
		$employeeId = $employee->getId();

		$calendarIds = array_keys($ret);
		foreach( $calendarIds as $calendarId ){
			$settingName = 'calendar_' . $calendarId . '_employee';
			$employeesIds = $this->settings->get( $settingName, TRUE );
			if( ! in_array($employeeId, $employeesIds) ){
				unset( $ret[$calendarId] );
			}
		}

		return $ret;
	}

	public function findCalendarsForEmployee( SH4_Employees_Model $employee )
	{
		// $ret = $this->calendarsQuery->findActive();
		// $ret = $this->self->filterCalendarsForEmployee( $ret, $employee );
		$ret = $this->self->filterCalendarsForEmployee( $this->repoActiveCalendar, $employee );
		return $ret;
	}

	public function findAllUsersWithEmployee()
	{
		$ret = array();

		$usersIds = array_keys( $this->repoUserEmployee );

		// $crud = $this->crudFactory->make('employee');

		// $args = array();
		// $results = $crud->read( $args );
		// if( ! $results ){
			// return $ret;
		// }

		// $usersIds = array();
		// foreach( $results as $r ){
			// $userId = array_key_exists('user_id', $r) ? $r['user_id'] : NULL;
			// if( ! $userId ){
				// continue;
			// }
			// $usersIds[ $userId ] = $userId;
		// }

		if( ! $usersIds ){
			return $ret;
		}

		$ret = $this->usersQuery->findManyById( $usersIds );
		return $ret;
	}

	public function findUserByEmployee( SH4_Employees_Model $employee )
	{
		$ret = null;

		$employeeId = $employee->getId();
		if( ! $employeeId ){
			return $ret;
		}

		$userId = isset( $this->repoEmployeeUser[$employeeId] ) ? $this->repoEmployeeUser[$employeeId] : null;

		// $crud = $this->crudFactory->make('employee');
		// $args = array();
		// $args[] = array('id', '=', $employeeId );
		// $results = $crud->read( $args );

		// if( ! $results ){
			// return $ret;
		// }

		// $results = array_shift( $results );
		// $userId = array_key_exists('user_id', $results) ? $results['user_id'] : null;

		if( ! $userId ){
			return $ret;
		}

		// $ret = $this->usersQuery->findById( $userId );

		if( ! isset($this->repoUser[$userId]) ){
			$this->repoUser[$userId] = $this->usersQuery->findById( $userId );
		}
		$ret =  $this->repoUser[$userId];

		return $ret;
	}

	public function findEmployeeByUserId( $userId )
	{
		$ret = null;
		if( ! $userId ){
			return $ret;
		}

		$employeeId = isset( $this->repoUserEmployee[$userId] ) ? $this->repoUserEmployee[$userId] : null;
		if( ! $employeeId ){
			return $ret;
		}

		if( ! isset($this->repoEmployee[$employeeId]) ){
			$this->repoEmployee[$employeeId] = $this->employeesQuery->findById( $employeeId );
		}
		$ret = $this->repoEmployee[$employeeId];

		return $ret;
	}

	public function findEmployeeByUser( HC3_Users_Model $user )
	{
		return $this->self->findEmployeeByUserId( $user->getId() );
	}
}