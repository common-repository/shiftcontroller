<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class SH4_ConflictsCalendar_Overlap implements SH4_Shifts_Conflict_IConflict
{
	private $_conflictingShifts = array();
	public $ui, $widget, $shiftsQuery;

	public function __construct(
		HC3_Hooks $hooks,
		HC3_Ui $ui,

		SH4_Shifts_Query $shiftsQuery,
		SH4_Shifts_View_Widget $widget
		)
	{
		$this->ui = $ui;
		$this->widget = $widget;

		$this->shiftsQuery = $hooks->wrap( $shiftsQuery );
	}

	// find overlapping shifts for the same calendar
	public function check( SH4_Shifts_Model $shift )
	{
		$return = TRUE;

		$this->_conflictingShifts = array();

		// $isOpen = $shift->isOpen();
		// if( $isOpen ){
		// 	return $return;
		// }

		$calendar = $shift->getCalendar();
		if( $calendar->isAvailability() ){
			return $return;
		}

		$calendar = $shift->getCalendar();
		if( ! $calendar ){
			return $return;
		}
		$calendarId = $calendar->getId();
		$id = $shift->getId();

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

			$testCalendar = $testShift->getCalendar();
			if( $testCalendar->isAvailability() ){
				continue;
			}

			$testCalendarId = $testCalendar->getId();
			if( $testCalendarId != $calendarId ){
				continue;
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
		$label = '__Calendar Overlap__';

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