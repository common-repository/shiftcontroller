<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
interface SH4_New_View_IDate
{
	public function render();
	public function ajaxRender();
	public function ajaxRenderHours();
	public function renderHours();
	public function renderStartDate();
	public function renderEndDate( $startDate );
	public function buttons( $calendarId, $shiftTypeId );
}

#[AllowDynamicProperties]
class SH4_New_View_Date implements SH4_New_View_IDate
{
	public function __construct(
		HC3_Hooks $hooks,
		HC3_Request $request,
		HC3_Ui $ui,
		HC3_Time $t,
		HC3_Ui_Layout1 $layout,
		HC3_Settings $settings,

		SH4_New_View_Common $common,

		SH4_Calendars_Query $calendarsQuery,
		SH4_ShiftTypes_Query $shiftTypesQuery
		)
	{
		$this->request = $request;
		$this->ui = $ui;
		$this->t = $t;
		$this->layout = $layout;

		$this->settings = $hooks->wrap( $settings );

		$this->calendarsQuery = $hooks->wrap( $calendarsQuery );
		$this->shiftTypesQuery = $hooks->wrap( $shiftTypesQuery );
		$this->common = $hooks->wrap($common);

		$this->self = $hooks->wrap($this);
	}

	public function render()
	{
		$params = $this->request->getParams();

		$shiftTypeId = $params['shifttype'];
		$calendarId = $params['calendar'];

		$shiftType = $this->shiftTypesQuery->findByid( $shiftTypeId );
		$range = $shiftType->getRange();

		switch( $range ){
			case SH4_ShiftTypes_Model::RANGE_DAYS:
				if( array_key_exists('start', $params) && $params['start'] ){
					$out = $this->self->renderEndDate( $params['start'] );
					$pageHeader = '__Select End Date__';
				}
				else {
					$out = $this->self->renderStartDate();
					$pageHeader = '__Select Start Date__';
				}
				break;

			case SH4_ShiftTypes_Model::RANGE_HOURS:
				$out = $this->self->renderHours();
				$pageHeader = '__Select Date__';
				break;
		}

		$disabledWeekdays = $this->settings->get( 'skip_weekdays', TRUE );

		// $startDate = array_key_exists('date', $params) ? $params['date'] : $this->t->setNow()->formatDateDb();
		// $this->t->setDateDb( $startDate);

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

		$out = $this->ui->makeList( array($header, $out) );

		$this->layout
			->setContent( $out )
			->setBreadcrumb( $this->common->breadcrumb($calendarId, $shiftTypeId) )
			->setHeader( $pageHeader )
			;
		$out = $this->layout->render();

		return $out;
	}

	public function ajaxRender()
	{
		$params = $this->request->getParams();

		$shiftTypeId = $params['shifttype'];
		$calendarId = $params['calendar'];

		$shiftType = $this->shiftTypesQuery->findByid( $shiftTypeId );
		$range = $shiftType->getRange();

		switch( $range ){
			case SH4_ShiftTypes_Model::RANGE_DAYS:
				if( array_key_exists('start', $params) && $params['start'] ){
					$out = $this->self->renderEndDate( $params['start'] );
				}
				else {
					$out = $this->self->renderStartDate();
				}
				break;

			case SH4_ShiftTypes_Model::RANGE_HOURS:
				$out = $this->self->renderHours();
				break;
		}

		return $out;
	}

	public function ajaxRenderHours()
	{
		$out = $this->self->renderDates( $calendarId, $shiftTypeId, $employeeId );
		return $out;
	}

