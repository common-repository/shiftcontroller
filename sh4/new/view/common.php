<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
interface SH4_New_View_ICommon
{
	public function breadcrumb( $calendarId, $shiftTypeId = 'x' );
	public function inputs( $calendarId, $shiftTypeId, $employeeId );
}

#[AllowDynamicProperties]
class SH4_New_View_Common implements SH4_New_View_ICommon
{
	public function __construct(
		HC3_Hooks $hooks,

		HC3_Request $request,
		HC3_Ui $ui,
		HC3_Time $t,

		SH4_Calendars_Query $calendarsQuery,
		SH4_Calendars_Presenter $calendarsPresenter,

		SH4_ShiftTypes_Query $shiftTypesQuery,
		SH4_ShiftTypes_Presenter $shiftTypesPresenter,

		SH4_Notifications_Html_Admin_View_TurnOnOff $notificationsTurnOnOff,

		SH4_Employees_Query $employees
		)
	{
		$this->request = $request;
		$this->ui = $ui;
		$this->t = $t;

		$this->calendarsQuery = $hooks->wrap( $calendarsQuery );
		$this->calendarsPresenter = $hooks->wrap( $calendarsPresenter );

		$this->shiftTypesQuery = $hooks->wrap( $shiftTypesQuery );
		$this->shiftTypesPresenter = $hooks->wrap( $shiftTypesPresenter );

		$this->notificationsTurnOnOff = $hooks->wrap( $notificationsTurnOnOff );

		$this->employees = $hooks->wrap($employees);

		$this->self = $hooks->wrap($this);
	}

	public function breadcrumb( $calendarId, $shiftTypeId = 'x' )
	{
		$params = $this->request->getParams();

		$return = array();

		$calendar = $this->calendarsQuery->findById( $calendarId );
		$label = $this->calendarsPresenter->presentTitle( $calendar );

		// if( $calendar->isShift() ){
		// 	$return['new'] = '+' . ' ' . '__Shift__';
		// 	$return['new/calendar'] = array( 'newshift', $label );
		// }
		// elseif( $calendar->isTimeoff() ){
		// 	$return['new'] = array( 'newtimeoff', '+' . ' ' . '__Time Off__' );
		// 	$return['new/calendar'] = array( 'newtimeoff', $label );
		// }
		// elseif( $calendar->isAvailability() ){
		// 	$return['new'] = array( 'newavailability', '+' . ' ' . '__Availability__' );
		// 	$return['new/calendar'] = array( 'newavailability', $label );
		// }

		$return['new/calendar'] = $label;
		if( $shiftTypeId != 'x' ){
			$shiftType = $this->shiftTypesQuery->findById( $shiftTypeId );
			// $label = $this->shiftTypesPresenter->presentTitleTime( $shiftType );
			$label1 = $this->shiftTypesPresenter->presentTitle( $shiftType );
			$label2 = $this->shiftTypesPresenter->presentTime( $shiftType );
			$label = $this->ui->makeListInline( array($label1, $label2) )->gutter(1);

			// $return['new/shifttype'] = array( 'new/' . $calendarId . '/' . 'x' . '/' . $employeeId, $label );
			$return['new/shifttype'] = $label;
		}

		return $return;
	}

	public function inputs( $calendarId, $shiftTypeId, $employeeId )
	{
		$return = array();

		// if( ! $employeeId ){
		// 	$qtyOptions = range( 1, 50 );
		// 	$qtyOptions = array_combine( $qtyOptions, $qtyOptions );
		// 	$qtyField = $this->ui->makeInputSelect( 'qty', '__How Many__', $qtyOptions );
		// 	$return['qty'] = $qtyField;
		// }

		$return['notifications'] = $this->notificationsTurnOnOff->renderInput();
		return $return;
	}
}