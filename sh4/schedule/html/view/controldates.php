<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
#[AllowDynamicProperties]
class SH4_Schedule_Html_View_ControlDates
{
	public function __construct(
		HC3_Hooks $hooks,
		HC3_Ui $ui,
		HC3_Request $request,
		HC3_Time $t,
		HC3_Settings $settings
		)
	{
		$this->self = $hooks->wrap( $this );

		$this->ui = $ui;
		$this->request = $request;
		$this->t = $t;

		$this->settings = $hooks->wrap( $settings );
	}

	public function render()
	{
		$out = NULL;

		$params = $this->request->getParams();
		$type = $params['type'];

		switch( $type ){
			case 'month':
				$out = $this->self->renderMonth();
				break;
			case '4weeks':
				$out = $this->self->renderFourWeeks();
				break;
			case '2weeks':
				$out = $this->self->renderTwoWeeks();
				break;
			case 'week':
				$out = $this->self->renderWeek();
				break;
			case 'day':
				$out = $this->self->renderDay();
				break;
			default:
				$out = $this->self->renderCustom();
				break;
		}

		return $out;
	}

	public function renderCustom()
	{
		$slug = $this->request->getRealSlug();
		$params = $this->request->getParams();
		if( isset($params['time']) && ('now' == $params['time']) ){
			$params['time'] = $this->t->setNow()->formatDateTimeDb();
		}

	// date selection
		$timeInDay = 0;
		$exactStartDate = NULL;
		if( array_key_exists('time', $params) ){
			$time = $params['time'];
			$startDate = $this->t->setDateTimeDb( $params['time'] )->formatDateDb();
			$timeInDay = $this->t->getTimeInDay();
			$exactStartDate = $startDate;
		}
		elseif( array_key_exists('start', $params) ){
			$startDate = $params['start'];
		}
		else {
			$startDate = $this->t->setNow()->setStartDay()->formatDateDb();
		}

		if( 'start-week' == $startDate ){
			$this->t->setNow()->setStartWeek();
			$startDate = $this->t->formatDateDb();
		}
		elseif( 'start-month' == $startDate ){
			$this->t->setNow()->setStartMonth();
			$startDate = $this->t->formatDateDb();
		}
		elseif( 'start-year' == $startDate ){
			$this->t->setNow()->setStartYear();
			$startDate = $this->t->formatDateDb();
		}
		else {
			if( (FALSE !== strpos($startDate, '+')) OR (FALSE !== strpos($startDate, '-')) ){
				$this->t->setNow()->setStartDay()->modify($startDate);
				$startDate = $this->t->formatDateDb();
			}
		}

		if( ! $timeInDay ){
			$timeInDay = $this->t->setNow()->getTimeInDay();
			$exactStartDate = $this->t->setNow()->formatDateDb();
		}

		if( array_key_exists('time', $params) ){
			$endDate = $startDate;
		}
		elseif( array_key_exists('end', $params) ){
			$endDate = $params['end'];
		}
		else {
			$endDate = $this->t->modify('+1 week')->modify('-1 day')->formatDateDb();
		}

		if( FALSE !== strpos($endDate, '+') ){
			$this->t->setDateDb($startDate)->modify($endDate)->modify('-1 day')->setEndDay();
			$endDate = $this->t->formatDateDb();
		}

		if( isset($params['time']) ){
			$this->t->setDateTimeDb( $params['time'] );
			$label = $this->t->formatDateWithWeekday() . ' ' . $this->t->formatTime();
		}
		else {
			$label = $this->t->formatDateRange( $startDate, $endDate );
		}

		$label = $this->ui->makeBlock( $label )
			->tag('font-size', 5)
			;

		$hideui = isset( $params['hideui'] ) ? $params['hideui'] : array();
		if( ! array_intersect(array('date-nav', 'all'), $hideui) ){
			$quickJumpForm = $this->ui->makeForm(
				$slug . '/dates',
				$this->ui->makeListInline()
					->add( $this->ui->makeInputDatepicker( 'start', NULL, $startDate ) )
					->add( '-' )
					->add( $this->ui->makeInputDatepicker( 'end', NULL, $endDate ) )
					->add( $this->ui->makeInputSubmit( '&rarr;')->tag('primary') )
				);

			$exactTimeForm = $this->ui->makeForm(
				$slug . '/exacttime',
				$this->ui->makeListInline()
					->add( $this->ui->makeInputDatepicker( 'date', NULL, $exactStartDate ) )
					->add( $this->ui->makeInputTime( 'time', NULL, $timeInDay ) )
					->add( $this->ui->makeInputSubmit( '&rarr;')->tag('primary') )
				);

			$quickJumpForm = $this->ui->makeList( array('__Date Range__', $quickJumpForm, '__Exact Time__', $exactTimeForm) );

			$quickJumpForm = $this->ui->makeCollapse( $label, $quickJumpForm );
			$out = $quickJumpForm;
		}
		else {
			$out = $label;
		}

		return $out;
	}

