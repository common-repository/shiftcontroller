<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
#[AllowDynamicProperties]
class SH4_Shifts_View_Date
{
	public function __construct(
		HC3_Hooks $hooks,

		HC3_Settings $settings,
		HC3_Request $request,
		HC3_Time $t,
		HC3_Ui $ui,
		HC3_Acl $acl,
		HC3_Ui_Layout1 $layout,

		SH4_Shifts_View_Common $common,
		SH4_Shifts_Query $shiftsQuery,

		SH4_Employees_Query $employees,
		SH4_App_Query $appQuery,

		SH4_Shifts_Conflicts $conflicts
		)
	{
		$this->request = $request;
		$this->ui = $ui;
		$this->acl = $acl;
		$this->t = $t;
		$this->layout = $layout;
		$this->settings = $hooks->wrap($settings);

		$this->shiftsQuery = $hooks->wrap( $shiftsQuery );

		$this->employees = $hooks->wrap($employees);
		$this->appQuery = $hooks->wrap( $appQuery );

		$this->common = $hooks->wrap($common);
		$this->self = $hooks->wrap($this);
		$this->conflicts = $hooks->wrap($conflicts);
	}

	public function ajaxRender( $shiftId )
	{
		$out = $this->self->renderDates( $shiftId );
		return $out;
	}

	public function renderDates( $shiftId )
	{
		$disabledWeekdays = $this->settings->get( 'skip_weekdays', TRUE );

		$params = $this->request->getParams();

		$showPrev = ( isset( $params['prev'] ) && (! $params['prev']) ) ? FALSE : TRUE;
		$showNext = ( isset( $params['next'] ) && (! $params['next']) ) ? FALSE : TRUE;

		$shift = $this->shiftsQuery->findById( $shiftId );

		$this->t->setDateTimeDb( $shift->getStart() );
		$shiftDate = $this->t->formatDateDb();
		$timeStart = $this->t->getTimestamp();
		$dayStart = $this->t->setStartDay()->getTimestamp();
		$timeStart = $timeStart - $dayStart;

		$this->t->setDateTimeDb( $shift->getEnd() );
		$timeEnd = $this->t->getTimestamp();
		$timeEnd = $timeEnd - $dayStart;

		$tsStart = $this->t->setDateTimeDb( $shift->getStart() )->getTimestamp();
		$tsEnd = $this->t->setDateTimeDb( $shift->getEnd() )->getTimestamp();;
		$duration = $tsEnd - $tsStart;
		$durationInDays = ceil( $duration/(24*60*60) );

		$calendar = $shift->getCalendar();
		$calendarId = $calendar->getId();
		$employee = $shift->getEmployee();
		$employeeId = $employee->getId();

		$weeksToShow = 4;
		$startDate = array_key_exists('date', $params) ? $params['date'] : $shiftDate;

		$out = array();
		$this->t->setDateDb( $startDate );
		$this->t->setStartWeek();

		$startDate = $this->t->formatDateDb();
		$displayedDays = 0;

		for( $ww = 1; $ww <= $weeksToShow; $ww++ ){
			$thisWeekView = array();

			for( $dd = 0; $dd <= 6; $dd++ ){
				$displayedDays++;
				$date = $this->t->formatDateDb();

				if( $disabledWeekdays ){
					$wkd = $this->t->getWeekday();
					if( in_array($wkd, $disabledWeekdays) ){
						$this->t->modify('+1 day');
						continue;
					}
				}

				$suitable = TRUE;
				// if( $date <= $shiftDate ){
					// $suitable = FALSE;
				// }

				if( $date == $shiftDate ){
					$suitable = FALSE;
				}

				$thisView = array();
				$label = $this->t->formatDate();

				if( $suitable ){
					$checkParams = $params;
					$checkParams['date'] = $date;
					$suitable = $this->acl->check( 'get:new', $checkParams );
				}

				if( $suitable ){
					$label = $this->ui->makeInputRadio( 'date', $label, $date )
						->setHtmlId( 'hc-date-' . $date )
						->asList()
						;
				}
				elseif( $date == $shiftDate ){
					// $label = $this->ui->makeListInline( array('&#10003;', $label) )
						// ->tag('nowrap')
						// ;
					$label = $this->ui->makeList( array('&#10003;', $label) )->gutter(0)
						->tag('align', 'center')
						;
				}
				else {
					$label = $this->ui->makeBlock( $label )
						->tag('muted')
						->tag('align', 'center')
						;
				}

				$thisView[] = $label;

			// check conflicts
				$conflicts = array();
				if( $suitable ){
					$start = $this->t->setDateDb( $date )
						->modify( '+' . $timeStart . ' seconds' )
						->formatDateTimeDb()
						;
					$this->t->setDateDb( $date );
					if( $durationInDays > 1 ){
						$this->t->modify( '+' . ($durationInDays - 1) . ' days' );
					}
					$end = $this->t
						->modify( '+' . $timeEnd . ' seconds' )
						->formatDateTimeDb()
						;

					$testModel = new SH4_Shifts_Model( null, $calendar, $start, $end, $employee );
					$conflicts = $this->conflicts->get( $testModel, array($shiftId) );
				}

				if( $conflicts ){
					$conflictsLabel = '__Conflicts__';

					$conflictsLink = array('conflicts', $shiftId, $calendarId, $start, $end, $employeeId);
					$conflictsLink = join('/', $conflictsLink);

					$conflictsView = $this->ui->makeAhref( $conflictsLink, $conflictsLabel )
						->newWindow()
						->tag('color', 'red')
						;
					// $conflictsView = $this->ui->makeListInline( array($conflictsView, '&nearr;'))->gutter(1);

					$thisView[] = $conflictsView;
				}

				$thisView = $this->ui->makeList( $thisView )->gutter(0);
				$thisView = $this->ui->makeBlock( $thisView )
					->tag('border')
					->tag('padding', 2)
					;

				if( $conflicts ){
					$thisView
						// ->tag('border-color', 'red')
						// ->tag('color', 'red')
						;
				}
				else {
					// if( $suitable OR ($date == $shiftDate) ){
					if( $suitable ){
						$thisView
							->tag('border-color', 'green')
							// ->tag('color', 'olive')
							;
					}
				}

				$thisWeekView[] = $thisView;

				$this->t->setDateDb( $date );
				$this->t->modify('+1 day');
			}

			$thisWeekView = $this->ui->makeGrid( $thisWeekView );

			$out[] = $thisWeekView;

			$lastDate = $date;
		}

		$out = $this->ui->makeList( $out );

		$nextTo = 'ajax/shifts/' . $shiftId . '/date';

	// next link
		$this->t->setDateDb( $lastDate );
		$this->t->modify('+1 day');
		$nextDate = $this->t->formatDateDb();

		$nextParams = $params;
		$nextParams['date'] = $nextDate;
		$nextParams['prev'] = 0;
		$nextLink = $this->ui->makeAhref( array($nextTo, $nextParams), '&darr; __Next__' )
			->tag('ajax')
			->tag('secondary')
			->tag('block')
			->tag('padding', 2)
			;

	// prev link
		$prevDate = $this->t
			->setDateDb( $startDate )
			->modify( '-' . $displayedDays . ' days' )
			->formatDateDb()
			;

		$prevParams = $params;
		$prevParams['date'] = $prevDate;
		$prevParams['next'] = 0;
		$prevLink = $this->ui->makeAhref( array($nextTo, $prevParams), '&uarr; __Previous__' )
			->tag('ajax')
			->tag('secondary')
			->tag('block')
			->tag('padding', 2)
			;

		$fullOut = array();

		if( $showPrev ){
			$fullOut[] = $prevLink;
		}

		$fullOut[] = $out;

		if( $showNext ){
			$fullOut[] = $nextLink;
		}

		$out = $this->ui->makeList( $fullOut );
		return $out;
	}

