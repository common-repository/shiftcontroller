<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class SH4_Reminders_Sms
{
	public $self, $settings;

	public function __construct(
		HC3_Settings $settings,
		HC3_Hooks $hooks
		)
	{
		$this->settings = $hooks->wrap( $settings );
		$this->self = $hooks->wrap( $this );
	}

	public function isEnabled()
	{
		$ret = function_exists( 'wp_sms_send' ) ? true : false;
		return $ret;
	}

	public function send( $to, $msg )
	{
		if( ! is_array($to) ) $to = [ $to ];
		wp_sms_send( $to, $msg );
	}

	public function setUserPhone( $userId, $phoneNo )
	{
		$pname = 'sms_user_phone_' . $userId;
		$this->settings->set( $pname, $phoneNo );
		return $this;
	}

	public function getUserPhone( $userId )
	{
		$pname = 'sms_user_phone_' . $userId;
		$ret = $this->settings->get( $pname );
		return $ret;
	}

	public function setEmployeePhone( $employeeId, $phoneNo )
	{
		$pname = 'sms_employee_phone_' . $employeeId;
		$this->settings->set( $pname, $phoneNo );
		return $this;
	}

	public function getEmployeePhone( $employeeId )
	{
		$pname = 'sms_employee_phone_' . $employeeId;
		$ret = $this->settings->get( $pname );
		return $ret;
	}
}