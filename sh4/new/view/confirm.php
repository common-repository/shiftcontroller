<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
#[AllowDynamicProperties]
class SH4_New_View_Confirm
{
	public function __construct(
		HC3_Hooks $hooks,

		HC3_Auth $auth,
		HC3_IPermission $permission,
		SH4_Shifts_Conflicts $conflicts,

		HC3_Request $request,
		HC3_Ui $ui,
		HC3_Ui_Layout1 $layout,
		HC3_Settings $settings,

		SH4_New_View_Common $common,

		SH4_Shifts_Availability $availability,

		SH4_Calendars_Query $calendars,
		SH4_Employees_Query $employees,
		SH4_ShiftTypes_Query $shiftTypesQuery,

		SH4_Employees_Presenter $employeesPresenter,
		SH4_Calendars_Presenter $calendarsPresenter,
		SH4_ShiftTypes_Presenter $shiftTypesPresenter,

		SH4_Calendars_Permissions $calendarsPermissions,

		SH4_App_Query $appQuery,
		SH4_New_Query $newQuery,

		HC3_Time $t
		)
	{
		$this->self = $hooks->wrap( $this );

		$this->auth = $hooks->wrap( $auth );
		$this->permission = $hooks->wrap( $permission );
		$this->request = $request;
		$this->ui = $ui;
		$this->layout = $layout;
		$this->common = $hooks->wrap($common);

		$this->calendarsPermissions = $hooks->wrap( $calendarsPermissions );
		$this->t = $t;

		$this->settings = $hooks->wrap( $settings );

		$this->conflicts = $hooks->wrap($conflicts);
		$this->availability = $hooks->wrap( $availability );

		$this->calendars = $hooks->wrap($calendars);
		$this->employees = $hooks->wrap($employees);
		$this->shiftTypesQuery = $hooks->wrap( $shiftTypesQuery );

		$this->employeesPresenter = $hooks->wrap( $employeesPresenter );
		$this->calendarsPresenter = $hooks->wrap( $calendarsPresenter );
		$this->shiftTypesPresenter = $hooks->wrap( $shiftTypesPresenter );

		$this->appQuery = $hooks->wrap( $appQuery );
		$this->newQuery = $hooks->wrap( $newQuery );
	}

	public function render()
	{
		$params = $this->request->getParams();

		$calendarId = $params['calendar'];
		$shiftTypeId = $params['shifttype'];
		$dateString = $params['date'];
		$employeeString = $params['employee'];

		$dates = HC3_Functions::unglueArray( $dateString );

		$datesView = array();
		foreach( $dates as $date ){
			$this->t->setDateDb( $date );
			$dateView = $this->t->getWeekdayName() . ', ' . $this->t->formatDate();
			$dateView = $this->ui->makeBlock( $dateView )
				->tag('border')
				->tag('border-color', 'gray')
				->tag('padding', 2)
				;
			$datesView[] = $dateView;
		}

		$shiftType = $this->shiftTypesQuery->findById( $shiftTypeId );
		$range = $shiftType->getRange();
		switch( $range ){
			case SH4_ShiftTypes_Model::RANGE_DAYS:
				$datesView = $this->ui->makeListInline( $datesView )
					->separated( '&rarr;' )
					;
				break;

			case SH4_ShiftTypes_Model::RANGE_HOURS:
				$datesView = $this->ui->makeListInline( $datesView )
					->gutter(1)
					;
				break;
		}

		$label = ( count($dates) > 1 ) ? '__Dates__' : '__Date__';
		$datesView = $this->ui->makeLabelled( $label, $datesView );

		$shiftType = $this->shiftTypesQuery->findById( $shiftTypeId );

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

		$openQty = 0;
		$suppliedEmployeesIds = HC3_Functions::unglueArray( $employeeString );
		if( in_array(0, $suppliedEmployeesIds) ){
			$openQty = 1;
		}

		$ids = array_keys( $employees );

		foreach( $suppliedEmployeesIds as $id ){
			if( strpos($id, SH4_NEW_OPEN_SEPARATOR) !== FALSE ){
				list( $id, $openQty ) = explode( SH4_NEW_OPEN_SEPARATOR, $id );
				$suppliedEmployeesIds[] = $id;
				break;
			}
		}

		foreach( $ids as $id ){
			if( ! in_array($id, $suppliedEmployeesIds) ){
				unset( $employees[$id] );
			}
		}

		if( ! $openQty ){
			unset( $employees[0] );
		}

		if( ! $employees ){
			return;
		}

		$out = array();
		$calendarView = $this->calendarsPresenter->presentTitle( $calendar );

		// $description = $this->calendarsPresenter->presentDescription( $calendar );
		// if( strlen($description) ){
			// $len = 100;
			// if( strlen($description) > $len ){
				// $descriptionSummary = substr( $description, 0, $len ) . '...';
				// $descriptionSummary = $this->ui->makeElement( 'summary', $descriptionSummary );
				// $descriptionView = $this->ui->makeCollection( array($descriptionSummary, $description) );
				// $descriptionView = $this->ui->makeElement( 'details', $descriptionView );
			// }
			// else {
				// $descriptionView = $description;
			// }

			// $calendarView = $this->ui->makeList( array($calendarView, $descriptionView) )
				// ->gutter(0)
				// ;
		// }

		$shiftTypeView = $this->shiftTypesPresenter->presentTitleTime( $shiftType );

		$calendarView = $this->ui->makeList( array($calendarView, $shiftTypeView) )
			->gutter(0)
			;
		$calendarView = $this->ui->makeBlock( $calendarView )
			->tag('border')
			->tag('border-color', 'gray')
			->tag('padding', 2)
			;

		$out[] = $calendarView;

		$employeesView = array();
		$timeStart = $shiftType->getStart();
		$timeEnd = $shiftType->getEnd();

		foreach( $employees as $employee ){
			$withConflicts = 0;
			$withAvailability = 0;
			$withNothing = 0;

			$employeeId = $employee->getId();
			$thisView = array();

			$label = $this->employeesPresenter->presentTitle( $employee );

			if( ! $employeeId ){
				if( $openQty > 1 ){
					$label = $openQty . ' x ' . $label;
				}
			}

			$thisView[] = $label;

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
				array_unshift( $thisView, $conflictsView );
			}

			$thisView = $this->ui->makeListInline( $thisView );
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

			$employeesView[] = $thisView;
		}

