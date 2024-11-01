<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
#[AllowDynamicProperties]
class SH4_New_Controller_Employee
{
	public function __construct(
		HC3_Hooks $hooks,
		HC3_Post $post,
		HC3_Request $request,

		SH4_Calendars_Query $calendars,
		SH4_ShiftTypes_Query $shiftTypes,

		HC3_Time $t
		)
	{
		$this->t = $t;
		$this->request = $request;
		$this->post = $hooks->wrap( $post );

		$this->calendars = $hooks->wrap($calendars);
		$this->shiftTypes = $hooks->wrap($shiftTypes);

		$this->self = $hooks->wrap($this);
	}

	public function execute()
	{
		$thisId = array();

		$params = $this->request->getParams();
		$employees = $this->post->get('employee');
		if( ! $employees ){
			$employees = array();
		}

		if( in_array('0', $employees) ){
			$employees = HC3_Functions::removeFromArray( $employees, '0' );
		}

		if( ! $employees ){
			$errors = array();
			$errors['employee'] = '__Select Employee__';
			$return = array( '-referrer-', $errors, TRUE );
			return $return;
		}

		$employeeString = HC3_Functions::glueArray( $employees );

		$to = 'new';
		$toParams = $params;
		$toParams['employee'] = $employeeString;
		$to = array( $to, $toParams );

		$return = array( $to, NULL );
		return $return;
	}
}