	public function render( $shiftId )
	{
		$disabledWeekdays = $this->settings->get( 'skip_weekdays', TRUE );
		$params = $this->request->getParams();

		$shift = $this->shiftsQuery->findById( $shiftId );

		$this->t->setStartWeek();
		$header = array();

		for( $ii = 0; $ii <= 6; $ii++ ){
			if( $disabledWeekdays ){
				$wkd = $this->t->getWeekday();
				if( in_array($wkd, $disabledWeekdays) ){
					$this->t->modify('+1 day');
					continue;
				}
			}

			$thisHeader = $this->t->getWeekdayName();
			$thisHeader = $this->ui->makeBlock( $thisHeader )
				// ->tag('font-size', 1)
				->tag('align', 'center')
				;
			$header[] = $thisHeader;
			$this->t->modify('+1 day');
		}

		$header = $this->ui->makeGrid( $header );

		$datesView = $this->self->renderDates( $shiftId );

		$out = $this->ui->makeList( array($header, $datesView) );

		$buttons = $this->ui->makeInputSubmit( '__Save__')
			->tag('primary')
			;

		$out = array( $out );

		$calendar = $shift->getCalendar();
		$calendarId = $calendar->getId();
		$employee = $shift->getEmployee();
		$employeeId = $employee->getId();
		$shiftTypeId = 0;

		$out[] = $buttons;

		$out = $this->ui->makeList( $out )->gutter(3);

	// schedule link
		$scheduleLink = HC3_Session::instance()->getUserdata( 'scheduleLink' );
		if( ! $scheduleLink ){
			$scheduleLink = array( 'schedule', array() );
		}
		$scheduleLinkValue = json_encode( $scheduleLink );
		$inputBackHidden = $this->ui->makeInputHidden( 'back', $scheduleLinkValue );
		$out = $this->ui->makeCollection( array($out, $inputBackHidden) );

		$to = array( 'shifts', $shiftId, 'date' );
		$to = join( '/', $to );
		$out = $this->ui->makeForm( $to, $out );

		$this->layout
			->setContent( $out )
			->setBreadcrumb( $this->common->breadcrumb($shift) )
			->setHeader( $this->self->header() )
			;
		$out = $this->layout->render();

		return $out;
	}

	public function header()
	{
		$ret = '__Change Date__';
		return $ret;
	}
}