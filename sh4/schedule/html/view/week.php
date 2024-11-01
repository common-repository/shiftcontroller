<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
#[AllowDynamicProperties]
class SH4_Schedule_Html_View_Week
{
	protected $borderColor = 'gray';
	protected $allCombosShift = array();
	protected $allCombosTimeoff = array();

	public function __construct(
		HC3_Hooks $hooks,
		HC3_Ui $ui,
		HC3_Request $request,
		HC3_Time $t,
		HC3_Settings $settings,

		HC3_Uri $uri,

		SH4_New_Acl $newAcl,

		SH4_Shifts_Duration $shiftsDuration,
		SH4_Shifts_View_Widget $widget,
		SH4_Calendars_Presenter $calendarsPresenter,
		SH4_Employees_Presenter $employeesPresenter,
		SH4_Schedule_Html_View_Common $common,
		SH4_Shifts_View_Common $shiftsCommon
		)
	{
		$this->self = $hooks->wrap($this);

		$this->ui = $ui;

		$this->uri = $uri;
		$this->t = $t;
		$this->request = $request;

		$this->settings = $hooks->wrap( $settings );
		$this->newAcl = $hooks->wrap( $newAcl );

		$this->shiftsDuration = $hooks->wrap( $shiftsDuration );
		$this->calendarsPresenter = $hooks->wrap( $calendarsPresenter );
		$this->employeesPresenter = $hooks->wrap( $employeesPresenter );

		$this->widget = $hooks->wrap( $widget );
		$this->common = $hooks->wrap( $common );
		$this->shiftsCommon = $hooks->wrap( $shiftsCommon );

		$this->allCombosShift = $this->newAcl->findAllCombosShift();
		$this->allCombosTimeoff = $this->newAcl->findAllCombosTimeoff();
	}

	public function renderDateLabel( $date )
	{
		$slug = $this->request->getSlug();
		$today = $this->t->setNow()->formatDateDb();

		$this->t->setDateDb( $date );

		$weekdayView = $this->t->getWeekdayName();
		$weekdayView = $this->ui->makeBlock( $weekdayView )->tag('muted')->tag('font-size', 1);

		$ret = $this->t->formatDate();

		$ret = $this->ui->makeList( array($weekdayView, $ret) )
			->gutter(0)
			;

		$toDateParams = array();
		$toDateParams['type'] = 'day';
		$toDateParams['start'] = $date;
		$toDate = array( $slug, $toDateParams );

		$ret = $this->ui->makeAhref( $toDate, $ret )
			->tag('print')
			;

		if( $date == $today ){
			$ret = $this->ui->makeBlock( $ret )
				->tag('border')
				->tag('border-color', 'gray')
				;
		}

		return $ret;
	}

