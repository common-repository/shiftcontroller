<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
interface SH4_App_ICommand
{
	public function linkEmployeeToUser( SH4_Employees_Model $employee, HC3_Users_Model $user );
	public function unlinkEmployeeFromUser( SH4_Employees_Model $employee );

	public function addManagerToCalendar( HC3_Users_Model $user, SH4_Calendars_Model $calendar );
	public function removeManagerFromCalendar( HC3_Users_Model $user, SH4_Calendars_Model $calendar );

	public function addViewerToCalendar( HC3_Users_Model $user, SH4_Calendars_Model $calendar );
	public function removeViewerFromCalendar( HC3_Users_Model $user, SH4_Calendars_Model $calendar );

	public function addEmployeeToCalendar( SH4_Employees_Model $employee, SH4_Calendars_Model $calendar );
	public function removeEmployeeFromCalendar( SH4_Employees_Model $employee, SH4_Calendars_Model $calendar );

	public function addShiftTypeToCalendar( SH4_ShiftTypes_Model $shiftType, SH4_Calendars_Model $calendar );
	public function removeShiftTypeFromCalendar( SH4_ShiftTypes_Model $shiftType, SH4_Calendars_Model $calendar );

	public function uninstall();
}

class SH4_App_Command implements SH4_App_ICommand
{
	public $self, $crudFactory, $settings, $shiftTypesCommand, $calendarsCommand, $employeesCommand, $shiftsCommand;

	public function __construct(
		HC3_CrudFactory $crudFactory,
		HC3_Settings $settings,
		HC3_Hooks $hooks,

		SH4_ShiftTypes_Command $shiftTypesCommand,
		SH4_Calendars_Command $calendarsCommand,
		SH4_Employees_Command $employeesCommand,
		SH4_Shifts_Command $shiftsCommand
		)
	{
		$this->crudFactory = $hooks->wrap( $crudFactory );
		$this->settings = $hooks->wrap( $settings );
		$this->shiftTypesCommand = $hooks->wrap( $shiftTypesCommand );
		$this->calendarsCommand = $hooks->wrap( $calendarsCommand );
		$this->employeesCommand = $hooks->wrap( $employeesCommand );
		$this->shiftsCommand = $hooks->wrap( $shiftsCommand );
		$this->self = $hooks->wrap( $this );
	}

	public function uninstall()
	{
		// $this->shiftTypesCommand->deleteAll();
		// $this->calendarsCommand->deleteAll();
		// $this->employeesCommand->deleteAll();
		// $this->shiftsCommand->deleteAll();
		$this->settings->resetAll();
	}

	public function addManagerToCalendar( HC3_Users_Model $user, SH4_Calendars_Model $calendar )
	{
		$userId = $user->getId();
		$calendarId = $calendar->getId();
		$settingName = 'calendar_' . $calendarId . '_manager';

		$managersIds = $this->settings->get( $settingName, TRUE );

		$iis = array_keys( $managersIds );
		foreach( $iis as $ii ){
			if( ! is_numeric($managersIds[$ii]) ){
				unset( $managersIds[$ii] );
			}
		}

		if( ! in_array($userId, $managersIds) ){
			$managersIds[] = $userId;
		}

		$this->settings->set( $settingName, $managersIds );
	}

	public function removeManagerFromCalendar( HC3_Users_Model $user, SH4_Calendars_Model $calendar )
	{
		$userId = $user->getId();
		$calendarId = $calendar->getId();
		$settingName = 'calendar_' . $calendarId . '_manager';

		$managersIds = $this->settings->get( $settingName, TRUE );
		$managersIds = HC3_Functions::removeFromArray( $managersIds, $userId );

		$this->settings->set( $settingName, $managersIds );
	}

	public function addViewerToCalendar( HC3_Users_Model $user, SH4_Calendars_Model $calendar )
	{
		$userId = $user->getId();
		$calendarId = $calendar->getId();
		$settingName = 'calendar_' . $calendarId . '_viewer';

		$managersIds = $this->settings->get( $settingName, TRUE );

		$iis = array_keys( $managersIds );
		foreach( $iis as $ii ){
			if( ! is_numeric($managersIds[$ii]) ){
				unset( $managersIds[$ii] );
			}
		}

		if( ! in_array($userId, $managersIds) ){
			$managersIds[] = $userId;
		}

		$this->settings->set( $settingName, $managersIds );
	}