	public function renderHours()
	{
		$disabledWeekdays = $this->settings->get( 'skip_weekdays', TRUE );

		$params = $this->request->getParams();

		$shiftTypeId = $params['shifttype'];
		$calendarId = $params['calendar'];

		$shiftType = $this->shiftTypesQuery->findByid( $shiftTypeId );
		$calendar = $this->calendarsQuery->findByid( $calendarId );

		$timeStart = $shiftType->getStart();
		$timeEnd = $shiftType->getEnd();

		$weeksToShow = 4;
		$startDate = array_key_exists('date', $params) ? $params['date'] : $this->t->setNow()->formatDateDb();

		$out = array();
		$this->t->setDateDb( $startDate );
		$this->t->setStartWeek();

		for( $ww = 1; $ww <= $weeksToShow; $ww++ ){
			$thisWeekView = array();

			for( $dd = 0; $dd <= 6; $dd++ ){
				$date = $this->t->formatDateDb();

				if( $disabledWeekdays ){
					$wkd = $this->t->getWeekday();
					if( in_array($wkd, $disabledWeekdays) ){
						$this->t->modify('+1 day');
						continue;
					}
				}

				$thisOn = FALSE;

				$label = $this->t->formatDate();

				$to = 'new';
				$toParams = $params;
				$toParams['date'] = $date;
				$to = array( $to, $toParams );

				$thisView = $this->ui->makeAhref( $to, $label )
					->tag('secondary')
					->tag('block')
					->tag('align', 'center')
					;

				$thisWeekView[] = $thisView;

				$this->t->setDateDb( $date );
				$this->t->modify('+1 day');
			}

			$thisWeekView = $this->ui->makeGrid( $thisWeekView );
			$out[] = $thisWeekView;

			$lastDate = $date;
		}

		$out = $this->ui->makeList( $out );

		$this->t->setDateDb( $lastDate );
		$this->t->modify('+1 day');
		$nextDate = $this->t->formatDateDb();

		$nextTo = 'ajax/new/date';
		$nextToParams = $params;
		$nextToParams['date'] = $nextDate;
		$nextTo = array( $nextTo, $nextToParams );

		$nextLink = $this->ui->makeAhref( $nextTo, '&darr;' )
			->tag('ajax')
			->tag('secondary')
			->tag('block')
			->tag('padding', 2)
			;

		$out = $this->ui->makeList( array($out, $nextLink) );
		return $out;
	}

	public function renderStartDate()
	{
		$disabledWeekdays = $this->settings->get( 'skip_weekdays', TRUE );

		$params = $this->request->getParams();

		$shiftTypeId = $params['shifttype'];
		$calendarId = $params['calendar'];

		$shiftType = $this->shiftTypesQuery->findByid( $shiftTypeId );
		$calendar = $this->calendarsQuery->findByid( $calendarId );

		$weeksToShow = 4;
		$startDate = array_key_exists('date', $params) ? $params['date'] : $this->t->setNow()->formatDateDb();

		$out = array();
		$this->t->setDateDb( $startDate );
		$this->t->setStartWeek();

		for( $ww = 1; $ww <= $weeksToShow; $ww++ ){
			$thisWeekView = array();

			for( $dd = 0; $dd <= 6; $dd++ ){
				$date = $this->t->formatDateDb();

				if( $disabledWeekdays ){
					$wkd = $this->t->getWeekday();
					if( in_array($wkd, $disabledWeekdays) ){
						$this->t->modify('+1 day');
						continue;
					}
				}

				$thisOn = FALSE;

				$label = $this->t->formatDate();

				$to = 'new';
				$toParams = $params;
				$toParams['start'] = $date;
				unset( $toParams['date'] );
				$to = array( $to, $toParams );

				$thisView = $this->ui->makeAhref( $to, $label )
					->tag('secondary')
					->tag('block')
					->tag('align', 'center')
					;

				$thisWeekView[] = $thisView;

				$this->t->setDateDb( $date );
				$this->t->modify('+1 day');
			}

			$thisWeekView = $this->ui->makeGrid( $thisWeekView );
			$out[] = $thisWeekView;

			$lastDate = $date;
		}

		$out = $this->ui->makeList( $out );

		$this->t->setDateDb( $lastDate );
		$this->t->modify('+1 day');
		$nextDate = $this->t->formatDateDb();

		$nextTo = 'ajax/new/date';
		$nextToParams = $params;
		$nextToParams['date'] = $nextDate;
		$nextTo = array( $nextTo, $nextToParams );

		$nextLink = $this->ui->makeAhref( $nextTo, '&darr;' )
			->tag('ajax')
			->tag('secondary')
			->tag('block')
			->tag('padding', 2)
			;

		$out = $this->ui->makeList( array($out, $nextLink) );
		return $out;
	}