	public function render()
	{
		$slug = $this->request->getSlug();
		$today = $this->t->setNow()->formatDateDb();

		$shifts = $this->common->getShifts();

		$params = $this->request->getParams();
		$startDate = $params['start'];

		$disabledWeekdays = $this->settings->get( 'skip_weekdays', TRUE );

		$startDate = $this->t->setDateDb( $startDate )->setStartWeek()->formatDateDb();
		$endDate = $this->t->modify('+1 week')->modify('-1 day')->formatDateDb();

		$this->t->setDateDb( $startDate );
		$dates = array();
		$rexDate = $startDate;

		while( $rexDate <= $endDate ){
			if( $disabledWeekdays ){
				$wkd = $this->t->getWeekday();
				if( in_array($wkd, $disabledWeekdays) ){
					$rexDate = $this->t->modify('+1 day')->formatDateDb();
					continue;
				}
			}

			$dates[] = $rexDate;
			$rexDate = $this->t->modify('+1 day')->formatDateDb();
		}

		$viewShifts = array();
		$iknow = array('date');

		$allCalendars = $this->common->findAllCalendars();
		if( count($allCalendars) <= 1 ) $iknow[] = 'calendar';

		$this->shiftsDuration->reset();
		foreach( $shifts as $shift ){
			$this->shiftsDuration->add( $shift );
			$start = $shift->getStart();
			$end = $shift->getEnd();

			$startDayTime = substr($start, 8, 4);
			$endDayTime = substr($end, 8, 4);
			$shiftAllDay = ( (0 == $startDayTime) && (0 == $endDayTime) ) ? TRUE : FALSE;

			$this->t->setDateTimeDb( $end )->modify('-1 second');
			$shiftEndDate = $this->t->formatDateDb();

			$this->t->setDateTimeDb( $start );
			$shiftStartDate = $this->t->formatDateDb();

			$rexDate = $shiftStartDate;
			while( $rexDate <= $shiftEndDate ){
				if( in_array($rexDate, $dates) ){
					if( ! array_key_exists($rexDate, $viewShifts) ){
						$viewShifts[$rexDate] = array();
					}
					$viewShifts[$rexDate][] = $shift;
				}

				if( ! $shiftAllDay ){
					break;
				}
				$this->t->modify( '+1 day' );
				$rexDate = $this->t->formatDateDb();
			}
		}

		$rows = array();

		$row = array();
		foreach( $dates as $date ){
			if( ! $date ){
				$row[] = NULL;
				continue;
			}

			$cell = array();

			$dateView = $this->self->renderDateLabel( $date );

		// SHIFTS DURATION
			$counted = 0;
			$this->shiftsDuration->reset();

			if( isset($viewShifts[$date]) ){
				$dateStart = $this->t->setDateDb( $date )
					->formatDateTimeDb()
					;
				$dateEnd = $this->t->modify('+1 day')
					->formatDateTimeDb()
					;
				foreach( $viewShifts[$date] as $shift ){
					if( $shift->isMultiDay() ){
						$durationShift = clone $shift;
						if( $dateStart > $durationShift->getStart() ){
							$durationShift->setStart( $dateStart );
						}
						if( $dateEnd < $durationShift->getEnd() ){
							$durationShift->setEnd( $dateEnd );
						}
						$this->shiftsDuration->add( $durationShift );
					}
					else {
						$this->shiftsDuration->add( $shift );
					}
					$counted++;
				}
			}

			if( $counted ){
				$outReport = $this->common->renderReport( $this->shiftsDuration, FALSE );
				$outReport = $this->ui->makeSpan( $outReport )
					->tag('font-size', 2)
					;
				$dateView = $this->ui->makeList( array($dateView, $outReport) )
					->gutter(1)
					;
			}

			$dateView = $this->ui->makeBlock( $dateView )
				->tag('align', 'center')
				;

			$cell[] = $dateView;

			if( isset($viewShifts[$date]) ){
				$shiftsView = $this->self->renderDay( $viewShifts[$date], $iknow );
				$cell[] = $shiftsView;
			}

			$to = 'new';
			$toParams = array(
				'date'	=> $date
				);
			if( array_key_exists('employee', $params) && (count($params['employee']) == 1) ){
				if( ! in_array(-1, $params['employee']) ){
					$toParams['employee'] = $params['employee'][0];
				}
			}

			$links = array();

			if( $this->allCombosShift ){
				$label = '+' . ' ' . '__Shift__';
				$thisTo = 'new/shift';
				$thisTo = array( $thisTo, $toParams );
				$link = $this->ui->makeAhref( $thisTo, $label )
					->tag('tab-link')
					->tag('align', 'center')
					// ->addAttr('title', '__Add New__')
					;
				if( $today > $date ){
					$link->tag('muted', 3);
				}
				$links[] = $link;
			}

			if( $this->allCombosTimeoff ){
				$label = '+' . ' ' . '__Time Off__';
				$thisTo = 'new/timeoff';
				$thisTo = array( $thisTo, $toParams );
				$link = $this->ui->makeAhref( $thisTo, $label )
					->tag('tab-link')
					->tag('align', 'center')
					// ->addAttr('title', '__Add New__')
					;
				if( $today > $date ){
					$link->tag('muted', 3);
				}
				$links[] = $link;
			}

			$links = $this->ui->makeList( $links )->gutter(0);

			$cell[] = $links;

			$cell = $this->ui->makeList( $cell )
				->gutter(1)
				->tag('margin', 1)
				;

			$row[] = $cell;
		}

		$rows[] = $row;

		$out = $this->ui->makeTable( NULL, $rows, FALSE )
			->gutter(0)
			->setBordered( $this->borderColor )
			// ->setSegments( $weekSegments )
			;

		return $out;
	}