	public function renderWeek()
	{
		$slug = $this->request->getRealSlug();

		$params = $this->request->getParams('withoutDefault');
		$params['end'] = NULL;

		$startDate = $params['start'];

		$this->t->setDateDb($startDate)->setStartWeek();
		$next = $this->t->modify('+1 week')->formatDateDb();
		$prev = $this->t->modify('-2 weeks')->formatDateDb();

		$thisParams = $params;
		$thisParams['start'] = $next;
		$nextLink = $this->ui->makeAhref( array($slug, $thisParams), '__Next__' . '&nbsp;&raquo;' )
			->tag('secondary')
			->tag('block')
			->tag('align', 'center')
			;

		$thisParams = $params;
		$thisParams['start'] = $prev;
		$prevLink = $this->ui->makeAhref( array($slug, $thisParams), '&laquo;&nbsp;' . '__Previous__' )
			->tag('secondary')
			->tag('block')
			->tag('align', 'center')
			;

	// label
		$this->t->setDateDb( $startDate );
		$this->t->modify('+1 week')->modify('-1 day');
		$endDate = $this->t->formatDateDb();
		$label = $this->t->formatDateRange( $startDate, $endDate );

		$weekNo = $this->t->getWeekNo();
		$label .= ' [' . '__Week__' . ' #' . $weekNo . ']';
		$out = $this->self->finalizeLabel( $label, $startDate, $prevLink, $nextLink );

		return $out;
	}

	public function renderDay()
	{
		$slug = $this->request->getRealSlug();
		$params = $this->request->getParams('withoutDefault');

		$params['end'] = NULL;

		$startDate = $params['start'];

		$this->t->setDateDb($startDate);

		$daysToShow = isset( SH4_Schedule_Html_View_Day::$daysToShow ) ? SH4_Schedule_Html_View_Day::$daysToShow : 7;

		$next = $this->t->modify( '+' . $daysToShow . ' days' )->formatDateDb();
		$prev = $this->t->modify( '-' . 2 * $daysToShow . ' days' )->formatDateDb();

		$thisParams = $params;
		$thisParams['start'] = $next;
		$nextLink = $this->ui->makeAhref( array($slug, $thisParams), '__Next__' . '&nbsp;&raquo;' )
			->tag('secondary')
			->tag('block')
			->tag('align', 'center')
			;

		$thisParams = $params;
		$thisParams['start'] = $prev;
		$prevLink = $this->ui->makeAhref( array($slug, $thisParams), '&laquo;&nbsp;' . '__Previous__' )
			->tag('secondary')
			->tag('block')
			->tag('align', 'center')
			;

	// label
		// $this->t->setDateDb( $startDate );
		// $label = $this->t->getWeekdayName() . ', ' . $this->t->formatDate();

		$this->t->setDateDb( $startDate );
		$this->t->modify( '+' . $daysToShow . ' days' )->modify('-1 day');

		$endDate = $this->t->formatDateDb();
		$label = $this->t->formatDateRange( $startDate, $endDate );
		$out = $this->self->finalizeLabel( $label, $startDate, $prevLink, $nextLink );

		return $out;
	}

	public function renderMonth()
	{
		$slug = $this->request->getRealSlug();
		$params = $this->request->getParams('withoutDefault');

		$params['end'] = NULL;

		$startDate = $params['start'];

		$this->t->setDateDb($startDate)->setStartMonth();
		$next = $this->t->modify('+1 month')->formatDateDb();
		$prev = $this->t->modify('-2 months')->formatDateDb();

		$thisParams = $params;
		$thisParams['start'] = $next;
		$nextLink = $this->ui->makeAhref( array($slug, $thisParams), '__Next__' . '&nbsp;&raquo;' )
			->tag('secondary')
			->tag('block')
			->tag('align', 'center')
			;

		$thisParams = $params;
		$thisParams['start'] = $prev;
		$prevLink = $this->ui->makeAhref( array($slug, $thisParams), '&laquo;&nbsp;' . '__Previous__' )
			->tag('secondary')
			->tag('block')
			->tag('align', 'center')
			;

	// label

		$this->t->setDateDb( $startDate );
		$label = array();
		$label[] = $this->t->getMonthName();
		$label[] = $this->t->getYear();
		$label = join(' ', $label);
		$out = $this->self->finalizeLabel( $label, $startDate, $prevLink, $nextLink );

		return $out;
	}

