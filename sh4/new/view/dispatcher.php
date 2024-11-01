<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
#[AllowDynamicProperties]
class SH4_New_View_Dispatcher
{
	public function __construct(
		HC3_Hooks $hooks,

		HC3_Request $request,

		SH4_App_Query $appQuery,
		SH4_Employees_Query $employeesQuery,
		SH4_New_Query $newQuery
		)
	{
		$this->self = $hooks->wrap( $this );
		$this->request = $request;

		$this->appQuery = $hooks->wrap( $appQuery );
		$this->employeesQuery = $hooks->wrap( $employeesQuery );
		$this->newQuery = $hooks->wrap( $newQuery );

	}

	public function render()
	{
		$to = $this->getTo();
		$params = $this->request->getParams();
		if( $params ){
			$to = array( $to, $params );
		}

		$return = array( $to, NULL );
		return $return;
	}

	protected function getTo()
	{
		$return = NULL;
		$params = $this->request->getParams();

		if( ! (array_key_exists('calendar', $params) && array_key_exists('shifttype', $params)) ){
			$return = 'new/calendar';
			return $return;
		}

		if( ! array_key_exists('date', $params) ){
			$return = 'new/date';
			return $return;
		}

		if( ! array_key_exists('employee', $params) ){
			$return = 'new/employee';
			return $return;
		}

		if( ! $return ){
			$return = 'new/confirm';
			return $return;
		}
	}
}