	public function renderByCalendar()
	{
		$slug = $this->request->getSlug();
		$today = $this->t->setNow()->formatDateDb();

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

		$disabledWeekdays = $this->settings->get( 'skip_weekdays', TRUE );

		$params = $this->request->getParams();
		$startDate = $params['start'];

		$this->t->setDateDb( $startDate )->setStartWeek();
		$dates = array();

		for( $ii = 0; $ii <= 6; $ii++ ){
			if( $disabledWeekdays ){
				$wkd = $this->t->getWeekday();
				if( in_array($wkd, $disabledWeekdays) ){
					$this->t->modify('+1 day');
					continue;
				}
			}
			$dates[] = $this->t->formatDateDb();
			$this->t->modify('+1 day');
		}

		$view_calendars = array();
		foreach( $calendars as $calendar ){
			$calendarId = $calendar->getId();

			$label = $this->calendarsPresenter->presentTitle( $calendar );
			$title = htmlspecialchars( $calendar->getTitle() );
			$description = $calendar->getDescription();
			if( strlen($description) ){
				$description = htmlspecialchars( $description );
				$title .= "\n" . $description;
			}

			$label = $this->ui->makeSpan( $label )
				->addAttr( 'title', $title )
				;

			$view_calendars[ $calendarId ] = $label;
		}

		$view_shifts = array();

		$iknow = array('calendar', 'date');
		$hori = FALSE;

		$allEmployees = $this->common->findAllEmployees();
		if( count($allEmployees) < 2 ){
			$iknow[] = 'employee';
		}

		foreach( $shifts as $shift ){
			$calendar = $shift->getCalendar();
			$id = $calendar->getId();

			if( ! array_key_exists($id, $view_shifts) ){
				$view_shifts[$id] = array();
			}

			$start = $shift->getStart();
			$end = $shift->getEnd();

			$startDayTime = substr($start, 8, 4);
			$endDayTime = substr($end, 8, 4);
			$shiftAllDay = ( (0 == $startDayTime) && (0 == $endDayTime) ) ? TRUE : FALSE;

			$this->t->setDateTimeDb( $end )->modify('-1 second');
			$shiftEndDate = $this->t->formatDateDb();

			$this->t->setDateTimeDb( $start );
			$shiftStartDate = $this->t->formatDateDb();

			$rexDate = $shiftStartDate;
			while( $rexDate <= $shiftEndDate ){
				if( in_array($rexDate, $dates) ){
					if( ! array_key_exists($rexDate, $view_shifts[$id]) ){
						$view_shifts[$id][$rexDate] = array();
					}
					$view_shifts[$id][$rexDate][] = $shift;
				}

				if( ! $shiftAllDay ){
					break;
				}
				$this->t->modify( '+1 day' );
				$rexDate = $this->t->formatDateDb();
			}
		}

		$header = array();
		$header[] = null;

		foreach( $dates as $date ){
			$this->t->setDateDb( $date );
			$dateView = $this->self->renderDateLabel( $date );

		// SHIFTS DURATION
			$counted = 0;
			$this->shiftsDuration->reset();

			reset( $view_shifts );
			foreach( $view_shifts as $id => $shifts2 ){
				if( isset($shifts2[$date]) ){
					$dateStart = $this->t->setDateDb( $date )
						->formatDateTimeDb()
						;
					$dateEnd = $this->t->modify('+1 day')
						->formatDateTimeDb()
						;
					foreach( $shifts2[$date] as $shift ){
						if( $shift->isMultiDay() ){
							$durationShift = clone $shift;
							if( $dateStart > $durationShift->getStart() ){
								$durationShift->setStart( $dateStart );
							}
							if( $dateEnd < $durationShift->getEnd() ){
								$durationShift->setEnd( $dateEnd );
							}
							$this->shiftsDuration->add( $durationShift );
						}
						else {
							$this->shiftsDuration->add( $shift );
						}
						$counted++;
					}
				}
			}
			if( $counted ){
				$outReport = $this->common->renderReport( $this->shiftsDuration, FALSE );
				$outReport = $this->ui->makeSpan( $outReport )
					->tag('font-size', 2)
					;
				$dateView = $this->ui->makeList( array($dateView, $outReport) )
					->gutter(1)
					;
			}

			$dateView = $this->ui->makeBlock( $dateView )
				->tag('align', 'center')
				;

			$header[] = $dateView;
		}

		$rows = array();

		foreach( $view_calendars as $id => $calendarView ){
			$row = array();

			$thisCalendar = $calendars[$id];
			$addOk = FALSE;
			if( $thisCalendar->isTimeoff() ){
				$label = '+ ' . '__Time Off__';
				if( isset($toParams['employee']) ){
					$testComboId = $id . '-' . $toParams['employee'];
					if( isset($this->allCombosTimeoff[$testComboId]) ){
						$addOk = TRUE;
					}
				}
				else {
					$testComboId = $id . '-';
					reset( $this->allCombosTimeoff );
					foreach( $this->allCombosTimeoff as $comboId ){
						if( $testComboId == substr($comboId, 0, strlen($testComboId)) ){
							$addOk = TRUE;
							break;
						}
					}
				}
			}
			else {
				$label = '+ ' . '__Shift__';
				if( isset($toParams['employee']) ){
					$testComboId = $id . '-' . $toParams['employee'];
					if( isset($this->allCombosShift[$testComboId]) ){
						$addOk = TRUE;
					}
				}
				else {
					$testComboId = $id . '-';
					reset( $this->allCombosShift );
					foreach( $this->allCombosShift as $comboId ){
						if( $testComboId == substr($comboId, 0, strlen($testComboId)) ){
							$addOk = TRUE;
							break;
						}
					}
				}
			}

			if( $addOk ){
				$to = 'new';
				$toParams = array(
					'calendar'	=> $id,
					// 'date'		=> $date
					);
				if( array_key_exists('employee', $params) && (count($params['employee']) == 1) ){
					if( ! in_array(-1, $params['employee']) ){
						$toParams['employee'] = $params['employee'][0];
					}
				}

				$to = array( $to, $toParams );

				$link = $this->ui->makeAhref( $to, $label )
					->tag('tab-link')
					->tag('align', 'center')
					;
				// if( $today > $date ){
					// $link->tag('muted', 3);
				// }

				$calendarView = $this->ui->makeList( array($calendarView, $link) )
					->gutter(1)
					;
			}

			$this->shiftsDuration->reset();
			if( isset($view_shifts[$id]) ){
				foreach( $view_shifts[$id] as $date => $dateShifts ){
					foreach( $dateShifts as $shift ){
						$this->shiftsDuration->add( $shift );
					}
				}

				$outReport = $this->common->renderReport( $this->shiftsDuration, FALSE );
				$outReport = $this->ui->makeSpan( $outReport )
					->tag('font-size', 2)
					;
				$calendarView = $this->ui->makeList( array($calendarView, $outReport) )
					->gutter(1)
					;
			}

			$calendarView = $this->ui->makeBlock( $calendarView )
				->tag('padding', 2)
				->tag('nowrap')
				;
			$row[] = $calendarView;

			foreach( $dates as $date ){
				$cell = array();

				if( isset($view_shifts[$id][$date]) ){
					$shiftsView = $this->self->renderDay( $view_shifts[$id][$date], $iknow );
					$cell[] = $shiftsView;
				}

				$thisCalendar = $calendars[$id];

				$to = 'new';
				$toParams = array(
					'calendar'	=> $id,
					'date'		=> $date
					);
				if( array_key_exists('employee', $params) && (count($params['employee']) == 1) ){
					if( ! in_array(-1, $params['employee']) ){
						$toParams['employee'] = $params['employee'][0];
					}
				}

				if( $addOk ){
					$to = array( $to, $toParams );

					$link = $this->ui->makeAhref( $to, $label )
						->tag('tab-link')
						->tag('align', 'center')
						;
					if( $today > $date ){
						$link->tag('muted', 3);
					}

					$cell[] = $link;
				}

				$cell = $this->ui->makeList( $cell )
					->gutter(1)
					->tag('margin', 1)
					;

				$row[] = $cell;
			}

			$rows[] = $row;
		}

		$out = $this->ui->makeTable( $header, $rows, FALSE )
			->gutter(0)
			->setBordered( $this->borderColor )
			// ->setSegments( $weekSegments )
			;

		return $out;
	}

