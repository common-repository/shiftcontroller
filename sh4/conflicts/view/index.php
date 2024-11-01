<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
#[AllowDynamicProperties]
class SH4_Conflicts_View_Index
{
	public function __construct(
		HC3_Hooks $hooks,

		HC3_Ui $ui,
		HC3_Ui_Layout1 $layout,

		SH4_Calendars_Query $calendars,
		SH4_Employees_Query $employees,

		SH4_Shifts_View_Widget $widget,
		SH4_Shifts_Presenter $presenter,

		SH4_Shifts_Query $shiftsQuery,
		SH4_Shifts_Conflicts $conflicts
		)
	{
		$this->ui = $ui;
		$this->layout = $layout;

		$this->calendars = $hooks->wrap($calendars);
		$this->employees = $hooks->wrap($employees);
		$this->shiftsQuery = $hooks->wrap($shiftsQuery);

		$this->conflicts = $hooks->wrap( $conflicts );

		$this->widget = $hooks->wrap($widget);
		$this->presenter = $hooks->wrap($presenter);
		$this->self = $hooks->wrap($this);
	}

	public function render( $shiftId, $calendarId, $start, $end, $employeeId )
	{
		$calendar = $this->calendars->findById( $calendarId );
		$employee = $this->employees->findById( $employeeId );

	// test model
		$testModel = new SH4_Shifts_Model( NULL, $calendar, $start, $end, $employee );

		$out = array();

	// conflicts view
		$conflicts = $this->conflicts->get( $testModel );
		if( $conflicts ){
			$conflictsView = array();
			foreach( $conflicts as $conflict ){
				$conflictsView[] = $conflict->render();
			}
			$conflictsView = $this->ui->makeList($conflictsView);
			$out[] = $conflictsView;
		}

		$out = $this->ui->makeList($out);

		$this->layout
			->setContent( $out )
			->setBreadcrumb( $this->breadcrumb($shiftId, $calendarId, $start, $end, $employeeId) )
			->setHeader( $this->self->header() )
			;

		$out = $this->layout->render();

		return $out;
	}

	public function header()
	{
		$out = '__Conflicts__';
		return $out;
	}

	public function breadcrumb( $shiftId, $calendarId, $start, $end, $employeeId )
	{
		$return = array();

		$calendar = $this->calendars->findById( $calendarId );
		$employee = $this->employees->findById( $employeeId );

		$testModel = new SH4_Shifts_Model( NULL, $calendar, $start, $end, $employee );

		$label = $this->presenter->presentTitle( $testModel );
		if( $shiftId ){
			$label = array( 'shifts/' . $shiftId , $label );
		}
		$return['new'] = $label;

		return $return;
	}
}