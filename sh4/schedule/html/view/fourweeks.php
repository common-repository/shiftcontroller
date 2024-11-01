<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
#[AllowDynamicProperties]
class SH4_Schedule_Html_View_FourWeeks
{
	protected $borderColor = 'gray';
	protected $allCombos = array();
	protected $allCombosShift = array();
	protected $allCombosTimeoff = array();
	protected $nWeeks = 4;
	protected $slimView = true;

	public function __construct(
		HC3_Hooks $hooks,
		HC3_Ui $ui,
		HC3_Request $request,
		HC3_Time $t,
		HC3_Settings $settings,

		HC3_Uri $uri,

		SH4_New_Acl $newAcl,

		SH4_Shifts_Duration $shiftsDuration,
		SH4_Schedule_Html_View_Week $viewWeek,
		SH4_Schedule_Html_View_Month $viewMonth,
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

		$this->viewWeek = $hooks->wrap( $viewWeek );
		$this->viewMonth = $hooks->wrap( $viewMonth );
		$this->shiftsDuration = $hooks->wrap( $shiftsDuration );
		$this->calendarsPresenter = $hooks->wrap( $calendarsPresenter );
		$this->employeesPresenter = $hooks->wrap( $employeesPresenter );

		$this->widget = $hooks->wrap( $widget );
		$this->common = $hooks->wrap( $common );
		$this->shiftsCommon = $hooks->wrap( $shiftsCommon );

		$this->allCombos = $this->newAcl->findAllCombos();
		$this->allCombosShift = $this->newAcl->findAllCombosShift();
		$this->allCombosTimeoff = $this->newAcl->findAllCombosTimeoff();

		$this->nWeeks = $this->settings->get( 'datetime_n_weeks' );
		if( ! $this->nWeeks ) $this->nWeeks = 4;
		$this->slimView = $this->settings->get( 'datetime_month_slim_view' ) ? true : false;
	}

