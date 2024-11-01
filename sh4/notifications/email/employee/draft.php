<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class SH4_Notifications_Email_Employee_Draft implements SH4_Notifications_INotification
{
	public $self, $notificationsTemplate, $notificator, $appQuery;

	public function __construct( 
		HC3_Hooks $hooks,
		HC3_Notificator $notificator,
		SH4_Notifications_Template $notificationsTemplate,
		SH4_App_Query $appQuery
	)
	{
		$this->self = $hooks->wrap( $this );

		$this->notificationsTemplate = $hooks->wrap( $notificationsTemplate );
		$this->notificator = $hooks->wrap( $notificator );
		$this->appQuery = $appQuery;
	}

	public function getTitle()
	{
		$return = '__Email__' . ': ' . '__Draft Shift Created__' . ' ('. '__Employee__' . ')';
		return $return;
	}

	public function execute( SH4_Shifts_Model $shift, $template )
	{
	// if is linked to a user account
		$employee = $shift->getEmployee();
		$user = $this->appQuery->findUserByEmployee( $employee );
		if( ! $user ){
			return;
		}

		$msg = $this->notificationsTemplate->parse( $template, $shift );

		$this->notificator
			->queue( $user, 'email_employee_publish', $msg )
			;
	}

	public function getDefaultTemplate()
	{
		$return = array();
		$return[] = '__Draft Shift Created__' . ' (#{ID})';

		$return[] = '{CALENDAR}';
		$return[] = '{DATETIME}';
		$return[] = '{EMPLOYEE}';

		$return = join("\n", $return);
		return $return;
	}
}