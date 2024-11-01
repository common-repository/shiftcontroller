<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
#[AllowDynamicProperties]
class SH4_Reminders_Query
{
	public function __construct(
		HC3_Settings $settings,
		SH4_Reminders_QueryLog	$remindersQueryLog,

		SH4_Employees_Query $employeesQuery,
		SH4_App_Query $appQuery,
		SH4_Shifts_Query $shiftsQuery,

		HC3_Time $t,
		HC3_Hooks $hooks
		)
	{
		$this->t = $t;
		$this->settings = $hooks->wrap( $settings );

		$this->remindersQueryLog = $hooks->wrap( $remindersQueryLog );
		$this->employeesQuery = $hooks->wrap( $employeesQuery );
		$this->appQuery = $hooks->wrap( $appQuery );
		$this->shiftsQuery = $hooks->wrap( $shiftsQuery );
	}

	public function find( $range, $date )
	{
		$reminderInclude = $this->settings->get( 'reminders_include' );

// echo "RANGE = '$range'<br>";
		switch( $range ){
			case SH4_Reminders_Model::RANGE_DAILY:
			case SH4_Reminders_Model::RANGE_DAILY_SMS:
				$start = $this->t->setDateDb( $date )->formatDateTimeDb();
				$end = $this->t->setDateDb( $date )->modify('+1 day')->formatDateTimeDb();
				break;

			case SH4_Reminders_Model::RANGE_WEEKLY:
				$start = $this->t->setDateDb( $date )->setStartWeek()->formatDateTimeDb();
				$end = $this->t->setDateDb( $date )->setStartWeek()->modify('+1 week')->formatDateTimeDb();
				break;

			case SH4_Reminders_Model::RANGE_MONTHLY:
				$start = $this->t->setDateDb( $date )->setStartMonth()->formatDateTimeDb();
				$end = $this->t->setDateDb( $date )->setStartMonth()->modify('+1 month')->formatDateTimeDb();
				break;
		}

	// move the date to the start of the range
		$date = $this->t->setDateTimeDb( $start )->formatDateDb();

		$this->shiftsQuery
			->setStart( $start )
			->setEnd( $end )
			;
		$allShifts = $this->shiftsQuery->find();

		$shifts = array();

	// filter
		foreach( $allShifts as $shift ){
			if( ! $shift->isPublished() ) continue;
			if( $shift->getStart() >= $end ) continue;
			if( $shift->getStart() < $start ) continue;

			$shiftCalendar = $shift->getCalendar();

			if( 'all' == $reminderInclude ){
			}
			if( 'shift' == $reminderInclude ){
				if( $shiftCalendar->isTimeoff() ){
					continue;
				}
			}
			if( 'timeoff' == $reminderInclude ){
				if( $shiftCalendar->isShift() ){
					continue;
				}
			}

			$shifts[] = $shift;
		}

	// check logs if already sent
		$args = array();
		$args[] = array( 'range', '=', $range );
		$args[] = array( 'date', '=', $date );
		$reminderLogs = $this->remindersQueryLog->read( $args );

		$sentForEmployees = array();
		foreach( $reminderLogs as $log ){
			$sentForEmployees[ $log->employee_id ] = $log;
		}

	// find employees
		$employees = array();
		$shiftsByEmployee = array();

		foreach( $shifts as $shift ){
			$employee = $shift->getEmployee();
			if( (! $employee) OR (! $employee->getId() ) ) continue;

			$employeeId = $employee->getId();
			// if( isset($sentForEmployees[$employeeId]) ) continue;

			$employees[ $employeeId ] = $employee;

			if( ! isset($shiftsByEmployee[$employeeId]) ) $shiftsByEmployee[$employeeId] = array();
			$shiftsByEmployee[$employeeId][] = $shift;
		}

		$employeeIds = array_keys( $employees );
		$employees = $this->employeesQuery->findManyById( $employeeIds );

		$ret = array();
		foreach( $employees as $employee ){
			$user = $this->appQuery->findUserByEmployee( $employee );

		// sms can be sent without user
			if( in_array($range, array(SH4_Reminders_Model::RANGE_DAILY_SMS) ) ){
				
			}
			else {
				if( ! $user ) continue;
			}

			$employeeId = $employee->getId();

			$model = new SH4_Reminders_Model;

			$model->date = $date;
			$model->range = $range;
			$model->user = $user;
			$model->employee = $employee;
			$model->shifts = $shiftsByEmployee[ $employeeId ];
			$model->log = isset( $sentForEmployees[$employeeId] ) ? $sentForEmployees[$employeeId] : NULL;

			$ret[] = $model;
		}

		return $ret;
	}
}