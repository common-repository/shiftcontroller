<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
#[AllowDynamicProperties]
class SH4_Schedule_Html_View_Day
{
	protected $borderColor = 'gray';
	protected $allCombos = array();
	protected $allCombosShift = array();
	protected $allCombosTimeoff = array();

	public static $daysToShow = 1;

	public function __construct(
		HC3_Hooks $hooks,
		HC3_Ui $ui,
		HC3_Request $request,
		HC3_Time $t,
		HC3_Settings $settings,

		HC3_Uri $uri,

		SH4_New_Acl $newAcl,

		SH4_Schedule_Html_Widget_DayGrid $widgetDayGrid,

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

		$this->widgetDayGrid = $hooks->wrap( $widgetDayGrid );
		$this->shiftsDuration = $hooks->wrap( $shiftsDuration );
		$this->calendarsPresenter = $hooks->wrap( $calendarsPresenter );
		$this->employeesPresenter = $hooks->wrap( $employeesPresenter );

		$this->widget = $hooks->wrap( $widget );
		$this->common = $hooks->wrap( $common );
		$this->shiftsCommon = $hooks->wrap( $shiftsCommon );

		$this->allCombos = $this->newAcl->findAllCombos();
		$this->allCombosShift = $this->newAcl->findAllCombosShift();
		$this->allCombosTimeoff = $this->newAcl->findAllCombosTimeoff();
	}

	public function renderDateLabel( $date )
	{
		$slug = $this->request->getSlug();
		$today = $this->t->setNow()->formatDateDb();

		$this->t->setDateDb( $date );
		$ret = $this->t->getWeekdayName() . ', ' . $this->t->formatDate();

		return $ret;
	}

