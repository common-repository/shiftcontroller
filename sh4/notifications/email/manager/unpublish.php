<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class SH4_Notifications_Email_Manager_Unpublish implements SH4_Notifications_INotification
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
		$this->appQuery = $hooks->wrap( $appQuery );
	}

	public function getTitle()
	{
		$return = '__Email__' . ': ' . '__Shift Unpublished__' . ' ('. '__Manager__' . ')';
		return $return;
	}

	public function execute( SH4_Shifts_Model $shift, $template )
	{
		$msg = $this->notificationsTemplate->parse( $template, $shift );

		$calendar = $shift->getCalendar();
		$managers = $this->appQuery->findManagersForCalendar( $calendar );

		foreach( $managers as $user ){
			$this->notificator
				->queue( $user, 'email_manager_unpublish', $msg )
				;
		}
	}

	public function getDefaultTemplate()
	{
		$return = array();
		$return[] = '__Shift Unpublished__' . ' (#{ID})';

		$return[] = '{CALENDAR}';
		$return[] = '{DATETIME}';
		$return[] = '{EMPLOYEE}';

		$return = join("\n", $return);
		return $return;
	}
}