	public function renderEndDate( $startDate )
	{
		$disabledWeekdays = $this->settings->get( 'skip_weekdays', TRUE );

		$params = $this->request->getParams();

		$shiftTypeId = $params['shifttype'];
		$calendarId = $params['calendar'];

		$shiftType = $this->shiftTypesQuery->findByid( $shiftTypeId );
		$calendar = $this->calendarsQuery->findByid( $calendarId );

		$minDays = $shiftType->getStart();
		$maxDays = $shiftType->getEnd();

		$weeksToShow = 4;

		$this->t->setDateDb( $startDate );

		if( array_key_exists('from', $params) ){
			$minDate = $params['from'];
		}
		else {
			$minDate = $this->t->setDateDb( $startDate )
				->modify( '+' . $minDays . ' days' )
				->modify('-1 day')
				->formatDateDb()
				;
		}

		$maxDate = $this->t->setDateDb( $startDate )
			->modify( '+' . $maxDays . ' days' )
			->modify('-1 day')
			->formatDateDb()
			;

		$out = array();
		$this->t->setDateDb( $minDate );
		$this->t->setStartWeek();

		for( $ww = 1; $ww <= $weeksToShow; $ww++ ){
			$thisWeekView = array();

			for( $dd = 0; $dd <= 6; $dd++ ){
				$date = $this->t->formatDateDb();

				if( $disabledWeekdays ){
					$wkd = $this->t->getWeekday();
					if( in_array($wkd, $disabledWeekdays) ){
						$this->t->modify('+1 day');
						continue;
					}
				}

				// $thisOn = ( $startDate == $date ) ? TRUE : FALSE;
				$thisOn = FALSE;

				$label = $this->t->formatDate();
				if( ($date >= $minDate) && ($date <= $maxDate) ){
					$dateString = HC3_Functions::glueArray( array($startDate, $date) );

					$to = 'new';
					$toParams = $params;
					$toParams['date'] = $dateString;
					$to = array( $to, $toParams );

					$thisView = $this->ui->makeAhref( $to, $label )
						->tag('secondary')
						->tag('block')
						->tag('align', 'center')
						;
				}
				else {
					if( $date == $startDate ){
						$arrow = '&rarr;';
						$label = $this->ui->makeListInline( array($label, $arrow) );
					}
					elseif( ($date < $startDate) OR ($date > $maxDate)){
						$label = $this->ui->makeBlock( $label )
							// ->tag('font-size', 2)
							->tag('muted', 3)
							;
					}
					$thisView = $this->ui->makeBlock( $label )
						->tag('border')
						->tag('padding', 1)
						->tag('block')
						->tag('align', 'center')
						// ->tag('border-color', 'gray')
						;
				}

				$thisWeekView[] = $thisView;

				$this->t->setDateDb( $date );
				$this->t->modify('+1 day');
			}

			$thisWeekView = $this->ui->makeGrid( $thisWeekView );
			$out[] = $thisWeekView;

			$lastDate = $date;
			if( $date >= $maxDate ){
				break;
			}
		}

		$out = $this->ui->makeList( $out );

		if( $lastDate < $maxDate ){
			$this->t->setDateDb( $lastDate );
			$this->t->modify('+1 day');
			$nextDate = $this->t->formatDateDb();

			$nextTo = 'ajax/new/date';
			$nextToParams = $params;
			$nextToParams['from'] = $nextDate;
			$nextTo = array( $nextTo, $nextToParams );

			$nextLink = $this->ui->makeAhref( $nextTo, '&darr;' )
				->tag('ajax')
				->tag('secondary')
				->tag('block')
				->tag('padding', 2)
				;

			$out = $this->ui->makeList( array($out, $nextLink) );
		}
		return $out;
	}

	public function buttons( $calendarId, $shiftTypeId, $employeeId = NULL )
	{
		$btn = $this->ui->makeInputSubmit( '__Continue__')
			->tag('primary')
			;

		$return = array('continue' => $btn);
		return $return;
	}
}