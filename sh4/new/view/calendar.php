<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
interface SH4_New_View_ICalendar
{
	public function render();

	public function renderShift();
	public function renderTimeoff();
	public function renderAvailability();

	public function finalize( $entries );
	public function header();
	public function renderShiftTypes( $calendar );
}

#[AllowDynamicProperties]
class SH4_New_View_Calendar implements SH4_New_View_ICalendar
{
	public function __construct(
		HC3_Hooks $hooks,

		HC3_Settings $settings,
		HC3_Request $request,
		HC3_Ui $ui,
		HC3_Time $t,
		HC3_Ui_Layout1 $layout,
		SH4_New_View_Common $common,

		SH4_App_Query $appQuery,
		SH4_New_Query $newQuery,
		SH4_Calendars_Presenter $calendarsPresenter,

		SH4_ShiftTypes_Presenter $shiftTypesPresenter
		)
	{
		$this->request = $request;
		$this->ui = $ui;
		$this->t = $t;
		$this->layout = $layout;
		$this->common = $hooks->wrap($common);
		$this->settings = $hooks->wrap($settings);

		$this->appQuery = $hooks->wrap( $appQuery );
		$this->newQuery = $hooks->wrap( $newQuery );
		$this->calendarsPresenter = $hooks->wrap( $calendarsPresenter );
		$this->shiftTypesPresenter = $hooks->wrap( $shiftTypesPresenter );

		$this->self = $hooks->wrap($this);
	}

	protected function _findAll()
	{
		$params = $this->request->getParams();

		$entries = $this->newQuery->findAllCalendars();

	// check if all of them have shift types
		$ids = array_keys($entries);
		foreach( $ids as $id ){
			$shiftTypes = $this->appQuery->findShiftTypesForCalendar( $entries[$id] );
			if( ! $shiftTypes ){
				unset( $entries[$id] );
			}
		}

		$employee = NULL;
		if( array_key_exists('employee', $params) ){
			$employeeId = $params['employee'];
			$allEmployees = $this->newQuery->findAllEmployees();
			if( isset($allEmployees[$employeeId]) ){
				$employee = $allEmployees[$employeeId];
			}
		}

		if( $employee ){
			$filter = $this->appQuery->findCalendarsForEmployee( $employee );
			$filterIds = array_keys( $filter );
			$ids = array_keys($entries);
			foreach( $ids as $id ){
				if( ! in_array($id, $filterIds) ){
					unset( $entries[$id] );
				}
			}
		}

		return $entries;
	}

	public function render()
	{
		$entries = $this->_findAll();

		$params = $this->request->getParams();

		if( array_key_exists('calendar', $params) ){
			$calendarIds = is_array( $params['calendar'] ) ? $params['calendar'] : array( $params['calendar'] );
			$calendarIds = array_intersect( $calendarIds, array_keys($entries) );
			if( $calendarIds ){
				$newEntries = array();
				foreach( $calendarIds as $calendarId ){
					$newEntries[ $calendarId ] = $entries[ $calendarId ];
				}
				$entries = $newEntries;
			}
		}

		$out = $this->finalize( $entries );
		return $out;
	}

	public function renderShift()
	{
		$entries = $this->_findAll();

		$ids = array_keys($entries);
		foreach( $ids as $id ){
			if( ! $entries[$id]->isShift() ){
				unset( $entries[$id] );
			}
		}

		$out = $this->finalize( $entries );
		return $out;
	}

	public function renderTimeoff()
	{
		$entries = $this->_findAll();

		$ids = array_keys($entries);
		foreach( $ids as $id ){
			if( ! $entries[$id]->isTimeoff() ){
				unset( $entries[$id] );
			}
		}

		$out = $this->finalize( $entries );
		return $out;
	}

	public function renderAvailability()
	{
		$entries = $this->_findAll();

		$ids = array_keys($entries);
		foreach( $ids as $id ){
			if( ! $entries[$id]->isAvailability() ){
				unset( $entries[$id] );
			}
		}

		$out = $this->finalize( $entries );
		return $out;
	}

	public function finalize( $entries )
	{
		$params = $this->request->getParams();

		$out = array();
		foreach( $entries as $e ){
			$thisId = $e->getId();

			// $thisLabel = $e->getTitle();
			// $color = $this->ui->makeBlockInline('&nbsp;')->paddingX(1)->tag('bgcolor', $e->getColor() );
			// $thisLabel = $this->ui->makeListInline( array($color, $thisLabel) );
			// $thisLabel = $this->ui->makeBlock( $thisLabel )
				// ->tag('font-size', 4)
				// ;
			$thisLabel = $this->calendarsPresenter->presentTitle( $e );

			$description = $this->calendarsPresenter->presentDescription( $e );
			if( strlen($description) ){
				$thisLabel = $this->ui->makeList( array($thisLabel, $description) );
			}

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

				// $thisLabel = $this->ui->makeList( array($thisLabel, $descriptionView) )
					// ->gutter(0)
					// ;
			// }

			$thisView = $this->self->renderShiftTypes( $e );
			if( $thisView ){
				$thisView = $this->ui->makeList( array($thisLabel, $thisView) );

				$thisView = $this->ui->makeBlock( $thisView )
					->tag('padding', 2)
					->tag('border')
					->tag('border-color', 'gray')
					;

				$out[] = $thisView;
			}
		}

		if( isset($params['date']) ){
			$dateView = $this->t->setDateDb( $params['date'] )->formatDateWithWeekday();
			$dateView = $this->ui->makeBlock( $dateView )
				->tag('font-size', 4)
				->tag('paddingX', 2 )
				;
			array_unshift( $out, $dateView );
		}

		$out = $this->ui->makeList( $out )
			->gutter(3)
			;

		$this->layout
			->setContent( $out )
			->setHeader( $this->self->header() )
			;

		$out = $this->layout->render();
		return $out;
	}

