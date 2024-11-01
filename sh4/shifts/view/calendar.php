<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
#[AllowDynamicProperties]
class SH4_Shifts_View_Calendar
{
	public function __construct(
		HC3_Hooks $hooks,

		HC3_Ui $ui,
		HC3_Ui_Layout1 $layout,

		SH4_Shifts_View_Common $common,
		SH4_Shifts_Query $shiftsQuery,

		SH4_Calendars_Presenter	$calendarsPresenter,
		SH4_Employees_Query $employees,
		SH4_App_Query $appQuery,

		SH4_Shifts_Availability $availability,
		SH4_Shifts_Conflicts $conflicts,
		SH4_Shifts_View_Zoom $viewZoom
		)
	{
		$this->self = $hooks->wrap( $this );
		$this->ui = $ui;
		$this->layout = $layout;

		$this->calendarsPresenter = $hooks->wrap( $calendarsPresenter );
		$this->shiftsQuery = $hooks->wrap( $shiftsQuery );
		$this->common = $hooks->wrap( $common );

		$this->employees = $hooks->wrap($employees);
		$this->appQuery = $hooks->wrap( $appQuery );
		$this->conflicts = $hooks->wrap($conflicts);
	}

	public function render( $shiftId )
	{
		$model = $this->shiftsQuery->findById( $shiftId );

		$employee = $model->getEmployee();
		$employeeId = $employee->getId();

		$calendar = $model->getCalendar();
		$calendarId = $calendar->getId();

		$calendars = $this->appQuery->findCalendarsForChange( $model );

		$calendarsView = array();
		$calendarsWithConflictsView = array();

		foreach( $calendars as $calendar ){
			$thisCalendarId = $calendar->getId();

			$withConflicts = 0;

			$id = $model->getId();
			$start = $model->getStart();
			$end = $model->getEnd();

			$testModel = new SH4_Shifts_Model( $shiftId, $calendar, $start, $end, $employee );

			$conflicts = $this->conflicts->get($testModel);
			if( $conflicts ){
				$withConflicts++;
			}

			$thisView = array();
			$calendarView = $this->calendarsPresenter->presentTitle( $calendar );
			$calendarView = $this->ui->makeSpan( $calendarView )
				->tag('font-size', 4)
				->tag('font-style', 'bold')
				;

			$descriptionView = $calendar->getDescription();
			if( strlen($descriptionView) ){
				$descriptionView = $this->ui->makeLongText( $descriptionView );
				$calendarView = $this->ui->makeList( array($calendarView, $descriptionView) )
					->gutter(1)
					;
			}

			$thisView[] = $calendarView;

			$conflictsView = array();

			if( $withConflicts ){
				$sign = '&nbsp;!&nbsp;';
				$sign = $this->ui->makeBlock( $sign )
					->tag('padding', 'x1')
					->tag('color', 'white')
					->tag('bgcolor', 'maroon')
					->addAttr( 'title', '__Conflicts__' )
					;

				$conflictsLink = array('conflicts', $shiftId, $thisCalendarId, $start, $end, $employeeId );
				$conflictsLink = join('/', $conflictsLink);

				$sign = $this->ui->makeAhref( $conflictsLink, $sign )
					->newWindow()
					;

				$conflictsView[] = $sign;
			}

			$conflictsView = $this->ui->makeListInline( $conflictsView )
				->gutter(1)
				;
			$thisView[] = $conflictsView;

			$thisView = $this->ui->makeBlock( $this->ui->makeListInline($thisView) )
				->addAttr('style', 'position: relative;')
				;

			$btn = $this->ui->makeInputSubmit('__Select__')
				->tag('secondary')
				;
			if( $withConflicts ){
				$btn
					->tag('confirm')
					;
			}

			$to = array( 'shifts', $shiftId, 'calendar', $thisCalendarId );
			$to = join('/', $to);

			$form = $this->ui->makeForm( $to, $btn );

			$thisView = $this->ui->makeList( array($thisView, $form) )->gutter(2);

			$thisView = $this->ui->makeBlock( $thisView )
				->tag('border')
				->tag('padding', 2)
				;

			if( $withConflicts ){
				$thisView
					->tag('border-color', 'darkred')
					;
				}
			else {
				$thisView
					->tag('border-color', 'green')
					;
			}

			$calendarsView[] = $thisView;
		}

		if( $calendarsView OR $calendarsWithConflictsView ){

			$out = $this->ui->makeGrid();
			foreach( $calendarsView as $ev ){
				$out->add( $ev, 3, 12 );
			}
		}
		else {
			$out = array();
			$out[] = '__No Available Calendars__';
			$out = $this->ui->makeList( $out );
		}

		$this->layout
			->setContent( $out )
			->setHeader( $this->self->header($model) )
			;

		$breadcrumb = $this->common->breadcrumb($model);
		$breadcrumbMultiline = FALSE;

		$this->layout
			->setBreadcrumb( $breadcrumb, $breadcrumbMultiline )
			;

		$out = $this->layout->render();
		return $out;
	}

	public function header( SH4_Shifts_Model $model )
	{
		$out = '__Change Calendar__';
		return $out;
	}
}