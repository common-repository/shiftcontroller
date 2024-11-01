<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
#[AllowDynamicProperties]
class SH4_Schedule_Html_View_Report
{
	public function __construct( 
		HC3_Hooks $hooks,
		HC3_Ui $ui,
		HC3_Request $request,
		HC3_Time $t,

		SH4_Shifts_Duration $shiftsDuration,
		SH4_Shifts_DurationService $shiftsDurationService,
		SH4_Calendars_Presenter $calendarsPresenter,
		SH4_Employees_Presenter $employeesPresenter,

		SH4_Schedule_Html_View_Common $common
		)
	{
		$this->self = $hooks->wrap($this);

		$this->ui = $ui;
		$this->t = $t;
		$this->request = $request;

		$this->calendarsPresenter = $hooks->wrap( $calendarsPresenter );
		$this->employeesPresenter = $hooks->wrap( $employeesPresenter );

		$this->common = $hooks->wrap( $common );
		$this->shiftsDuration = $hooks->wrap( $shiftsDuration );
		$this->shiftsDurationService = $hooks->wrap( $shiftsDurationService );
	}

	public function render()
	{
		$params = $this->request->getParams();
		$shifts = $this->common->getShifts();

		$header = array(
			'qty'		=> '__Number Of Shifts__',
			'duration'	=> '__Hours__',
			);

		$hideui = isset( $params['hideui'] ) ? $params['hideui'] : array();
		if( array_intersect(array('report-qty'), $hideui) ){
			unset( $header['qty'] );
		}
		if( array_intersect(array('report-duration'), $hideui) ){
			unset( $header['duration'] );
		}

		$rows = array();

		$this->shiftsDuration->reset();
		foreach( $shifts as $shift ){
			$this->shiftsDuration->add( $shift );
		}

		$row = array();
		$row['qty'] = $this->shiftsDuration->getQty();
		$row['duration'] = $this->shiftsDuration->formatDuration();

		$rows[] = $row;

		$out = $this->ui->makeTable( $header, $rows );

		return $out;
	}

	public function renderByCalendar()
	{
		$params = $this->request->getParams();
		$calendars = $this->common->getCalendars();
		$employees = $this->common->getEmployees();
		$shifts = $this->common->getShifts();

	// finalize calendars to include those that employee was removed from but still have shifts in
		$listCalendarId = array_keys( $calendars );
		$dictCalendarId = array_combine( $listCalendarId, $listCalendarId );
		foreach( $shifts as $shift ){
			$calendar = $shift->getCalendar();
			if( ! $calendar ) continue;
			$calendarId = $calendar->getId();
			$dictCalendarId[ $calendarId ] = $calendarId;
		}
		if( count($dictCalendarId) > count($calendars) ){
			$allCalendars = $this->common->findAllCalendars();
			$calendars = array_intersect_key( $allCalendars, $dictCalendarId );
		}

	// we can probably have archived calendars so if such calendars have no shifts then skip them
		$removeArchivedCalendars = array();
		foreach( $calendars as $calendar ){
			if( $calendar->isArchived() ){
				$removeArchivedCalendars[ $calendar->getId() ] = $calendar->getId();
			}
		}
		if( $removeArchivedCalendars ){
			foreach( $shifts as $shift ){
				$calendar = $shift->getCalendar();
				$id = $calendar ? $calendar->getId() : 0;
				if( isset($removeArchivedCalendars[$id]) ){
					unset( $removeArchivedCalendars[$id] );
				}
				if( ! $removeArchivedCalendars ){
					break;
				}
			}
			foreach( $removeArchivedCalendars as $id ){
				unset( $calendars[$id] );
			}
			reset( $shifts );
		}

		$header = array(
			'label'		=> NULL,
			'qty'		=> '__Number Of Shifts__',
			'duration'	=> '__Hours__',
			);

		$hideui = isset( $params['hideui'] ) ? $params['hideui'] : array();
		if( array_intersect(array('report-qty'), $hideui) ){
			unset( $header['qty'] );
		}
		if( array_intersect(array('report-duration'), $hideui) ){
			unset( $header['duration'] );
		}

		$viewBy = array();
		$rows = array();

		foreach( $calendars as $calendar ){
			$calendarId = $calendar->getId();
			$rows[ $calendarId ] = array('label' => '', 'qty' => 0, 'duration' => 0);

			$label = $this->calendarsPresenter->presentTitle( $calendar );
			// $label = $calendar->getTitle();
			$rows[ $calendarId ]['label'] = $label;

			$viewBy[ $calendarId ] = array();
		}

		foreach( $shifts as $shift ){
			$calendar = $shift->getCalendar();
			$calendarId = $calendar->getId();

			if( ! array_key_exists($calendarId, $viewBy) ){
				continue;
			}
			$viewBy[ $calendarId ][] = $shift;
		}

		reset( $viewBy );
		foreach( $viewBy as $calendarId => $thisShifts ){
			$this->shiftsDuration->reset();
			foreach( $thisShifts as $shift ){
				$this->shiftsDuration->add( $shift );
			}

			$rows[ $calendarId ]['qty'] = $this->shiftsDuration->getQty();
			$rows[ $calendarId ]['duration'] = $this->shiftsDuration->formatDuration();
		}

		$out = $this->ui->makeTable( $header, $rows );
		return $out;
	}

