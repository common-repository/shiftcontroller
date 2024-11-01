<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
#[AllowDynamicProperties]
class SH4_Shifts_View_Zoom
{
	public function __construct( 
		HC3_Hooks $hooks,

		HC3_Ui $ui,
		HC3_Ui_Layout1 $layout,
		HC3_Request $request,

		SH4_App_Query $appQuery,
		SH4_Calendars_Presenter $calendarsPresenter,
		SH4_Employees_Presenter $employeesPresenter,
		SH4_Shifts_Presenter $shiftsPresenter,

		SH4_Shifts_View_Common $common,
		SH4_Shifts_Query $query,
		SH4_Shifts_Conflicts $conflicts
		)
	{
		$this->ui = $ui;
		$this->layout = $layout;
		$this->request = $request;

		$this->query = $hooks->wrap( $query );

		$this->calendarsPresenter = $hooks->wrap( $calendarsPresenter );
		$this->employeesPresenter = $hooks->wrap( $employeesPresenter );
		$this->shiftsPresenter = $hooks->wrap( $shiftsPresenter );

		$this->appQuery = $hooks->wrap( $appQuery );
		$this->conflicts = $hooks->wrap($conflicts);
		$this->common = $hooks->wrap($common);
		$this->self = $hooks->wrap($this);
	}

	public function render( $id )
	{
	// model
		$model = $this->query->findById($id);

		$rows = $this->self->rows( $model );

	// compile
		$out = array();

		foreach( $rows as $row ){
			if( is_array($row) ){
				$row = $this->ui->makeGrid()
					->add( $row['label'], 3, 12 )
					->add( $row['view'], 9, 12 )
					;
			}
			$out[] = $row;
		}

		$out = $this->ui->makeList( $out )
			->setStriped( TRUE )
			;

		$this->layout
			->setContent( $out )
			->setHeader( $this->self->header($model) )
			;

		if( ! $this->request->isAjax() ){
			$this->layout
				->setBreadcrumb( $this->common->breadcrumb($model) )
				;
		}

		$out = $this->layout->render();

		return $out;
	}

