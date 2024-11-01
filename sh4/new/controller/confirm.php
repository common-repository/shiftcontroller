<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
#[AllowDynamicProperties]
class SH4_New_Controller_Confirm
{
	public function __construct(
		HC3_Hooks $hooks,
		HC3_Post $post,
		HC3_Notificator $notificator,

		SH4_New_Controller_Common $common,
		SH4_Shifts_Command $shiftsCommand,

		SH4_Calendars_Query $calendars,
		SH4_Employees_Query $employees,
		SH4_ShiftTypes_Query $shiftTypes,

		HC3_Time $t
		)
	{
		$this->t = $t;
		$this->post = $hooks->wrap( $post );
		$this->notificator = $notificator;

		$this->common = $hooks->wrap( $common );
		$this->shiftsCommand = $hooks->wrap($shiftsCommand);

		$this->calendars = $hooks->wrap($calendars);
		$this->employees = $hooks->wrap($employees);
		$this->shiftTypes = $hooks->wrap($shiftTypes);

		$this->self = $hooks->wrap($this);
	}

	public function executePublish( $calendarId, $shiftTypeId, $dateString, $employeeString )
	{
		return $this->_execute( $calendarId, $shiftTypeId, $dateString, $employeeString, 'publish' );
	}

	public function executeDraft( $calendarId, $shiftTypeId, $dateString, $employeeString )
	{
		return $this->_execute( $calendarId, $shiftTypeId, $dateString, $employeeString, 'draft' );
	}

	public function _execute( $calendarId, $shiftTypeId, $dateString, $employeeString, $status )
	{
set_time_limit( 600 );
		$this->common->beforeExecute();

		$shiftType = $this->shiftTypes->findById( $shiftTypeId );
		$calendar = $this->calendars->findById( $calendarId );

		$employeesIds = HC3_Functions::unglueArray( $employeeString );

	// if we have multiple open shifts
		$multiOpenShifts = 0;
		for( $ii = 0; $ii < count($employeesIds); $ii++ ){
			if( substr($employeesIds[$ii], 0, strlen('0' . SH4_NEW_OPEN_SEPARATOR)) == '0'.SH4_NEW_OPEN_SEPARATOR ){
				$multiOpenShifts = substr( $employeesIds[$ii], strlen('0'.SH4_NEW_OPEN_SEPARATOR) );
				$employeesIds[$ii] = substr( $employeesIds[$ii], 0, -strlen(SH4_NEW_OPEN_SEPARATOR . $multiOpenShifts) );
				break;
			}
		}

		$employees = $this->employees->findManyActiveById( $employeesIds );

		$dates = HC3_Functions::unglueArray( $dateString );

		$shifts = array();
		$ids = array();

		$range = $shiftType->getRange();
		switch( $range ){
			case SH4_ShiftTypes_Model::RANGE_DAYS:
				$start = $this->t->setDateDb( $dates[0] )
					->formatDateTimeDb()
					;
				$end = $this->t->setDateDb( $dates[1] )
					->modify('+1 day')
					->formatDateTimeDb()
					;
				foreach( $employees as $employee ){
					$toCreate = 1;
					$employeeId = $employee->getId();
					if( (! $employeeId) && $multiOpenShifts ){
						$toCreate = $multiOpenShifts;
					}

					for( $ii = 1; $ii <= $toCreate; $ii++ ){
						$shift = new SH4_Shifts_Model( NULL, $calendar, $start, $end, $employee );
						$shifts[] = $shift;
					}
				}
				break;

			case SH4_ShiftTypes_Model::RANGE_HOURS:
				$timeStart = $shiftType->getStart();
				$timeEnd = $shiftType->getEnd();

				$breakTimeStart = $shiftType->getBreakStart();
				$breakTimeEnd = $shiftType->getBreakEnd();

				reset( $dates );
				foreach( $dates as $date ){
					$start = $this->t->setDateDb( $date )
						->modify( '+' . $timeStart . ' seconds' )
						->formatDateTimeDb()
						;
					$end = $this->t->setDateDb( $date )
						->modify( '+' . $timeEnd . ' seconds' )
						->formatDateTimeDb()
						;

					$breakStart = $breakEnd = NULL;
					if( ! ((NULL === $breakTimeStart) && (NULL === $breakTimeEnd)) ){
						if( $breakTimeStart OR $breakTimeEnd ){
							$breakStart = $this->t->setDateDb( $date );
							if( $breakTimeStart ){
								$this->t->modify( '+' . $breakTimeStart . ' seconds' );
							}
							$breakStart = $this->t->formatDateTimeDb();

							$breakEnd = $this->t->setDateDb( $date );
							if( $breakTimeEnd ){
								$this->t->modify( '+' . $breakTimeEnd . ' seconds' );
							}
							$breakEnd = $this->t->formatDateTimeDb();
						}
					}

					foreach( $employees as $employee ){
						$toCreate = 1;
						$employeeId = $employee->getId();
						if( (! $employeeId) && $multiOpenShifts ){
							$toCreate = $multiOpenShifts;
						}

						for( $ii = 1; $ii <= $toCreate; $ii++ ){
							$shift = new SH4_Shifts_Model( NULL, $calendar, $start, $end, $employee, $breakStart, $breakEnd );
							$shifts[] = $shift;
						}
					}
				}
				break;
		}

		$count = count( $shifts );
		for( $ii = 0; $ii < $count; $ii++ ){
			$id = $this->shiftsCommand->createNew( $shifts[$ii] );
			$ids[] = $id;
		}

		$this->common->afterExecute( $ids );

		for( $ii = 0; $ii < $count; $ii++ ){
			switch( $status ){
				case 'publish':
					$id = $this->shiftsCommand->publish( $shifts[$ii] );
					break;
				case 'draft':
					$id = $this->shiftsCommand->draft( $shifts[$ii] );
					break;
			}
		}

		$scheduleLink = $this->post->get( 'back' );
		if( $scheduleLink ){
			$scheduleLink = json_decode( $scheduleLink, TRUE );
		}
		else {
			$scheduleLink = HC3_Session::instance()->getUserdata( 'scheduleLink' );
			if( ! $scheduleLink ){
				$scheduleLink = array( 'schedule', array() );
			}
			$scheduleLink = array( 'schedule', array() );
		}

		$returnToDate = $dates[0];
		$scheduleLink[1]['start'] = $returnToDate;

		$return = array( $scheduleLink, '__New Created__' . ' [' . count($ids) . ']' );
		return $return;
	}
}