	public function render()
	{
		$slug = $this->request->getSlug();
		$today = $this->t->setNow()->formatDateDb();

		$disabledWeekdays = $this->settings->get( 'skip_weekdays', TRUE );

		$shifts = $this->common->getShifts();

		$params = $this->request->getParams();
		$startDate = $params['start'];

		$startDate = $this->t->setDateDb( $startDate )->setStartWeek()->formatDateDb();
		$endDate = $this->t->modify('+' . $this->nWeeks . ' weeks')->modify('-1 day')->formatDateDb();

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

		$this->t->setDateDb( $startDate );
		// $monthMatrix = $this->t->getMonthMatrix( $disabledWeekdays );
		$monthMatrix = $this->t->getWeeksMatrix( $this->nWeeks, $disabledWeekdays );
		$rows = array();
		foreach( $monthMatrix as $week => $days ){
			$row = array();

			foreach( $days as $date ){
				if( ! $date ){
					$row[] = NULL;
					continue;
				}

				$cell = array();

				$this->t->setDateDb( $date );
				$dateView = $this->common->renderDateLabel( $date );

				$cell[] = $dateView;

				if( isset($viewShifts[$date]) ){
					$shiftsView = $this->viewWeek->renderDay( $viewShifts[$date], $iknow );
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
		}

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

		$disabledWeekdays = $this->settings->get( 'skip_weekdays', TRUE );

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

		$params = $this->request->getParams();
		$startDate = $params['start'];

		$startDate = $this->t->setDateDb( $startDate )->setStartWeek()->formatDateDb();
		$endDate = $this->t->modify('+' . $this->nWeeks . ' weeks')->modify('-1 day')->formatDateDb();

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

		$viewCalendars = array();
		foreach( $calendars as $calendar ){
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

			$viewCalendars[ $calendar->getId() ] = $label;
		}

		$viewShifts = array();

		$iknow = array('calendar', 'date');
		foreach( $shifts as $shift ){
			$calendar = $shift->getCalendar();
			$id = $calendar ? $calendar->getId() : 0;

			if( ! array_key_exists($id, $viewShifts) ){
				$viewShifts[$id] = array();
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
					if( ! array_key_exists($rexDate, $viewShifts[$id]) ){
						$viewShifts[$id][$rexDate] = array();
					}
					$viewShifts[$id][$rexDate][] = $shift;
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

		$weekStartsOn = $this->t->getWeekStartsOn();
		$weekSegments = array();

		$this->t->setDateDb( $startDate );
		// $monthMatrix = $this->t->getMonthMatrix( $disabledWeekdays );
		$monthMatrix = $this->t->getWeeksMatrix( $this->nWeeks, $disabledWeekdays );

		foreach( $monthMatrix as $week => $days ){
			$wdi = 0;
			foreach( $days as $date ){
				if( ! $date ){
					continue;
				}

				$dateView = $this->common->renderDateLabelGrouped( $date );
				$header[] = $dateView;

				if( ! $wdi ){
					$weekSegments[] = count($header);
				}

				$wdi++;
			}
		}

		// while( count($header) < 32 ){
			// $header[] = NULL;
		// }

		$rows = array();
		foreach( $viewCalendars as $id => $calendarView ){
			$row = array();

			$this->shiftsDuration->reset();
			if( isset($viewShifts[$id]) ){
				foreach( $viewShifts[$id] as $date => $dateShifts ){
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
				->tag('padding', array('x1', 'y2'))
				->tag('nowrap')
				;
			$row[] = $calendarView;

			$countDates = count( $dates );
			foreach( $dates as $date ){
				$cell = array();

				if( isset($viewShifts[$id][$date]) ){
					if( ($countDates > 10) && ($this->slimView) ){
						$shiftsView = $this->viewMonth->renderDay( $viewShifts[$id][$date], $iknow );
					}
					else {
						$shiftsView = $this->viewWeek->renderDay( $viewShifts[$id][$date], $iknow );
					}
					$cell[] = $shiftsView;
				}

				$label = '+';

				$to = 'new';
				$toParams = array(
					'date'	=> $date,
					'calendar'	=> $id,
					);
				if( array_key_exists('employee', $params) && (count($params['employee']) == 1) ){
					if( ! in_array(-1, $params['employee']) ){
						$toParams['employee'] = $params['employee'][0];
					}
				}
				$to = array( $to, $toParams );

				$addOk = FALSE;
				if( isset($toParams['employee']) ){
					$testComboId = $id . '-' . $toParams['employee'];
					if( isset($this->allCombos[$testComboId]) ){
						$addOk = TRUE;
					}
				}
				else {
					$testComboId = $id . '-';
					reset( $this->allCombos );
					foreach( $this->allCombos as $comboId ){
						if( $testComboId == substr($comboId, 0, strlen($testComboId)) ){
							$addOk = TRUE;
							break;
						}
					}
				}

				if( $addOk ){
					$link = $this->ui->makeAhref( $to, $label )
						->tag('tab-link')
						->tag('align', 'center')
						->addAttr('title', '__Add New__')
						;

					if( $today > $date ){
						$link->tag('muted', 3);
					}

					$cell[] = $link;
				}

				$cell = $this->ui->makeList( $cell )
					->gutter(1)
					->tag('margin', '05')
					;

				$row[] = $cell;
			}

			// while( count($row) < 32 ){
				// $row[] = NULL;
			// }
			$rows[] = $row;
		}

		$out = $this->ui->makeTable( $header, $rows, FALSE )
			->gutter(0)
			->setBordered( $this->borderColor )
			->setSegments( $weekSegments )
			->setLabelled( TRUE )
			;

		if( ! $this->slimView ){
			$out->forceColWidth( '6rem' );
			$out->forceRowHeaderWidth( '10rem' );
		}

		return $out;
	}

	public function renderByEmployee()
	{
		$slug = $this->request->getSlug();
		$today = $this->t->setNow()->formatDateDb();

		$disabledWeekdays = $this->settings->get( 'skip_weekdays', TRUE );

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

		$params = $this->request->getParams();
		$startDate = $params['start'];

		$startDate = $this->t->setDateDb( $startDate )->setStartWeek()->formatDateDb();
		$endDate = $this->t->modify('+' . $this->nWeeks . ' weeks')->modify('-1 day')->formatDateDb();

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

		$viewEmployees = array();
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

			$viewEmployees[ $employee->getId() ] = $label;
		}

		$viewShifts = array();

		$iknow = array('employee', 'date');

		$allCalendars = $this->common->findAllCalendars();
		if( count($allCalendars) <= 1 ) $iknow[] = 'calendar';

		foreach( $shifts as $shift ){
			$employee = $shift->getEmployee();
			$id = $employee ? $employee->getId() : 0;

			if( ! array_key_exists($id, $viewShifts) ){
				$viewShifts[$id] = array();
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
					if( ! array_key_exists($rexDate, $viewShifts[$id]) ){
						$viewShifts[$id][$rexDate] = array();
					}
					$viewShifts[$id][$rexDate][] = $shift;
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

		$weekStartsOn = $this->t->getWeekStartsOn();
		$weekSegments = array();

		$this->t->setDateDb( $startDate );
		// $monthMatrix = $this->t->getMonthMatrix( $disabledWeekdays );
		$monthMatrix = $this->t->getWeeksMatrix( $this->nWeeks, $disabledWeekdays );

		foreach( $monthMatrix as $week => $days ){
			$wdi = 0;
			foreach( $days as $date ){
				if( ! $date ){
					continue;
				}
				$this->t->setDateDb( $date );

				$dateView = $this->common->renderDateLabelGrouped( $date );
				$header[] = $dateView;

				if( ! $wdi ){
					$weekSegments[] = count($header);
				}

				$wdi++;
			}
		}

		// while( count($header) < 32 ){
			// $header[] = NULL;
		// }

		$rows = array();
		foreach( $viewEmployees as $id => $employeeView ){
			$row = array();

			$this->shiftsDuration->reset();
			if( isset($viewShifts[$id]) ){
				$counted = 0;
				foreach( $viewShifts[$id] as $date => $dateShifts ){
					foreach( $dateShifts as $shift ){
						$shiftCalendar = $shift->getCalendar();
						// if( $shiftCalendar->isTimeoff() ){
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

			$thisShifts = isset($viewShifts[$id]) ? $viewShifts[$id] : array();
			$employeeView = $this->common->employeeRowLabel( $employeeView, $id, '4weeks', $dates, $thisShifts );

			$employeeView = $this->ui->makeBlock( $employeeView )
				->tag('padding', array('x1', 'y2'))
				->tag('nowrap')
				;
			$row[] = $employeeView;

			$countDates = count( $dates );
			foreach( $dates as $date ){
				$cell = array();

				if( isset($viewShifts[$id][$date]) ){
					if( ($countDates > 10) && ($this->slimView) ){
						$shiftsView = $this->viewMonth->renderDay( $viewShifts[$id][$date], $iknow );
					}
					else {
						$shiftsView = $this->viewWeek->renderDay( $viewShifts[$id][$date], $iknow );
					}
					$cell[] = $shiftsView;
				}

				$comboId = 0 . '-' . $id;
				if( isset($this->allCombos[$comboId]) ){
					$label = '+';

					$to = 'new';
					$toParams = array(
						'date'		=> $date,
						'employee'	=> $id,
						);
					$to = array( $to, $toParams );

					$link = $this->ui->makeAhref( $to, $label )
						->tag('tab-link')
						->tag('align', 'center')
						->addAttr('title', '__Add New__')
						;

					if( $today > $date ){
						$link->tag('muted', 3);
					}

					$cell[] = $link;
				}

				$cell = $this->ui->makeList( $cell )
					->gutter(1)
					->tag('margin', '05')
					;

				$row[] = $cell;
			}

			// while( count($row) < 32 ){
				// $row[] = NULL;
			// }
			$rows[] = $row;
		}

		$out = $this->ui->makeTable( $header, $rows, FALSE )
			->gutter(0)
			->setBordered( $this->borderColor )
			->setSegments( $weekSegments )
			->setLabelled( TRUE )
			;

		if( ! $this->slimView ){
			$out->forceColWidth( '6rem' );
			$out->forceRowHeaderWidth( '10rem' );
		}

		return $out;
	}
}