	public function rows( SH4_Shifts_Model $model = NULL )
	{
		$rows = array();
		if( ! $model ){
			return $rows;
		}

		$menu = $this->common->menu( $model );
		$icons = $this->common->icons( $model );

		$id = $model->getId();

	// find if we have alternative calendars
// temp disable
		// $calendars = $this->appQuery->findCalendarsForChange( $model );
		// if( $calendars ){
			// $menu['calendar']['change'] = array( 'shifts/' . $id . '/calendar', '__Change__' );
		// }

	// datetime
		$thisLabel = '__Date and Time__';

		if( $model->isMultiDay() ){
			$thisView = $this->shiftsPresenter->presentFullTime( $model );
			$thisView = $this->ui->makeBlock( $thisView )
				->tag('font-size', 4)
				;
		}
		else {
			$thisView = array();
			$dateView = $this->shiftsPresenter->presentDate( $model );

			$timeView = $this->shiftsPresenter->presentTime( $model );
			$rawTimeView = $this->shiftsPresenter->presentRawTime( $model );

			if( $rawTimeView != $timeView ){
				$timeView = $this->ui->makeBlock( $timeView )
					->tag('font-size', 4)
					;
				$rawTimeView = $this->ui->makeBlock( $rawTimeView )
					->tag('muted')
					;
				$timeView = $this->ui->makeListInline( array($timeView, $rawTimeView) )
					// ->gutter(0)
					;
			}
			else {
				$timeView = $this->ui->makeBlock( $timeView )
					->tag('font-size', 4)
					;
			}

		// if we have lunch timebreak
			$breakView = $this->shiftsPresenter->presentBreak( $model );
			if( $breakView ){
				$breakView = $this->ui->makeListInline( array('__Break__', $breakView) )
					->gutter(1)
				;
				$breakView = $this->ui->makeBlock( $breakView )
					->tag('muted')
					->tag('font-size', 2)
					// ->tag('font-style', 'line-through')
					// ->addAttr('title', '__Lunch Break__')
					;

				$timeView = $this->ui->makeListInline( array($timeView, $breakView) );
			}

			$thisView[] = $dateView;
			$thisView[] = $timeView;

			$thisView = $this->ui->makeList( $thisView )->gutter(0);
		}

		if( isset($icons['datetime']) ){
			$thisIcons = $this->ui->makeListInline( $icons['datetime'] )->gutter(1);
			$thisLabel = $this->ui->makeBlock( $this->ui->makeListInline(array($thisLabel, $thisIcons))->gutter(1) )
				->addAttr('style', 'position: relative;')
				;
		}

		if( isset($menu['datetime']) ){
			$thisMenu = $this->ui->helperActionsFromArray( $menu['datetime'] );
			$thisMenu = $this->ui->makeListInline( $thisMenu );
			unset( $menu['datetime'] );
			$thisView = $this->ui->makeList( array($thisView, $thisMenu) )
				->gutter(1)
				;
		}

		$thisRow = array(
			'label'	=> $thisLabel,
			'view'	=> $thisView
			);

		$rows[] = $thisRow;

	// calendar
		$calendar = $model->getCalendar();
		$thisLabel = '__Calendar__';
		$thisView = $this->calendarsPresenter->presentTitle( $calendar );
		if( isset($icons['calendar']) ){
			$thisIcons = $this->ui->makeListInline( $icons['calendar'] )->gutter(1);
			$thisLabel = $this->ui->makeBlock( $this->ui->makeListInline(array($thisLabel, $thisIcons))->gutter(1) )
				->addAttr('style', 'position: relative;')
				;
		}
		$thisView = $this->ui->makeBlock( $thisView )
			->tag('font-size', 4)
			;

		// $calendarDescription = $calendar->getDescription();
		$calendarDescription = $this->calendarsPresenter->presentDescription( $calendar );
		if( (null !== $calendarDescription) && strlen($calendarDescription) ){
			$thisView = $this->ui->makeList( array($thisView, $calendarDescription) )
				->gutter(2)
				;
		}

		if( isset($menu['calendar']) ){
			$thisMenu = $this->ui->helperActionsFromArray( $menu['calendar'] );
			$thisMenu = $this->ui->makeListInline($thisMenu);
			unset( $menu['calendar'] );
			$thisView = $this->ui->makeList( array($thisView, $thisMenu) )
				->gutter(1)
				;
		}
		$thisRow = array(
			'label'	=> $thisLabel,
			'view'	=> $thisView
			);
		$rows[] = $thisRow;

	// employee
		$employee = $model->getEmployee();
		$thisLabel = '__Employee__';
		$thisView = $this->employeesPresenter->presentTitle( $employee );

		if( isset($icons['employee']) ){
			$thisIcons = $this->ui->makeListInline( $icons['employee'] )->gutter(1);
			$thisLabel = $this->ui->makeBlock( $this->ui->makeListInline(array($thisLabel, $thisIcons))->gutter(1) )
				->addAttr('style', 'position: relative;')
				;
		}

		$thisView = $this->ui->makeBlock( $thisView )
			->tag('font-size', 4)
			;

		$employeeDescription = $this->employeesPresenter->presentDescription( $employee );
		if( (null !== $employeeDescription) && strlen($employeeDescription) ){
			$thisView = $this->ui->makeList( array($thisView, $employeeDescription) )
				->gutter(2)
				;
		}

		if( isset($menu['employee']) ){
			$thisMenu = $this->ui->helperActionsFromArray( $menu['employee'] );

			$thisMenu = $this->ui->makeListInline($thisMenu)->gutter(1);
			unset( $menu['employee'] );
			$thisView = $this->ui->makeList( array($thisView, $thisMenu) )
				->gutter(1)
				;
		}
		$thisRow = array(
			'label'	=> $thisLabel,
			'view'	=> $thisView
			);
		$rows[] = $thisRow;

	// status
		$thisLabel = '__Status__';
		$thisView = $this->shiftsPresenter->presentStatus( $model );
		if( isset($icons['status']) ){
			$thisIcons = $this->ui->makeListInline( $icons['status'] )->gutter(1);
			$thisLabel = $this->ui->makeBlock( $this->ui->makeListInline(array($thisLabel, $thisIcons))->gutter(1) )
				->addAttr('style', 'position: relative;')
				;
		}

		$thisView = $this->ui->makeBlock( $thisView )
			->tag('font-size', 4)
			;

		if( isset($menu['status']) ){
			$thisMenu = $this->ui->helperActionsFromArray( $menu['status'] );
			$thisMenu = $this->ui->makeListInline($thisMenu);
			unset( $menu['status'] );

			$thisView = $this->ui->makeList( array($thisView, $thisMenu) )
				->gutter(1)
				;
		}

		$thisRow = array(
			'label'		=> $thisLabel,
			'view'		=> $thisView,
			);
		$rows[] = $thisRow;

		return $rows;
	}

	public function header( SH4_Shifts_Model $shift )
	{
		$out = array();
		$out[] = '__Shift__';
		$out[] = '#' . $shift->getId();

		$out = join( ' ', $out );

		return $out;
	}
}