	public function header()
	{
		$out = '__Calendar & Time__';
		return $out;
	}

	public function renderShiftTypes( $calendar )
	{
		$calendarId = $calendar->getId();
		$params = $this->request->getParams();

		$entries = $this->appQuery->findShiftTypesForCalendar( $calendar );
		$customTime = NULL;
		if( array_key_exists(0, $entries) ){
			$customTime = $entries[0];
			unset( $entries[0] );
		}

		$out = array();

		$templatesOut = array();
		foreach( $entries as $e ){
			$thisId = $e->getId();

			$thisView = $this->shiftTypesPresenter->presentTitle( $e );
			$timeView = $this->shiftTypesPresenter->presentTime( $e );
			$timeView = $this->ui->makeSpan($timeView)
				->tag('font-size', 2)
				;
			$thisView = $this->ui->makeList( array($thisView, $timeView) )->gutter(1);

			$to = 'new';
			$toParams = $params;
			$toParams['calendar'] = $calendarId;
			$toParams['shifttype'] = $thisId;

			if( SH4_ShiftTypes_Model::RANGE_DAYS == $e->getRange() ){
				if( isset($toParams['date']) ){
					$toParams['start'] = $toParams['date'];
					unset( $toParams['date'] );
				}
			}

			$to = array( $to, $toParams );

			$thisView = $this->ui->makeAhref( $to, $thisView )
				->tag('tab-link')
				->addAttr( 'class', 'hc-underline' );
				// ->tag('underline')
				// ->tag('border')
				;

			if( $thisView ){
				$templatesOut[] = $thisView;
			}
		}

	// if we have custom time
		$out = array();

		if( $customTime ){
			$thisView = $this->shiftTypesPresenter->presentTitle( $customTime );
			$thisView = $this->ui->makeBlock( $thisView )
				->tag('tab-link')
				;

			$to = 'new/customtime/' . $calendarId;
			$to = array( $to, $params );

			$thisFormInputs = array();

			$value = array();
			$minTime = $this->settings->get('datetime_min_time');
			$defaultDuration = $this->settings->get('shifttypes_default_duration');
			if( (NULL !== $minTime) && (NULL !== $defaultDuration) ){
				$value = array( $minTime, $minTime + $defaultDuration );
			}
			$thisFormInputs[] = $this->ui->makeInputTimeRange( 'time', NULL, $value );

			$noBreak = $this->settings->get( 'shifttypes_nobreak' );
			if( ! $noBreak ){
				$thisFormInputs[] = $this->ui->makeCollapseCheckbox(
					'break_on',
					'__Lunch Break__' . '?',
					$this->ui->makeInputTimeRange( 'break', NULL)
					);
			}

			$thisFormInputs[] = $this->ui->makeInputSubmit( '__Continue__')->tag('secondary');

			// $thisFormInputs = $this->ui->makeList( $thisFormInputs );
			$thisFormInputs = $this->ui->makeListInline( $thisFormInputs );

			$thisForm = $this->ui->makeForm( $to, $thisFormInputs );

			$thisForm = $this->ui->makeBlock( $thisForm )
				// ->tag('border')
				->tag('padding', 2)
				;

			$out[] = '<h2>__Custom Time__</h2>';
			$out[] = $thisForm;
		}

		if( $templatesOut ){
			foreach( $templatesOut as $to ){
				$out[] = $to;
			}
		}

		if( $out ){
			$out = $this->ui->makeGrid( $out, 4 );
		}

		$out2 = array();

		if( $templatesOut ){
			$templatesOut = $this->ui->makeListInline( $templatesOut );

			$templatesOut = array( $templatesOut );
			// if( $customTime ){
				array_unshift( $templatesOut, '<strong>__Select Shift Time__</strong>' );
			// }

			$templatesOut = $this->ui->makeList( $templatesOut );
			$out2[] = $templatesOut;
		}

		if( $customTime ){
			if( $templatesOut ){
				$out2[] = '<strong>__Custom Time__</strong>';
			}
			$out2[] = $thisForm;
		}

		$out2 = $this->ui->makeList( $out2 );
		return $out2;

		return $out;
	}
}