	public function renderByEmployee()
	{
		$slug = $this->request->getSlug();
		$today = $this->t->setNow()->formatDateDb();

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

		$disabledWeekdays = $this->settings->get( 'skip_weekdays', TRUE );

		$params = $this->request->getParams();
		$startDate = $params['start'];

		$this->t->setDateDb( $startDate )->setStartWeek();
		$dates = array();

		for( $ii = 0; $ii <= 6; $ii++ ){
			if( $disabledWeekdays ){
				$wkd = $this->t->getWeekday();
				if( in_array($wkd, $disabledWeekdays) ){
					$this->t->modify('+1 day');
					continue;
				}
			}

			$dates[] = $this->t->formatDateDb();
			$this->t->modify('+1 day');
		}

		$view_employees = array();
		foreach( $employees as $employee ){
			$label = $this->employeesPresenter->presentTitle( $employee );
			$title = htmlspecialchars( $label );

			$description = $employee->getDescription();
			if( strlen($description) ){
				$imgpos = strpos( $description, '<img' );
				if( false !== $imgpos ){
					$label = $this->ui->makeList( array($label, $description) );
				}
				else {
					$title .= "\n" . htmlspecialchars( $description );
				}
			}

			$label = $this->ui->makeSpan( $label )
				->tag( 'font-size', 4 )
				->addAttr( 'title', $title )
				;

			$view_employees[ $employee->getId() ] = $label;
		}

		$view_shifts = array();

		$iknow = array('employee', 'date');

		$allCalendars = $this->common->findAllCalendars();
		if( count($allCalendars) <= 1 ) $iknow[] = 'calendar';

		$hori = FALSE;

		foreach( $shifts as $shift ){
			$employee = $shift->getEmployee();
			$id = $employee ? $employee->getId() : 0;

			if( ! array_key_exists($id, $view_shifts) ){
				$view_shifts[$id] = array();
			}

			$start = $shift->getStart();
			$end = $shift->getEnd();

			$startDayTime = substr($start, 8, 4);
			$endDayTime = substr($end, 8, 4);
			$shiftAllDay = ( (0 == $startDayTime) && (0 == $endDayTime) ) ? TRUE : FALSE;

			$this->t->setDateTimeDb( $end )->modify('-1 second');
			$shiftEndDate = $this->t->formatDateDb();

			$this->t->setDateTimeDb( $start );
			$shiftStartDate = $this->t->formatDateDb();

			$rexDate = $shiftStartDate;
			while( $rexDate <= $shiftEndDate ){
				if( in_array($rexDate, $dates) ){
					if( ! array_key_exists($rexDate, $view_shifts[$id]) ){
						$view_shifts[$id][$rexDate] = array();
					}
					$view_shifts[$id][$rexDate][] = $shift;
				}

				if( ! $shiftAllDay ){
					break;
				}
				$this->t->modify( '+1 day' );
				$rexDate = $this->t->formatDateDb();
			}
		}

		$header = array();
		$header[] = NULL;
		foreach( $dates as $date ){
			$this->t->setDateDb( $date );
			$dateView = $this->self->renderDateLabel( $date );

		// SHIFTS DURATION
			$counted = 0;
			$this->shiftsDuration->reset();

			reset( $view_shifts );
			foreach( $view_shifts as $id => $shifts2 ){
				if( isset($shifts2[$date]) ){
					$dateStart = $this->t->setDateDb( $date )
						->formatDateTimeDb()
						;
					$dateEnd = $this->t->modify('+1 day')
						->formatDateTimeDb()
						;
					foreach( $shifts2[$date] as $shift ){
						if( $shift->isMultiDay() ){
							$durationShift = clone $shift;
							if( $dateStart > $durationShift->getStart() ){
								$durationShift->setStart( $dateStart );
							}
							if( $dateEnd < $durationShift->getEnd() ){
								$durationShift->setEnd( $dateEnd );
							}
							$this->shiftsDuration->add( $durationShift );
						}
						else {
							$this->shiftsDuration->add( $shift );
						}
						$counted++;
					}
				}
			}
			if( $counted ){
				$outReport = $this->common->renderReport( $this->shiftsDuration, FALSE );
				$outReport = $this->ui->makeSpan( $outReport )
					->tag('font-size', 2)
					;
				$dateView = $this->ui->makeList( array($dateView, $outReport) )
					->gutter(1)
					;
			}

			$dateView = $this->ui->makeBlock( $dateView )
				->tag('align', 'center')
				;

			$header[] = $dateView;
		}

		$rows = array();

		foreach( $view_employees as $id => $employeeView ){
			$row = array();

			$this->shiftsDuration->reset();
			if( isset($view_shifts[$id]) ){
				$counted = 0;
				foreach( $view_shifts[$id] as $date => $dateShifts ){
					foreach( $dateShifts as $shift ){
						$shiftCalendar = $shift->getCalendar();
						// if( ! $shiftCalendar->isShift() ){
							// continue;
						// }
						$this->shiftsDuration->add( $shift );
						$counted++;
					}
				}

				if( $counted ){
					$outReport = $this->common->renderReport( $this->shiftsDuration, FALSE );
					$outReport = $this->ui->makeSpan( $outReport )
						->tag('font-size', 2)
						;
					$employeeView = $this->ui->makeList( array($employeeView, $outReport) )
						->gutter(1)
						;
				}
			}

			$thisShifts = isset( $view_shifts[$id] ) ? $view_shifts[$id] : array();
			$employeeView = $this->common->employeeRowLabel( $employeeView, $id, 'week', $dates, $thisShifts );

			$employeeView = $this->ui->makeBlock( $employeeView )
				->tag('padding', 2)
				->tag('nowrap')
				;
			$row[] = $employeeView;

			foreach( $dates as $date ){
				$cell = array();

				if( isset($view_shifts[$id][$date]) ){
					$shiftsView = $this->self->renderDay( $view_shifts[$id][$date], $iknow );
					$cell[] = $shiftsView;
				}

				$toParams = array(
					'employee'	=> $id,
					'date'		=> $date
					);

				$links = array();

				$comboId = 0 . '-' . $id;

				if( isset($this->allCombosShift[$comboId]) ){
					$label = '+' . ' ' . '__Shift__';
					$thisTo = 'new/shift';
					$thisTo = array( $thisTo, $toParams );
					$link = $this->ui->makeAhref( $thisTo, $label )
						->tag('tab-link')
						->tag('align', 'center')
						;
					if( $today > $date ){
						$link->tag('muted', 3);
					}
					$links[] = $link;
				}

				if( isset($this->allCombosTimeoff[$comboId]) ){
					$label = '+' . ' ' . '__Time Off__';
					$thisTo = 'new/timeoff';
					$thisTo = array( $thisTo, $toParams );
					$link = $this->ui->makeAhref( $thisTo, $label )
						->tag('tab-link')
						->tag('align', 'center')
						;
					if( $today > $date ){
						$link->tag('muted', 3);
					}
					$links[] = $link;
				}

				if( $links ){
					$links = $this->ui->makeList( $links )->gutter(0);
					$cell[] = $links;
				}

				$cell = $this->ui->makeList( $cell )
					->gutter(1)
					->tag('margin', 1)
					;

				$row[] = $cell;
			}

			$rows[] = $row;
		}

		$out = $this->ui->makeTable( $header, $rows, FALSE )
			->gutter(0)
			->setBordered( $this->borderColor )
			// ->setSegments( $weekSegments )
			;

		return $out;
	}

