<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
interface SH4_Reminders_QueryLog_
{
	public function findAll();
}

class SH4_Reminders_QueryLog implements SH4_Reminders_QueryLog_
{
	protected $_storage = array();
	public $self, $crud;

	public function __construct(
		HC3_Hooks $hooks,
		HC3_CrudFactory $crudFactory
		)
	{
		$crudFactory = $hooks->wrap( $crudFactory );

		$this->crud = $hooks->wrap( $crudFactory->make('reminderlog') );
		$this->self = $hooks->wrap( $this );
	}

	public function findAll()
	{
		$args = array();
		$ret = $this->self->read( $args );
		return $ret;
	}

	public function read( array $args = array() )
	{
		$args[] = array( 'sort', 'date', 'asc' );

		$ret = $this->crud->read( $args );
		$ids = array_keys( $ret );

		foreach( $ids as $id ){
			$ret[$id] = $this->_arrayToModel( $ret[$id] );
			// $this->_storage[$id] = $return[$id];
		}
		// uasort( $return, array($this, '_sort') );

		return $ret;
	}

	protected function _arrayToModel( array $array )
	{
		static $sortOrder = 1;

		$id = array_key_exists('id', $array) ? $array['id'] : NULL;
		$range = array_key_exists('range', $array) ? $array['range'] : NULL;
		$date = array_key_exists('date', $array) ? $array['date'] : NULL;
		$employee_id = array_key_exists('employee_id', $array) ? $array['employee_id'] : NULL;
		$sent_on = array_key_exists('sent_on', $array) ? $array['sent_on'] : NULL;

		$ret = new SH4_Reminders_ModelLog;

		$ret->id = $id;
		$ret->range = $range;
		$ret->date = $date;
		$ret->employee_id = $employee_id;
		$ret->sent_on = $sent_on;

		return $ret;
	}
}