	public function renderFourWeeks()
	{
		$slug = $this->request->getRealSlug();
		$params = $this->request->getParams('withoutDefault');
		$nWeeks = $this->settings->get( 'datetime_n_weeks' );
		if( ! $nWeeks ) $nWeeks = 4;

		$params['end'] = NULL;

		$startDate = $params['start'];
		$endDate = $this->t->setDateDb($startDate)->setStartWeek()->modify('+' . $nWeeks  . ' weeks')->modify('-1 day')->formatDateDb();

		$this->t->setDateDb($startDate)->setStartWeek();
		$next = $this->t->modify('+' . $nWeeks . ' weeks')->formatDateDb();
		$prev = $this->t->modify('-' . 2 * $nWeeks . ' weeks')->formatDateDb();

		$thisParams = $params;
		$thisParams['start'] = $next;
		$nextLink = $this->ui->makeAhref( array($slug, $thisParams), '__Next__' . '&nbsp;&raquo;' )
			->tag('secondary')
			->tag('block')
			->tag('align', 'center')
			;

		$thisParams = $params;
		$thisParams['start'] = $prev;
		$prevLink = $this->ui->makeAhref( array($slug, $thisParams), '&laquo;&nbsp;' . '__Previous__' )
			->tag('secondary')
			->tag('block')
			->tag('align', 'center')
			;

	// label
		$this->t->setDateDb( $startDate );
		$label = $this->t->formatDateRange( $startDate, $endDate );
		$out = $this->self->finalizeLabel( $label, $startDate, $prevLink, $nextLink );

		return $out;
	}

	public function renderTwoWeeks()
	{
		$slug = $this->request->getRealSlug();
		$params = $this->request->getParams('withoutDefault');

		$params['end'] = NULL;

		$startDate = $params['start'];
		$endDate = $this->t->setDateDb($startDate)->setStartWeek()->modify('+2 weeks')->modify('-1 day')->formatDateDb();

		$this->t->setDateDb($startDate)->setStartWeek();
		$next = $this->t->modify('+2 weeks')->formatDateDb();
		$prev = $this->t->modify('-4 weeks')->formatDateDb();

		$thisParams = $params;
		$thisParams['start'] = $next;
		$nextLink = $this->ui->makeAhref( array($slug, $thisParams), '__Next__' . '&nbsp;&raquo;' )
			->tag('secondary')
			->tag('block')
			->tag('align', 'center')
			;

		$thisParams = $params;
		$thisParams['start'] = $prev;
		$prevLink = $this->ui->makeAhref( array($slug, $thisParams), '&laquo;&nbsp;' . '__Previous__' )
			->tag('secondary')
			->tag('block')
			->tag('align', 'center')
			;

	// label
		$this->t->setDateDb( $startDate );
		$label = $this->t->formatDateRange( $startDate, $endDate );
		$out = $this->self->finalizeLabel( $label, $startDate, $prevLink, $nextLink );

		return $out;
	}

	public function finalizeLabel( $label, $startDate, $prevLink, $nextLink )
	{
		$params = $this->request->getParams();
		$slug = $this->request->getRealSlug();

		$label = $this->ui->makeBlock( $label )
			->tag('font-size', 5)
			;

		$hideui = isset( $params['hideui'] ) ? $params['hideui'] : array();
		if( ! array_intersect(array('date-nav', 'all'), $hideui) ){
			$quickJumpForm = $this->ui->makeForm(
				$slug . '/dates',
				$this->ui->makeListInline()
					->add( $this->ui->makeInputDatepicker( 'start', NULL, $startDate ) )
					->add(
						$this->ui->makeInputSubmit( '&rarr;' )
							->tag('primary')
							->tag('block')
						)
					->gutter(1)
				);

			$quickJumpForm = $this->ui->makeCollapse( $label, $quickJumpForm );
			$controls = $this->ui->makeGrid( array($prevLink, $nextLink) );

			$ret = $this->ui->makeGrid()
				->add( $quickJumpForm, 8, 12 )
				->add( $controls, 4, 12 )
				;
		}
		else {
			$ret = $label;
		}

		return $ret;
	}
}