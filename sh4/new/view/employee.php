<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
#[AllowDynamicProperties]
class SH4_New_View_Employee
{
	public function __construct(
		HC3_Hooks $hooks,

		HC3_Request $request,
		HC3_Ui $ui,
		HC3_Ui_Layout1 $layout,
		HC3_Settings $settings,

		SH4_New_View_Common $common,

		SH4_Shifts_Conflicts $conflicts,
		SH4_Shifts_Availability $availability,

		SH4_Calendars_Query $calendars,
		SH4_Employees_Query $employees,

		SH4_ShiftTypes_Query $shiftTypesQuery,
		SH4_Employees_Presenter $employeesPresenter,
		SH4_App_Query $appQuery,
		SH4_New_Query $newQuery,

		HC3_Time $t
		)
	{
		$this->self = $hooks->wrap( $this );

		$this->request = $request;
		$this->ui = $ui;
		$this->layout = $layout;
		$this->common = $hooks->wrap($common);

		$this->t = $t;

		$this->settings = $hooks->wrap( $settings );

		$this->conflicts = $hooks->wrap($conflicts);
		$this->availability = $hooks->wrap( $availability );

		$this->calendars = $hooks->wrap($calendars);
		$this->employees = $hooks->wrap($employees);

		$this->shiftTypesQuery = $hooks->wrap( $shiftTypesQuery );
		$this->employeesPresenter = $hooks->wrap( $employeesPresenter );
		$this->appQuery = $hooks->wrap( $appQuery );
		$this->newQuery = $hooks->wrap( $newQuery );
	}