		$employeesView = $this->ui->makeListInline( $employeesView );
		$label = ( count($employees) > 1 ) ? '__Employees__' : '__Employee__';
		$employeesView = $this->ui->makeLabelled( $label, $employeesView );

		$out[] = $datesView;
		$out[] = $employeesView;

		$out = $this->ui->makeList( $out );

		$out = array( $out );

// employee_create_own_conflicts
		$showButtons = TRUE;

		if( $withConflicts ){
			$isManager = FALSE;

			$currentUser = $this->auth->getCurrentUser();
			$currentUserId = $currentUser->getId();
			if( $currentUserId ){
				if( $this->permission->isAdmin($currentUser) ){
					$isManager = TRUE;
				}
				else {
					$calendarsAsManager = $this->appQuery->findCalendarsManagedByUser( $currentUser );
					if( isset($calendarsAsManager[$calendarId]) ){
						$isManager = TRUE;
					}
				}
			}

			if( ! $isManager ){
				if( ! $this->calendarsPermissions->get($calendar, 'employee_create_own_conflicts') ){
					$showButtons = FALSE;
				}
			}
		}

		if( $showButtons ){
			$btn = $this->self->buttons( $calendarId, $shiftTypeId, $dateString, $employeeString );
			$btn = $this->ui->makeListInline( $btn );
		}
		else {
			$btn = '__You cannot create new shifts with conflicts.__';
		}

	// open shift
		$inputs = $this->common->inputs( $calendarId, $shiftTypeId, $employeeId );
		$out = array_merge( $out, $inputs );

		$out[] = $btn;

		$out = $this->ui->makeList( $out )->gutter(3);

	// schedule link
		$scheduleLink = HC3_Session::instance()->getUserdata( 'scheduleLink' );
		if( ! $scheduleLink ){
			$scheduleLink = array( 'schedule', array() );
		}
		$scheduleLinkValue = json_encode( $scheduleLink );
		$inputBackHidden = $this->ui->makeInputHidden( 'back', $scheduleLinkValue );
		$out = $this->ui->makeCollection( array($out, $inputBackHidden) );

		$to = array( 'new', $calendarId, $shiftTypeId, $dateString, $employeeString );
		$to = join( '/', $to );
		$out = $this->ui->makeForm( $to, $out );

		$this->layout
			->setContent( $out )
			// ->setBreadcrumb( $this->common->breadcrumb($calendarId, $shiftTypeId) )
			->setHeader( $this->self->header() )
			;
		$out = $this->layout->render();

		return $out;
	}

	public function buttons( $calendarId, $shiftTypeId, $dateString, $employeeString )
	{
		$ret = array();

		$noDraft = $this->settings->get('shifts_no_draft') ? TRUE : FALSE;

	// draft
		if( ! $noDraft ){
			$to = array( 'new', $calendarId, $shiftTypeId, $dateString, $employeeString, 'draft' );
			$to = join( '/', $to );
			$btnDraft = $this->ui->makeInputSubmit( '__Create Draft__')
				->tag('secondary')
				->setFormAction( $to )
				;
			$ret['draft'] = $btnDraft;
		}

	// publish
		$to = array( 'new', $calendarId, $shiftTypeId, $dateString, $employeeString, 'publish' );
		$to = join( '/', $to );
		$label = $noDraft ? '__Post Shift__' : '__Create Published__';

		$btnPublish = $this->ui->makeInputSubmit( $label )
			->tag('primary')
			->setFormAction( $to )
			;

		$confirm = $this->settings->get('shifts_confirm_publish') ? true : false;
		if( $confirm ){
			$btnPublish->addAttr( 'onclick', ' return confirm(\'' . $label . '?\')' );
		}

		$ret['publish'] = $btnPublish;

		return $ret;
	}

	public function header()
	{
		$out = '__Confirm__';
		return $out;
	}
}