	public function renderDay( $shifts, $iknow )
	{
		$return = NULL;
		if( ! $shifts ){
			return $return;
		}

		$params = $this->request->getParams();

		$hideui = $params['hideui'];
		$noZoom = in_array('shiftdetails', $hideui);

		if( array_key_exists('employee', $params) && (count($params['employee']) == 1) ){
			if( ! in_array(-1, $params['employee']) ){
				$iknow[] = 'employee';
			}
		}
		if( array_key_exists('calendar', $params) && (count($params['calendar']) == 1) ){
			$iknow[] = 'calendar';
		}

	// groups?
		$groups = array();
		$groupedShifts = array();
		reset( $shifts );
		foreach( $shifts as $shift ){
			if( ! $shift->isOpen() ){
				continue;
			}

			$groupId = $shift->getGroupingId();

			if( ! isset($groups[$groupId]) ){
				$groups[ $groupId ] = $shift->getId();
				$groupedShifts[ $shift->getId() ] = array( $shift->getId() => $shift );
			}
			else {
				$mainShiftId = $groups[ $groupId ];
				$groupedShifts[ $mainShiftId ][ $shift->getId() ] = $shift;
				$groupedShifts[ $shift->getId() ] = 0;
			}
		}

		$hori = FALSE;
		$return = array();
		reset( $shifts );
		foreach( $shifts as $shift ){
			$id = $shift->getId();

			if( isset($groupedShifts[$id]) && (! $groupedShifts[$id]) ){
				continue;
			}

			$groupedQty = NULL;
			if( isset($groupedShifts[$id]) && (count($groupedShifts[$id]) > 1) ){
				$groupedQty = count($groupedShifts[$id]);
			}

			$thisView = $this->widget->render( $shift, $iknow, $hori, $noZoom, $groupedQty );
			$shiftId = $shift->getId();

			$buildMenuFromShifts = isset($groupedShifts[$id]) ? $groupedShifts[$id] : array( $shift );
			$menu = $this->shiftsCommon->bulkMenu( $buildMenuFromShifts );
			if( $menu ){
				$thisView = $this->ui->makeCollection( array($thisView, $menu) );
			}

			$thisView = $this->ui->makeBlock( $thisView )
				->tag('block')
				->addAttr('class', 'sh4-shift-widget')
				;

			$return[] = $thisView;
		}

		$return = $this->ui->makeList( $return )->gutter(1);

		return $return;
	}
}