	public function removeViewerFromCalendar( HC3_Users_Model $user, SH4_Calendars_Model $calendar )
	{
		$userId = $user->getId();
		$calendarId = $calendar->getId();
		$settingName = 'calendar_' . $calendarId . '_viewer';

		$managersIds = $this->settings->get( $settingName, TRUE );
		$managersIds = HC3_Functions::removeFromArray( $managersIds, $userId );

		$this->settings->set( $settingName, $managersIds );
	}

	public function addEmployeeToCalendar( SH4_Employees_Model $employee, SH4_Calendars_Model $calendar )
	{
		$employeeId = $employee->getId();
		$calendarId = $calendar->getId();
		$settingName = 'calendar_' . $calendarId . '_employee';

		$employeesIds = $this->settings->get( $settingName, TRUE );

		$iis = array_keys( $employeesIds );
		foreach( $iis as $ii ){
			if( ! is_numeric($employeesIds[$ii]) ){
				unset( $employeesIds[$ii] );
			}
		}

		if( ! in_array($employeeId, $employeesIds) ){
			$employeesIds[] = $employeeId;
		}

		$this->settings->set( $settingName, $employeesIds );
	}

	public function addShiftTypeToCalendar( SH4_ShiftTypes_Model $shiftType, SH4_Calendars_Model $calendar )
	{
		$shiftTypeId = $shiftType->getId();
		$calendarId = $calendar->getId();
		$settingName = 'calendar_' . $calendarId . '_shifttype';

		$shiftTypesIds = $this->settings->get( $settingName, TRUE );

		$iis = array_keys( $shiftTypesIds );
		foreach( $iis as $ii ){
			if( ! is_numeric($shiftTypesIds[$ii]) ){
				unset( $shiftTypesIds[$ii] );
			}
		}

		if( ! in_array($shiftTypeId, $shiftTypesIds) ){
			$shiftTypesIds[] = $shiftTypeId;
		}

		$this->settings->set( $settingName, $shiftTypesIds );
	}

	public function removeShiftTypeFromCalendar( SH4_ShiftTypes_Model $shiftType, SH4_Calendars_Model $calendar )
	{
		$shiftTypeId = $shiftType->getId();
		$calendarId = $calendar->getId();
		$settingName = 'calendar_' . $calendarId . '_shifttype';

		$shiftTypesIds = $this->settings->get( $settingName, TRUE );
		$shiftTypesIds = HC3_Functions::removeFromArray( $shiftTypesIds, $shiftTypeId );

		$this->settings->set( $settingName, $shiftTypesIds );
	}

	public function removeEmployeeFromCalendar( SH4_Employees_Model $employee, SH4_Calendars_Model $calendar )
	{
		$employeeId = $employee->getId();
		$calendarId = $calendar->getId();
		$settingName = 'calendar_' . $calendarId . '_employee';

		$employeesIds = $this->settings->get( $settingName, TRUE );
		$employeesIds = HC3_Functions::removeFromArray( $employeesIds, $employeeId );

		$this->settings->set( $settingName, $employeesIds );
	}

	public function linkEmployeeToUserById( $employeeId, $userId )
	{
		if( ! $employeeId ){
			return;
		}

		if( ! $userId ){
			return;
		}

		$crud = $this->crudFactory->make('employee');

		$values = array( 'user_id' => $userId );
		$results = $crud->update( $employeeId, $values );
	}

	public function linkEmployeeToUser( SH4_Employees_Model $employee, HC3_Users_Model $user )
	{
		return $this->self->linkEmployeeToUserById( $employee->getId(), $user->getId() );
	}

	public function unlinkEmployeeFromUser( SH4_Employees_Model $employee )
	{
		$employeeId = $employee->getId();
		if( ! $employeeId ){
			return;
		}

		$crud = $this->crudFactory->make('employee');

		$values = array( 'user_id' => NULL );
		$results = $crud->update( $employeeId, $values );
	}
}