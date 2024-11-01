<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
interface SH4_Shifts_IQuery
{
	public function setStart( $dateTime );
	public function setEnd( $dateTime );
	public function find();
	public function findById( $id );
	public function findManyById( array $ids );
	public function findAllByEmployee( SH4_Employees_Model $employee );
	public function findAllByCalendar( SH4_Calendars_Model $calendar );
}

class SH4_Shifts_Query implements SH4_Shifts_IQuery
{
	protected $start = 197001010000;
	protected $end = 230001010000;
	protected $calendars = array();
	protected $employees = array();

	protected $_loadedStart = NULL;
	protected $_loadedEnd = NULL;
	protected $_storage = array();

	public $self, $t, $crud, $calendarsQuery, $employeesQuery;

	public function __construct(
		HC3_Time $t,
		HC3_CrudFactory $crudFactory,
		SH4_Calendars_Query $calendarsQuery,
		SH4_Employees_Query $employeesQuery,
		HC3_Hooks $hooks
		)
	{
// echo "CONTSTRUCTING!";
		$this->t = $t;
		$this->self = $hooks->wrap( $this );

		$this->crud = $crudFactory->make('shift')
			->withMeta()
			;

		$this->calendarsQuery = $calendarsQuery;
		$this->employeesQuery = $employeesQuery;
	}

	public function setStart( $dateTime )
	{
		$this->start = $dateTime;
		return $this;
	}

	public function setEnd( $dateTime )
	{
		$this->end = $dateTime;
		return $this;
	}

	public function keep( SH4_Shifts_Model $model )
	{
		$id = $model->getId();
		if( $id ){
			$this->_storage[$id] = $model;
		}

		return $this;
	}

	public function findById( $id )
	{
		$return = NULL;
		
		if( is_array($id) && isset($id['id']) ){
			$id = $id['id'];
		}

		if( array_key_exists($id, $this->_storage) ){
			$return = $this->_storage[$id];
			return $return;
		}

		$args = array();
		$args[] = array('id', '=', $id);
		$args[] = array('limit', 1);

		if( $array = $this->self->read( $args ) ){
			$return = array_shift( $array );
			$this->_storage[$id] = $return;
		}

		return $return;
	}

	public function findManyById( array $ids )
	{
		$return = array();
		$notOnCache = FALSE;
		reset( $ids );
		foreach( $ids as $id ){
			if( ! array_key_exists($id, $this->_storage) ){
				$return = NULL;
				break;
			}
			$return[ $id ] = $this->_storage[$id];
		}

		if( $return ){
			return $return;
		}

		$args = array();
		$args[] = array('id', 'IN', $ids);

		$return = $this->self->read( $args );
		return $return;
	}

	public function find()
	{
		$this->_load();

		$return = array();
		foreach( $this->_storage as $shift ){
			if( $this->end && $shift->getStart() >= $this->end ){
				continue;
			}

			if( $this->start && $shift->getEnd() <= $this->start ){
				continue;
			}

			$return[ $shift->getId() ] = $shift;
		}

		return $return;
	}

	protected function _load()
	{
	// if we need to reload
		if(
			( $this->_loadedStart && $this->_loadedStart <= $this->start )
			&&
			( $this->_loadedEnd && $this->_loadedEnd >= $this->end )
			){
			// echo "NO2 NEED TO LOAD!<br>";
			return;
		}

	// adjust borders to include day before and after
		$this->start = $this->t->setDateTimeDb( $this->start )
			->setStartDay()
			->modify('-1 day')
			->formatDateTimeDb()
			;
		$this->end = $this->t->setDateTimeDb( $this->end )
			->setStartDay()
			->modify('+2 days')
			->formatDateTimeDb()
			;

// echo 'RELOAD: ' . $this->start . ' - ' . $this->end . '<br>';
		$args = array();
		if( $this->end ){
			$args[] = array( 'starts_at', '<', $this->end );
		}
		if( $this->start ){
			$args[] = array( 'ends_at', '>', $this->start );
		}

		// if( $this->calendars ){
			// $args[] = array( 'calendar_id', 'IN', array_keys($this->calendars) );
		// }
		// if( $this->employees ){
			// $args[] = array( 'employee_id', 'IN', array_keys($this->employees) );
		// }

		$args[] = array('sort', 'starts_at', 'asc');
		$args[] = array('sort', 'ends_at', 'asc');
		$args[] = array('sort', 'calendar_id', 'asc');
// _print_r( $args );
// exit;
		$results = $this->crud->read( $args );

		$this->_storage = array();
		foreach( $results as $array ){
			$shift = $this->arrayToModel( $array );
			if( ! $shift ){
				continue;
			}

			$shiftId = $shift->getId();
			$this->_storage[ $shiftId ] = $shift;
		}

		$this->_loadedEnd = $this->end ? $this->end : 230001010000;
		$this->_loadedStart = $this->start ? $this->start : 197001010000;
	}

	public function findAllByEmployee( SH4_Employees_Model $employee )
	{
		$args = array();

		$employeeId = $employee->getId();
		$args[] = array( 'employee_id', '=', $employeeId );

		$return = $this->self->read( $args );
		return $return;
	}

	public function findAllByCalendar( SH4_Calendars_Model $calendar )
	{
		$args = array();

		$calendarId = $calendar->getId();
		$args[] = array( 'calendar_id', '=', $calendarId );

		$return = $this->self->read( $args );
		return $return;
	}

	public function read( array $args = array() )
	{
		$hasSort = false;
		foreach( $args as $a ){
			if( isset($a[0]) && ('sort' == $a[0]) ){
				$hasSort = true;
				break;
			}
		}

		if( ! $hasSort ){
			$args[] = array('sort', 'starts_at', 'asc');
			$args[] = array('sort', 'ends_at', 'asc');
		}
		$args[] = array('sort', 'calendar_id', 'asc');

		$return = $this->crud->read( $args );

		$ids = array_keys($return);
		foreach( $ids as $id ){
			$model = $this->arrayToModel( $return[$id] );
			if( ! $model ){
				continue;
			}
			$return[$id] = $model;
		}

		return $return;
	}

	public function arrayToModel( array $array )
	{
		if( array_key_exists($array['calendar_id'], $this->calendars) ){
			$calendar = $this->calendars[ $array['calendar_id'] ];
		}
		else {
			$calendar = $this->calendarsQuery->findById( $array['calendar_id'] );
		}

		if( ! $calendar ){
			return;
		}

		$this->calendars[ $calendar->getId() ] = $calendar;

		$employee = NULL;
		if( isset($array['employee_id']) ){
			if( array_key_exists($array['employee_id'], $this->employees) ){
				$employee = $this->employees[ $array['employee_id'] ];
			}
			else {
				$employee = $this->employeesQuery->findById( $array['employee_id'] );
			}
			if( ! $employee ){
				return;
			}
			$this->employees[ $employee->getId() ] = $employee;
		}

		$start = $array['starts_at'];
		$end = $array['ends_at'];

		$breakStart = array_key_exists('break_starts_at', $array) ? $array['break_starts_at'] : NULL;
		$breakEnd = array_key_exists('break_ends_at', $array) ? $array['break_ends_at'] : NULL;

		$status = array_key_exists('status', $array) ? $array['status'] : NULL;
		$id = array_key_exists('id', $array) ? $array['id'] : NULL;

		$return = new SH4_Shifts_Model( $id, $calendar, $start, $end, $employee, $breakStart, $breakEnd, $status );
		$return->setRawData( $array );

		return $return;
	}
}