	public function render()
	{
		$shifts = $this->common->getShifts();
		$params = $this->request->getParams();
		$today = $this->t->setNow()->formatDateDb();

		$startDate = $params['start'];
		$endDate = $startDate;
		if( static::$daysToShow > 1 ){
			$endDate = $this->t->setDateDb( $startDate )->modify( '+' . (static::$daysToShow - 1) . ' days')->formatDateDb();
		}

		$dayStart = $this->t->setDateDb( $startDate )->formatDateTimeDb();
		$dayEnd = $this->t->setDateDb( $endDate )->modify( '+1 day')->formatDateTimeDb();

		$disabledWeekdays = $this->settings->get( 'skip_weekdays', TRUE );
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

		$endDate = $startDate;

		$viewShifts = array();
		$iknow = array('date');

		$this->shiftsDuration->reset();

		reset( $shifts );
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

		$iknow = array('date');
		$rows = array();

		foreach( $dates as $date ){
			$row = array();
			$cell = array();

			$this->t->setDateDb( $date );
			$dateView = $this->self->renderDateLabel( $date );
			$cell[] = $dateView; 

			$thisShifts = isset($viewShifts[$date]) ? $viewShifts[$date] : array();

			if( $thisShifts ){
				$gridView = $this->self->renderDay( $thisShifts, $iknow, $date );
				$cell[] = $gridView;
			}

			$toParams = array(
				'date'		=> $date
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

			$rows[] = $row;
		}

		$out = $this->ui->makeTable( NULL, $rows, FALSE )
			->gutter(0)
			// ->setBordered( $this->borderColor )
			;

		return $out;
	}

	public function renderByCalendar()
	{
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

		$today = $this->t->setNow()->formatDateDb();
		$disabledWeekdays = $this->settings->get( 'skip_weekdays', TRUE );

		$params = $this->request->getParams();
		$startDate = $params['start'];

		$endDate = $startDate;
		if( static::$daysToShow > 1 ){
			$endDate = $this->t->setDateDb( $startDate )->modify( '+' . (static::$daysToShow - 1) . ' days')->formatDateDb();
		}

		$dayStart = $this->t->setDateDb( $startDate )->formatDateTimeDb();
		$dayEnd = $this->t->setDateDb( $endDate )->modify( '+1 day')->formatDateTimeDb();

		$disabledWeekdays = $this->settings->get( 'skip_weekdays', TRUE );
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

			$viewCalendars[ $calendarId ] = $label;
		}

		$viewShifts = array();

		$iknow = array('calendar', 'date');
		$hori = FALSE;

		$allEmployees = $this->common->findAllEmployees();
		if( count($allEmployees) < 2 ){
			$iknow[] = 'employee';
		}

		$out = array();

		reset( $dates );
		foreach( $dates as $date ){
			$thisOut = array();
			$rows = array();

			$this->t->setDateDb( $date );
			$dateView = $this->self->renderDateLabel( $date );
			$thisOut[] = $dateView; 

			$viewShifts = array();

			reset( $shifts );
			foreach( $shifts as $shift ){
				$start = $shift->getStart();
				$end = $shift->getEnd();

				$startDate = $this->t->setDateTimeDb( $start )->formatDateDb();
				// echo "START '$startDate' VS '$date'<br>";
				if( $startDate > $date ){
					continue;
				}

				$endDate = $this->t->setDateTimeDb( $end )->formatDateDb();
				// echo "END '$endDate' VS '$date'<br>";
				if( $endDate < $date ){
					continue;
				}

				// echo "'$startDate' - '$endDate' VS '$date' OK<br><br>";

				$calendar = $shift->getCalendar();
				$id = $calendar->getId();

				if( ! array_key_exists($id, $viewShifts) ){
					$viewShifts[$id] = array();
				}

				$viewShifts[$id][] = $shift;
			}

			$header = array();
			$header[] = NULL;
			$header[] = NULL;

			$dayStart = $this->t->setDateDb( $startDate )->formatDateTimeDb();
			$dayEnd = $this->t->setDateDb( $startDate )->modify('+1 day')->formatDateTimeDb();

			reset( $viewCalendars );
			foreach( $viewCalendars as $id => $calendarView ){
				$row = array();

				$this->shiftsDuration->reset();
				if( isset($viewShifts[$id]) ){
					foreach( $viewShifts[$id] as $shift ){
						$this->shiftsDuration->add( $shift );
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

				$cell = array();

				if( isset($viewShifts[$id]) ){
					$gridView = $this->self->renderDay( $viewShifts[$id], $iknow, $date );
					$cell[] = $gridView;
				}

				$thisCalendar = $calendars[$id];
				if( $thisCalendar->isTimeoff() ){
					$label = '+ ' . '__Time Off__';
				}
				else {
					$label = '+ ' . '__Shift__';
				}

				$to = 'new';
				$toParams = array(
					'date'		=> $date
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

				$rows[] = $row;
			}

			$thisOut[] = $this->ui->makeTable( $header, $rows, FALSE )
				->gutter(0)
				->setBordered( $this->borderColor )
				// ->setSegments( $weekSegments )
				->setLabelled( TRUE )
				;

			$thisOut = $this->ui->makeList( $thisOut );

			$out[] = $thisOut;
		}

		$out = $this->ui->makeList( $out );

		return $out;
	}

	public function renderByEmployee()
	{
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

		$today = $this->t->setNow()->formatDateDb();

		$disabledWeekdays = $this->settings->get( 'skip_weekdays', TRUE );

		$params = $this->request->getParams();
		$startDate = $params['start'];
		$endDate = $startDate;
		if( static::$daysToShow > 1 ){
			$endDate = $this->t->setDateDb( $startDate )->modify( '+' . (static::$daysToShow - 1) . ' days')->formatDateDb();
		}

		$dayStart = $this->t->setDateDb( $startDate )->formatDateTimeDb();
		$dayEnd = $this->t->setDateDb( $endDate )->modify( '+1 day')->formatDateTimeDb();

		$disabledWeekdays = $this->settings->get( 'skip_weekdays', TRUE );
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
		$hori = FALSE;

		$out = array();

		reset( $dates );
		foreach( $dates as $date ){
			$thisOut = array();

			$thisOut = array();
			$rows = array();

			$this->t->setDateDb( $date );
			$dateView = $this->self->renderDateLabel( $date );
			$thisOut[] = $dateView; 

			$viewShifts = array();

			foreach( $shifts as $shift ){
				$employee = $shift->getEmployee();
				$id = $employee ? $employee->getId() : 0;

				if( ! array_key_exists($id, $viewShifts) ){
					$viewShifts[$id] = array();
				}

				$viewShifts[$id][] = $shift;
			}

			$header = array();
			$header[] = NULL;
			$header[] = NULL;

			$rows = array();

			foreach( $viewEmployees as $id => $employeeView ){
				$row = array();

				$this->shiftsDuration->reset();
				if( isset($viewShifts[$id]) ){
					$counted = 0;
					foreach( $viewShifts[$id] as $shift ){
						$shiftCalendar = $shift->getCalendar();
						// if( ! $shiftCalendar->isShift() ){
							// continue;
						// }
						$this->shiftsDuration->add( $shift );
						$counted++;
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
				$employeeView = $this->common->employeeRowLabel( $employeeView, $id, 'day', $dates, $thisShifts );

				$employeeView = $this->ui->makeBlock( $employeeView )
					->tag('padding', 2)
					->tag('nowrap')
					;
				$row[] = $employeeView;

				$cell = array();

				if( isset($viewShifts[$id]) ){
					$gridView = $this->self->renderDay( $viewShifts[$id], $iknow, $date );
					$cell[] = $gridView;
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
						// ->addAttr('title', '__Add New__')
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

				$rows[] = $row;
			}


			$thisOut[] = $this->ui->makeTable( $header, $rows, FALSE )
				->gutter(0)
				->setBordered( $this->borderColor )
				// ->setSegments( $weekSegments )
				->setLabelled( TRUE )
				;

			$thisOut = $this->ui->makeList( $thisOut );

			$out[] = $thisOut;
		}

		$out = $this->ui->makeList( $out );

		return $out;
	}

	public function renderDay( $shifts, $iknow, $startDate = NULL )
	{
		$hori = FALSE;
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

		if( NULL === $startDate ){
			$startDate = $params['start'];
		}

		$this->t->setDateDb( $startDate );
		$minTime = $this->settings->get('datetime_min_time');
		if( $minTime ){
			$this->t->modify('+' . $minTime . ' seconds');
		}
		$dayStart = $this->t->formatDateTimeDb();

		$this->t->setDateDb( $startDate );
		$maxTime = $this->settings->get('datetime_max_time');
		if( $maxTime ){
			$this->t->modify('+' . $maxTime . ' seconds');
		}
		else {
			$this->t->modify('+1 day');
		}
		$dayEnd = $this->t->formatDateTimeDb();

	// check if we need to extend ranges to show overtime shifts
		if( $shifts ){
			$this->t->setDateDb( $startDate )->modify('+1 day');
			$minCheckEnd = $this->t->formatDateTimeDb();

			$this->t->modify('+1 day');
			$maxCheckEnd = $this->t->formatDateTimeDb();

			foreach( $shifts as $shift ){
				$thisEnd = $shift->getEnd();
				if( ($thisEnd > $minCheckEnd) && ($thisEnd < $maxCheckEnd) ){
					if( $thisEnd > $dayEnd ){
						$dayEnd = $thisEnd;
					}
				}
			}
		}

		$grid = $this->widgetDayGrid
			->reset()
			;
		$grid->setRange( $dayStart, $dayEnd );

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
				->addAttr('style', 'padding-right: 1px;')
				;

			$grid
				->add( $shift->getStart(), $shift->getEnd(), $thisView )
				;
		}

		$return = $grid->render();
		return $return;
	}
}