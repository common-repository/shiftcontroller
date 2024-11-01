<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
interface SH4_Calendars_IPermissions
{
	public function set( SH4_Calendars_Model $calendar, $permissionId, $value );
	public function get( SH4_Calendars_Model $calendar, $permissionId );
	public function getAll( SH4_Calendars_Model $calendar );
	public function options();
	public function categorizedOptions();
}

class SH4_Calendars_Permissions implements SH4_Calendars_IPermissions
{
	public $self, $dic, $settings, $calendarsQuery;

	public function __construct(
		HC3_Hooks $hooks,
		HC3_Dic $dic,
		HC3_Settings $settings,

		SH4_Calendars_Query $calendarsQuery
		)
	{
		$this->self = $hooks->wrap( $this );

		$this->settings = $settings;
		$this->dic = $dic;

		$calendarsQuery = $hooks->wrap( $calendarsQuery );
		$calendars = $calendarsQuery->findActive();

		$defaults = $this->self->getDefaults();

		reset( $calendars );
		foreach( $calendars as $calendar ){
			reset( $defaults );
			foreach( $defaults as $key => $value ){
				$key = $this->_settingsKey( $calendar, $key );
				$this->settings->init( $key, $value );
			}
		}
	}

	public function categorizedOptions()
	{
		$ret = array( 'employee' => array(), 'employee2' => array(), 'visitor' => array() );

		$options = $this->self->options();
		foreach( $options as $o ){
			$label = array_shift( $o );
			$employeeOption = array_shift( $o );
			$visitorOption = array_shift( $o );
			$employee2Option = array_shift( $o );
			// list( $label, $employeeOption, $visitorOption ) = $o;

			if( $employeeOption ){
				$ret['employee'][] = $employeeOption[0];
			}
			if( $visitorOption ){
				$ret['visitor'][] = $visitorOption[0];
			}
			if( $employee2Option ){
				$ret['employee2'][] = $employee2Option[0];
			}
		}

		return $ret;
	}

	public function options()
	{
		$ret = array();

		$ret[] = array(
			'__View Own Published Shifts__',
				array( 'employee_view_own_publish', 1 ),
				NULL
			);

		$ret[] = array(
			'__View Own Draft Shifts__',
			array( 'employee_view_own_draft', 1 ),
			NULL
			);

		$ret[] = array(
			'__Create Own Published Shifts__',
			array( 'employee_create_own_publish', 0 ),
			NULL
			);

		$ret[] = array(
			'__Create Own Draft Shifts__',
			array( 'employee_create_own_draft', 0 ),
			NULL
			);

		$ret[] = array(
			'__Edit Own Published Shifts__',
			array( 'employee_edit_own_publish', 0 ),
			NULL
			);

		$ret[] = array(
			'__Edit Own Draft Shifts__',
			array( 'employee_edit_own_draft', 0 ),
			NULL
			);

		$ret[] = array(
			'__Delete Own Published Shifts__',
			array( 'employee_delete_own_publish', 0 ),
			NULL
			);

		$ret[] = array(
			'__Delete Own Draft Shifts__',
			array( 'employee_delete_own_draft', 0 ),
			NULL
			);

		$ret[] = array(
			'__Create Own Shifts With Conflicts__',
			array( 'employee_create_own_conflicts', 0 ),
			NULL
			);

		$ret[] = array(
			'__View Others Published Shifts__',
			array( 'employee_view_others_publish', 1 ),
			array( 'visitor_view_others_publish', 1 ),
			array( 'employee2_view_others_publish', 1 )
			);

		$ret[] = array(
			'__View Others Draft Shifts__',
			array( 'employee_view_others_draft', 0 ),
			array( 'visitor_view_others_draft', 0 ),
			array( 'employee2_view_others_draft', 0 )
			);

		$ret[] = array(
			'__View Open Published Shifts__',
			array( 'employee_view_open_publish', 1 ),
			array( 'visitor_view_open_publish', 0 ),
			array( 'employee2_view_open_publish', 0 )
			);

		$ret[] = array(
			'__View Open Draft Shifts__',
			array( 'employee_view_open_draft', 0 ),
			array( 'visitor_view_open_draft', 0 ),
			array( 'employee2_view_open_draft', 0 )
			);

		return $ret;
	}

	public function set( SH4_Calendars_Model $calendar, $permissionId, $value )
	{
		$key = $this->_settingsKey( $calendar, $permissionId );
		$this->settings->set( $key, $value );
		return $this;
	}

	public function getDefaults()
	{
		$defaults = array();

		$options = $this->self->options();
		foreach( $options as $option ){
			$label = array_shift( $option );
			while( $option ){
				$prop = array_shift($option);
				if( NULL === $prop ){
					continue;
				}

				list( $permissionName, $defaultValue ) = $prop;
				$defaults[ $permissionName ] = $defaultValue;
			}
		}

		return $defaults;
	}

	public function getAll( SH4_Calendars_Model $calendar )
	{
		$defaults = $this->self->getDefaults();

		foreach( array_keys($defaults) as $permissionName ){
			$return[$permissionName] = $this->self->get( $calendar, $permissionName );
		}

		return $return;
	}

	public function get( SH4_Calendars_Model $calendar, $permissionId )
	{
		$key = $this->_settingsKey( $calendar, $permissionId );
		$return = $this->settings->get( $key );
		return $return;
	}

	protected function _settingsKey( SH4_Calendars_Model $calendar, $permissionId )
	{
		$calendarId = $calendar->getId();

		$return = array();
		$return[] = 'permissions';
		$return[] = $permissionId;
		$return[] = $calendarId;

		$return = join( '_', $return );

		return $return;
	}
}