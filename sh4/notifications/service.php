<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
interface SH4_Notifications_IService
{
	public function register( $listenOn, $notificationId, $class, $defaultOn = TRUE );

	public function findById( $notificationId );
	public function findAll();

	public function setOn( SH4_Calendars_Model $calendar, $notificationId );
	public function setOff( SH4_Calendars_Model $calendar, $notificationId );
	public function isOn( SH4_Calendars_Model $calendar, $notificationId );

	public function getTemplate( SH4_Calendars_Model $calendar, $notificationId );
	public function setTemplate( SH4_Calendars_Model $calendar, $notificationId, $template );

	public function setOnForUser( HC3_Users_Model $user, SH4_Calendars_Model $calendar = NULL, $notificationId = NULL );
	public function setOffForUser( HC3_Users_Model $user, SH4_Calendars_Model $calendar = NULL, $notificationId = NULL );
	public function isOnForUser( HC3_Users_Model $user, SH4_Calendars_Model $calendar = NULL, $notificationId = NULL );
}

class SH4_Notifications_Service implements SH4_Notifications_IService
{
	protected $notifications = array();
	protected $calendars = array();
	protected $listen = array();

	public $self, $settings, $dic, $hooks;

	public function __construct(
		HC3_Hooks $hooks,
		HC3_Dic $dic,
		HC3_Settings $settings,

		SH4_Calendars_Query $calendarsQuery
		)
	{
		$this->settings = $settings;
		$this->dic = $dic;

		$calendarsQuery = $hooks->wrap( $calendarsQuery );
		$this->calendars = $calendarsQuery->findActive();

		$this->self = $hooks->wrap( $this );
		$this->hooks = $hooks;
	}

	public function register( $listenOn, $notificationId, $class, $defaultOn = TRUE )
	{
		$this->notifications[ $notificationId ] = $class;

		reset( $this->calendars );
		foreach( $this->calendars as $calendar ){
			$key = $this->_settingsKey( $calendar, $notificationId, 'on' );
			$value = $defaultOn ? 1 : 0;
			$this->settings->init( $key, $value );
		}

		$self = $this;

		if( ! isset($this->listen[$listenOn]) ){
			$this->listen[$listenOn] = array();

			$func = function($args) use ($listenOn, $self){
				return $self->listen( $listenOn, $args );
				};

			$this->hooks
				->add( $listenOn, $func )
				;
		}
		$this->listen[$listenOn][] = $notificationId;

		return $this;
	}

	public function listen( $listenOn, $args )
	{
		if( ! isset($this->listen[$listenOn]) ){
			return;
		}

		$shift = $args[0];
		$calendar = $shift->getCalendar();

		foreach( $this->listen[$listenOn] as $notificationId ){
			if( ! $this->isOn($calendar, $notificationId) ){
				continue;
			}

			$template = $this->getTemplate( $calendar, $notificationId );
			$notification = $this->findById( $notificationId );

			$notification
				->execute( $shift, $template )
				;
		}
	}

	public function findAll()
	{
		$ids = array_keys( $this->notifications );
		foreach( $ids as $id ){
			$return[ $id ] = $this->self->findById( $id );
		}
		return $return;
	}

	public function findById( $notificationId )
	{
		$return = NULL;
		if( array_key_exists($notificationId, $this->notifications) ){
			$returnClass = $this->notifications[ $notificationId ];
			$return = $this->dic->make( $returnClass );
		}

		return $return;
	}

	public function setOn( SH4_Calendars_Model $calendar, $notificationId )
	{
		$key = $this->_settingsKey( $calendar, $notificationId, 'on' );
		$this->settings->set( $key, 1 );
		return $this;
	}

	public function setOff( SH4_Calendars_Model $calendar, $notificationId )
	{
		$key = $this->_settingsKey( $calendar, $notificationId, 'on' );
		$this->settings->set( $key, 0 );
		return $this;
	}

	public function isOn( SH4_Calendars_Model $calendar, $notificationId )
	{
		$key = $this->_settingsKey( $calendar, $notificationId, 'on' );
		$return = $this->settings->get( $key );
		return $return;
	}

	public function setOnForUser( HC3_Users_Model $user, SH4_Calendars_Model $calendar = NULL, $notificationId = NULL )
	{
		$key = $this->_settingsKeyForUser( $user, 'on', $calendar, $notificationId );

		$this->settings->set( $key, 1 );
		return $this;
	}

	public function setOffForUser( HC3_Users_Model $user, SH4_Calendars_Model $calendar = NULL, $notificationId = NULL )
	{
		$key = $this->_settingsKeyForUser( $user, 'on', $calendar, $notificationId );

		$this->settings->set( $key, 0 );
		return $this;
	}

	public function isOnForUser( HC3_Users_Model $user, SH4_Calendars_Model $calendar = NULL, $notificationId = NULL )
	{
		$key = $this->_settingsKeyForUser( $user, 'on', $calendar, $notificationId );

		$ret = $this->settings->get( $key );
		if( null === $ret ){
			$ret = true;
		}

		return $ret;
	}

	public function getTemplate( SH4_Calendars_Model $calendar, $notificationId )
	{
		$key = $this->_settingsKey( $calendar, $notificationId, 'template' );
		$ret = $this->settings->get( $key );

		if( ! $ret ){
			$ret = '';
		}

		if( ! strlen($ret) ){
			$notification = $this->self->findById( $notificationId );
			$ret = $notification->getDefaultTemplate();
		}

		return $ret;
	}

	public function setTemplate( SH4_Calendars_Model $calendar, $notificationId, $template )
	{
		$key = $this->_settingsKey( $calendar, $notificationId, 'template' );
		$this->settings->set( $key, $template );
		return $this;
	}

	protected function _settingsKey( SH4_Calendars_Model $calendar, $notificationId, $pname )
	{
		$calendarId = $calendar->getId();

		$return = array();
		$return[] = 'notification';
		$return[] = $notificationId;
		$return[] = $pname;
		$return[] = $calendarId;

		$return = join( '_', $return );

		return $return;
	}

	protected function _settingsKeyForUser( HC3_Users_Model $user, $pname, SH4_Calendars_Model $calendar = NULL, $notificationId = NULL )
	{
		$calendarId = $calendar ? $calendar->getId() : 'x';
		$notificationId = $notificationId ? $notificationId : 'x';
		$userId = $user->getId();

		$return = array();
		$return[] = 'user';
		$return[] = $userId;

		$return[] = 'notification';
		$return[] = $notificationId;
		$return[] = $pname;
		$return[] = $calendarId;

		$return = join( '_', $return );

		return $return;
	}
}