	public function render()
	{
		$params = $this->request->getParams();

		$dateString = $params['date'];
		$shiftTypeId = $params['shifttype'];
		$calendarId = $params['calendar'];

		$dates = HC3_Functions::unglueArray( $dateString );
		$datesView = array();
		foreach( $dates as $date ){
			$this->t->setDateDb( $date );
			$dateView = $this->t->getWeekdayName() . ', ' . $this->t->formatDate();
			$datesView[] = $dateView;
		}

		$shiftType = $this->shiftTypesQuery->findById( $shiftTypeId );
		$range = $shiftType->getRange();

		switch( $range ){
			case SH4_ShiftTypes_Model::RANGE_DAYS:
				$datesView = $this->ui->makeListInline( $datesView )
					->separated( '&rarr;' )
					;
				$datesView = array( $datesView );
				break;
		}

	// employees
		$calendars = $this->newQuery->findAllCalendars();
		if( array_key_exists($calendarId, $calendars) ){
			$calendar = $calendars[$calendarId];
		}
		else {
			$calendar = NULL;
		}

		if( ! $calendar ){
			return;
		}

		$isAvailability = $calendar->isAvailability();

		$employees = $this->newQuery->findAllEmployees();
		if( $calendar ){
			$filter = $this->appQuery->findEmployeesForCalendar( $calendar );

			$filterIds = array_keys( $filter );
			$ids = array_keys( $employees );
			foreach( $ids as $id ){
				if( ! in_array($id, $filterIds) ){
					unset( $employees[$id] );
				}
			}
		}

		if( ! $employees ){
			return;
		}

		if( count($employees) == 1 ){
			$params = $this->request->getParams();
			$employee = current( $employees );
			$employeeId = $employee->getId();

			$to = 'new';
			$toParams = array(
				'calendar'	=> $calendarId,
				'shifttype'	=> $shiftTypeId,
				'date'		=> $dateString,
				'employee'	=> $employeeId
				);
			$to = array( $to, $toParams );

			$return = array( $to, NULL );
			return $return;
		}

		$employeesView = array();
		$employeesWithConflictsView = array();

		foreach( $employees as $employee ){
			$withConflicts = 0;
			$withAvailability = 0;
			$withNothing = 0;

			$employeeId = $employee->getId();
			$thisView = array();

			if( $employeeId ){
				$label = $this->employeesPresenter->presentTitle( $employee );
				$input = $this->ui->makeInputCheckbox( 'employee[]', $label, $employeeId );
				$thisView[] = $input;
			}
		// open shift
			else {
				$label = $this->employeesPresenter->presentTitle( $employee );
				$options = array();
				$options[0] = $label;
				for( $ii = 1; $ii <= 50; $ii++ ){
					$options[ '0' . SH4_NEW_OPEN_SEPARATOR . $ii ] = $ii;
				}
				$input = $this->ui->makeInputSelect( 'employee[]', NULL, $options );
				$thisView[] = $input;
			}

		// check conflicts
			if( $employeeId && (! $isAvailability) ){
				$testModels = array();

				$range = $shiftType->getRange();
				switch( $range ){
					case SH4_ShiftTypes_Model::RANGE_DAYS:
						$start = $this->t->setDateDb( $dates[0] )
							->formatDateTimeDb()
							;
						$end = $this->t->setDateDb( $dates[1] )
							->modify('+1 day')
							->formatDateTimeDb()
							;

						$testModel = new SH4_Shifts_Model( NULL, $calendar, $start, $end, $employee );
						$testModels[] = $testModel;
						break;

					case SH4_ShiftTypes_Model::RANGE_HOURS:
						$timeStart = $shiftType->getStart();
						$timeEnd = $shiftType->getEnd();
						reset( $dates );
						foreach( $dates as $date ){
							$start = $this->t->setDateDb( $date )
								->modify( '+' . $timeStart . ' seconds' )
								->formatDateTimeDb()
								;
							$end = $this->t->setDateDb( $date )
								->modify( '+' . $timeEnd . ' seconds' )
								->formatDateTimeDb()
								;
							$testModel = new SH4_Shifts_Model( NULL, $calendar, $start, $end, $employee );
							$testModels[] = $testModel;
						}
						break;
				}

				reset( $testModels );
				foreach( $testModels as $testModel ){
					$conflicts = $this->conflicts->get( $testModel );
					if( $conflicts ){
						$withConflicts++;
					}
					else {
						if( (! $this->availability->hasAvailability()) OR $this->availability->get($testModel) ){
							$withAvailability++;
						}
						else {
							$withNothing++;
						}
					}
				}

				$conflictsView = array();
				if( $withAvailability ){
					$sign = ( count($testModels) < 2 ) ? '&check;' : $withAvailability;
					$sign = $this->ui->makeBlock( $sign )
						->tag('padding', 'x1')
						->tag('color', 'white')
						->tag('bgcolor', 'olive')
						->addAttr( 'title', '__Available__' )
						;
					$conflictsView[] = $sign;
				}

				if( $withNothing ){
					$sign = ( count($testModels) < 2 ) ? '&check;' : $withNothing;
					$sign = $this->ui->makeBlock( $sign )
						->tag('padding', 'x1')
						->tag('color', 'white')
						->tag('bgcolor', 'gray')
						->addAttr( 'title', '__No Availability__' )
						;
					$conflictsView[] = $sign;
				}

				if( $withConflicts ){
					$sign = (count($testModels) < 2) ? '&nbsp;!&nbsp;' : $withConflicts;
					$sign = $this->ui->makeBlock( $sign )
						->tag('padding', 'x1')
						->tag('color', 'white')
						->tag('bgcolor', 'maroon')
						->addAttr( 'title', '__Conflicts__' )
						;
					$conflictsView[] = $sign;
				}

				$conflictsView = $this->ui->makeListInline( $conflictsView )
					->gutter(1)
					;
				$thisView[] = $conflictsView;
			}

			$thisView = $this->ui->makeListInline( $thisView );

			if( $employeeId ){
				$descriptionView = $employee->getDescription();
				if( strlen($descriptionView) ){
					$thisView = $this->ui->makeList( array($thisView, $descriptionView) );
				}
			}

			$thisView = $this->ui->makeBlock( $thisView )
				->addAttr('style', 'position: relative;')
				;

			$thisView = $this->ui->makeBlock( $thisView )
				->tag('border')
				->tag('padding', 2)
				;

			if( $withAvailability == count($dates) ){
				$thisView
					->tag('border-color', 'green')
					;
			}
			elseif( $withConflicts ){
				$thisView
					->tag('border-color', 'red')
					;
			}
			else {
				$thisView
					->tag('border-color', 'gray')
					;
			}

			if( $withConflicts ){
				$employeesWithConflictsView[] = $thisView;
			}
			else {
				$employeesView[] = $thisView;
			}
		}

		// $employeesView = $this->ui->makeListInline( $employeesView );
		// $employeesView = $this->ui->makeList( $employeesView );
		$employeesView = $this->ui->makeGrid( $employeesView, 2 );

		$toggleAllLabel = '__Select All__';
		$toggleAll = $this->ui->makeInputCheckbox( 'toggle', $toggleAllLabel, 1 )
			->addAttr( 'class', 'sh4-new-employee-toggler' )
			;

		$employeesView = $this->ui->makeList( array($toggleAll, $employeesView) );

		$js = <<<EOT

<script language="JavaScript">
jQuery('.sh4-new-employee-container').on( 'change', '.sh4-new-employee-toggler', function(event){
	var \$checkers = jQuery(this).closest('.sh4-new-employee-container').find('input[type="checkbox"]');
	var meOn = jQuery(this).prop('checked');
	\$checkers.each( function(){
		var \$checker = jQuery(this);
		if ( ! \$checker.hasClass('sh4-new-employee-toggler') ){
			// \$checker.prop( 'checked', ! \$checker.prop('checked') );
			\$checker.prop( 'checked', meOn );
		}
	});
	return false;
});
</script>

EOT;

		$employeesView = $this->ui->makeCollection( array($js, $employeesView) );
		$employeesView = $this->ui->makeBlock( $employeesView )
			->addAttr( 'class', 'sh4-new-employee-container' )
			;

		$out = array();
		$out[] = $employeesView;

		if( $employeesWithConflictsView ){
			// $employeesWithConflictsView = $this->ui->makeListInline( $employeesWithConflictsView );
			$employeesWithConflictsView = $this->ui->makeGrid( $employeesWithConflictsView, 2 );
			$employeesWithConflictsView = $this->ui->makeCollapse( '__Unavailable Employees__', $employeesWithConflictsView );
			$out[] = $employeesWithConflictsView;
		}

		$btn = $this->self->buttons( $calendarId, $shiftTypeId );
		$btn = $this->ui->makeListInline( $btn );

	// open shift
		// $inputs = $this->common->inputs( $calendarId, $shiftTypeId, $employeeId );
		// $out = array_merge( $out, $inputs );

		$out[] = $btn;

		$out = $this->ui->makeList( $out )->gutter(3);

		$to = 'new/employee';
		$toParams = $params;
		$to = array( $to, $toParams );

		$out = $this->ui->makeForm( $to, $out );

		$breadcrumb = array();
		$breadcrumb[] = $this->common->breadcrumb($calendarId, $shiftTypeId);
		$breadcrumb[] = $datesView;

		$this->layout
			->setContent( $out )
			->setBreadcrumb( $breadcrumb, TRUE )
			->setHeader( $this->self->header() )
			;
		$out = $this->layout->render();

		return $out;
	}

	public function buttons( $calendarId, $shiftTypeId )
	{
		$btn = $this->ui->makeInputSubmit( '__Continue__')
			->tag('primary')
			;

		$return = array( 'continue' => $btn );
		return $return;
	}

	public function header()
	{
		$out = '__Employee__';
		return $out;
	}
}