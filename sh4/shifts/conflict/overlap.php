<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class SH4_Shifts_Conflict_Overlap implements SH4_Shifts_Conflict_IConflict
{
	private $_conflictingShifts = array();
	public $ui, $shiftsQuery, $widget, $settings;

	public function __construct(
		HC3_Hooks $hooks,
		HC3_Ui $ui,
		HC3_Settings $settings,
		SH4_Shifts_Query $shiftsQuery,
		SH4_Shifts_View_Widget $widget
		)
	{
		$this->ui = $hooks->wrap( $ui );
		$this->shiftsQuery = $hooks->wrap( $shiftsQuery );
		$this->widget = $hooks->wrap( $widget );
		$this->settings = $hooks->wrap($settings);
	}

	// find overlapping shifts for the same employee
	public function check( SH4_Shifts_Model $shift, array $skipIdList = array() )
	{
		$return = TRUE;

		$conflictSameCalendarOnly = $this->settings->get( 'conflicts_calendar_only' );

		$this->_conflictingShifts = array();

		$isOpen = $shift->isOpen();
		if( $isOpen ){
			return $return;
		}

		$calendar = $shift->getCalendar();
		if( $calendar->isAvailability() ){
			return $return;
		}

		$employee = $shift->getEmployee();
		if( ! $employee ){
			return $return;
		}
		$employeeId = $employee->getId();
		$id = $shift->getId();

		$myStart = $shift->getStart();
		$myEnd = $shift->getEnd();

		$this->shiftsQuery
			->setStart( $shift->getStart() )
			->setEnd( $shift->getEnd() )
			;

		$testShifts = $this->shiftsQuery->find();

		foreach( $testShifts as $testShift ){
			$testId = $testShift->getId();

			if( $testId == $id ){
				continue;
			}

			if( in_array($testId, $skipIdList) ){
				continue;
			}

			$testStart = $testShift->getStart();
			$testEnd = $testShift->getEnd();

		/* assuming shifts are sorted by start time */
			if( $testStart >= $myEnd ){
				break;
			}
			if( $testEnd <= $myStart ){
				continue;
			}

			$testCalendar = $testShift->getCalendar();
			if( $testCalendar->isAvailability() ){
				continue;
			}

			$testEmployee = $testShift->getEmployee();
			if( ! $testEmployee ){
				continue;
			}

			$testEmployeeId = $testEmployee->getId();
			if( $testEmployeeId != $employeeId ){
				continue;
			}

			if( $conflictSameCalendarOnly && (! $testCalendar->isTimeoff()) && ( ! $calendar->isTimeoff() ) ){
				if( $testCalendar->getId() != $calendar->getId() ){
					continue;
				}
			}

			$this->_conflictingShifts[] = $testShift;
		}

		if( $this->_conflictingShifts ){
			$return = FALSE;
		}

		return $return;
	}

	public function render()
	{
		$label = '__Overlap__';

		$iknow = array('conflicts');
		$hori = TRUE;

		$viewShifts = array();
		foreach( $this->_conflictingShifts as $shift ){
			$viewShifts[] = $this->widget->render( $shift, $iknow, $hori );
		}
		$out = $this->ui->makeList( $viewShifts );

		$out = $this->ui->makeLabelled( $label, $out );
		return $out;
	}
}