	public function renderByEmployee()
	{
		$params = $this->request->getParams();
		$calendars = $this->common->getCalendars();
		$employees = $this->common->getEmployees();
		$shifts = $this->common->getShifts();

	// finalize employees to include those that employee was removed from but still have shifts in
		$listEmployeeId = array_keys( $employees );
		$dictEmployeeId = array_combine( $listEmployeeId, $listEmployeeId );
		foreach( $shifts as $shift ){
			$employee = $shift->getEmployee();
			if( ! $employee ) continue;
			$employeeId = $employee->getId();
			$dictEmployeeId[ $employeeId ] = $employeeId;
		}
		if( count($dictEmployeeId) > count($employees) ){
			$allEmployees = $this->common->findAllEmployees();
			$employees = array_intersect_key( $allEmployees, $dictEmployeeId );
		}

	// we can probably have archived employees so if such employees have no shifts then skip them
		$removeArchivedEmployees = array();
		foreach( $employees as $employee ){
			if( $employee->isArchived() ){
				$removeArchivedEmployees[ $employee->getId() ] = $employee->getId();
			}
		}
		if( $removeArchivedEmployees ){
			foreach( $shifts as $shift ){
				$employee = $shift->getEmployee();
				$id = $employee ? $employee->getId() : 0;
				if( isset($removeArchivedEmployees[$id]) ){
					unset( $removeArchivedEmployees[$id] );
				}
				if( ! $removeArchivedEmployees ){
					break;
				}
			}
			foreach( $removeArchivedEmployees as $id ){
				unset( $employees[$id] );
			}
			reset( $shifts );
		}

	// we can probably have archived calendars so if such calendars have no shifts then skip them
		$removeArchivedCalendars = array();
		foreach( $calendars as $calendar ){
			if( $calendar->isArchived() ){
				$removeArchivedCalendars[ $calendar->getId() ] = $calendar->getId();
			}
		}
		if( $removeArchivedCalendars ){
			foreach( $shifts as $shift ){
				$calendar = $shift->getCalendar();
				$id = $calendar ? $calendar->getId() : 0;
				if( isset($removeArchivedCalendars[$id]) ){
					unset( $removeArchivedCalendars[$id] );
				}
				if( ! $removeArchivedCalendars ){
					break;
				}
			}
			foreach( $removeArchivedCalendars as $id ){
				unset( $calendars[$id] );
			}
			reset( $shifts );
		}

		$header = array(
			'label' => NULL,
			);

		foreach( $calendars as $calendar ){
			$calendarId = $calendar->getId();
			$calendarView = $this->calendarsPresenter->presentTitle( $calendar );
			$header['calendar_' . $calendarId] = $calendarView;
		}
		$header['total'] = '__Total__';

		$viewBy = array();
		$rows = array();

		$hideui = isset( $params['hideui'] ) ? $params['hideui'] : array();
		if( array_intersect(array('report-qty'), $hideui) ){
			unset( $header['qty'] );
		}
		if( array_intersect(array('report-duration'), $hideui) ){
			unset( $header['duration'] );
		}

		foreach( $employees as $employee ){
			$employeeId = $employee ? $employee->getId() : 0;
			$rows[ $employeeId ] = array('label' => '', 'qty' => 0, 'duration' => 0);

			// $label = $employee->getTitle();
			$label = $this->employeesPresenter->presentTitle( $employee );
			$label = $this->ui->makeSpan( $label )
				->tag('font-size', 4)
				;
			$rows[ $employeeId ]['label'] = $label;

			$viewBy[ $employeeId ] = array();
		}

		foreach( $shifts as $shift ){
			$employee = $shift->getEmployee();
			$employeeId = $employee ? $employee->getId() : 0;

			if( ! array_key_exists($employeeId, $viewBy) ){
				continue;
			}
			$viewBy[ $employeeId ][] = $shift;
		}

		reset( $viewBy );
		foreach( $viewBy as $employeeId => $thisShifts ){
			$durations = array();

			$durations[0] = $this->shiftsDurationService->newCounter();
			reset( $calendars );
			foreach( $calendars as $calendar ){
				$calendarId = $calendar->getId();
				$durations[ $calendarId ] = $this->shiftsDurationService->newCounter();
			}

			reset( $thisShifts );
			foreach( $thisShifts as $shift ){
				$calendar = $shift->getCalendar();
				$calendarId = $calendar->getId();
				if( ! array_key_exists($calendarId, $durations) ){
					continue;
				}
				$durations[$calendarId]->add( $shift );
				$durations[0]->add( $shift );
			}

			reset( $calendars );
			foreach( $calendars as $calendar ){
				$calendarId = $calendar->getId();
				if( array_intersect(array('report-duration'), $hideui) ){
					$rows[ $employeeId ]['calendar_' . $calendarId] = $durations[$calendarId]->getQty();
				}
				else {
					$rows[ $employeeId ]['calendar_' . $calendarId] = $durations[$calendarId]->formatDuration();
				}
			}

			if( array_intersect(array('report-duration'), $hideui) ){
				$rows[ $employeeId ]['total'] = $durations[0]->getQty();
			}
			else {
				$rows[ $employeeId ]['total'] = $durations[0]->formatDuration();
			}
		}

		$out = $this->ui->makeTable( $header, $rows );

		return $out;
	}
}