<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
#[AllowDynamicProperties]
class SH4_Calendars_Html_Admin_View_ShiftTypes
{
	public function __construct(
		HC3_Hooks $hooks,
		HC3_Ui $ui,
		HC3_Ui_Layout1 $layout,

		SH4_App_Query $appQuery,

		SH4_ShiftTypes_Query $shiftTypesQuery,
		SH4_ShiftTypes_Presenter $shiftTypesPresenter,

		SH4_Calendars_Query $calendarsQuery,
		SH4_Calendars_Presenter $calendarsPresenter
	)
	{
		$this->self = $hooks->wrap($this);
		$this->ui = $ui;
		$this->layout = $layout;

		$this->appQuery = $hooks->wrap( $appQuery );

		$this->shiftTypesQuery = $hooks->wrap( $shiftTypesQuery );
		$this->shiftTypesPresenter = $hooks->wrap( $shiftTypesPresenter );

		$this->calendarsQuery = $hooks->wrap( $calendarsQuery );
		$this->calendarsPresenter = $hooks->wrap( $calendarsPresenter );
	}

	public function render( $id, $new = FALSE )
	{
		$model = $this->calendarsQuery->findById($id);

		$currentShiftTypes = $this->appQuery->findShiftTypesForCalendar( $model );
		$currentShiftTypesIds = array_keys( $currentShiftTypes );

		$shiftTypes = $this->shiftTypesQuery->findAll();
		$shiftTypesView = array();
		foreach( $shiftTypes as $shiftType ){
			$shiftTypeId = $shiftType->getId();
			$checked = in_array( $shiftTypeId, $currentShiftTypesIds ) ? TRUE : FALSE;

			$thisView = $this->shiftTypesPresenter->presentTitle( $shiftType );
			$thisTimeView = $this->shiftTypesPresenter->presentTime( $shiftType );

			$thisView = $this->ui->makeInputCheckbox( 'shifttype[]', $thisView, $shiftTypeId, $checked );

			$thisView = $this->ui->makeList( array($thisView, $thisTimeView) )
				->gutter(1)
				;

			$thisView = $this->ui->makeBlock( $thisView )
				->tag('border')
				->tag('padding', 1)
				;
			if( $checked ){
				$thisView
					->tag('border-color', 'olive')
					;
			}

			$shiftTypesView[] = $thisView;
		}

		$out = $this->ui->makeListInline( $shiftTypesView );

		$buttons = $this->ui->makeInputSubmit( '__Save__')
			->tag('primary')
			;
		$out = $this->ui->makeList( array($out, $buttons) );

		$to = $new ? 'admin/calendars/' . $id . '/shifttypes/new' : 'admin/calendars/' . $id . '/shifttypes';
		$out = $this->ui->makeForm( $to, $out );

		$this->layout
			->setContent( $out )
			->setBreadcrumb( $this->self->breadcrumb($model) )
			->setHeader( $this->self->header($model) )
			;

		$out = $this->layout->render();
		return $out;
	}

	public function header( SH4_Calendars_Model $model )
	{
		$out = '__Shift Time__';
		return $out;
	}

	public function breadcrumb( SH4_Calendars_Model $model )
	{
		$calendarId = $model->getId();
		$calendarTitle = $this->calendarsPresenter->presentTitle( $model );

		$return = array();
		$return['admin'] = array( 'admin', '__Administration__' );
		$return['calendars'] = array( 'admin/calendars', '__Calendars__' );
		$return['calendars/edit'] = $calendarTitle;
		return $return;
	}
}
