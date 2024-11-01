<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
interface SH4_Shifts_IAvailability
{
	public function get( SH4_Shifts_Model $shift );
	public function hasAvailability();
}

class SH4_Shifts_Availability implements SH4_Shifts_IAvailability
{
	protected $checks = array();
	protected $availabilityCalendars = array();
	public $self, $shiftsQuery, $calendarsQuery;

	public function __construct(
		HC3_Hooks $hooks,
		SH4_Calendars_Query $calendarsQuery,
		SH4_Shifts_Query $shiftsQuery
		)
	{
		$this->shiftsQuery = $hooks->wrap( $shiftsQuery );
		$this->calendarsQuery = $hooks->wrap( $calendarsQuery );

		$this->availabilityCalendars = array();
		$activeCalendars = $this->calendarsQuery->findActive();
		foreach( $activeCalendars as $c ){
			if( $c->isAvailability() ){
				$this->availabilityCalendars[] = $c;
			}
		}
	}

	public function hasAvailability()
	{
		$return = count( $this->availabilityCalendars ) ? TRUE : FALSE;
		return $return;
	}

	public function get( SH4_Shifts_Model $shift )
	{
		$return = array();

		if( ! $this->availabilityCalendars ){
			return $return;
		}

		$start = $shift->getStart();
		$end = $shift->getEnd();
		$employee = $shift->getEmployee();

		$this->shiftsQuery
			->setStart( $start )
			->setEnd( $end )
			;

		$shifts = $this->shiftsQuery->find();

		foreach( $shifts as $checkShift ){
			$checkCalendar = $checkShift->getCalendar();
			if( ! $checkCalendar->isAvailability() ){
				continue;
			}

			$checkEmployee = $checkShift->getEmployee();

			if( $employee->getId() != $checkEmployee->getId() ){
				continue;
			}

			$checkStart = $checkShift->getStart();
			$checkEnd = $checkShift->getEnd();

			if( $checkStart > $start ){
				continue;
			}

			if( $checkEnd < $end ){
				continue;
			}

			$return[] = $checkShift;
		}

		return $return;
	}
}