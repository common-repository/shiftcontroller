<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
interface SH4_ShiftTypes_IQuery
{
	public function findAll();
	public function findManyById( array $ids );
	public function findById( $id );
}

class SH4_ShiftTypes_Query implements SH4_ShiftTypes_IQuery
{
	public $crud, $calendarsQuery, $shiftTypesPresenter, $self;

	public function __construct(
		HC3_Hooks $hooks,
		HC3_CrudFactory $crudFactory,
		SH4_Calendars_Query $calendarsQuery,
		SH4_ShiftTypes_Presenter $shiftTypesPresenter
	)
	{
		$this->crud = $hooks->wrap( $crudFactory->make('shifttype') );
		$this->calendarsQuery = $hooks->wrap( $calendarsQuery );
		$this->shiftTypesPresenter = $hooks->wrap( $shiftTypesPresenter );
		$this->self = $hooks->wrap( $this );
	}

	public function findById( $id )
	{
		$return = NULL;
		if( strpos($id, '-') ){
			$parts = explode( '-', $id );
			$start = array_shift( $parts );
			$end = array_shift( $parts );

			$array = array();
			$array['id'] = 0;
			$array['title'] = '- ' . '__Custom Time__' . ' -';
			$array['starts_at'] = $start;
			$array['ends_at'] = $end;

			if( $parts ){
				$break_start = array_shift( $parts );
				$break_end = array_shift( $parts );
				$array['break_starts_at'] = $break_start;
				$array['break_ends_at'] = $break_end;
			}

			$return = $this->_arrayToModel( $array );
			return $return;
		}
		elseif( $id == 0 ){
			$array = array();
			$array['id'] = 0;
			$array['title'] = '- ' . '__Custom Time__' . ' -';
			$array['starts_at'] = NULL;
			$array['ends_at'] = NULL;

			$return = $this->_arrayToModel( $array );
			return $return;
		}

		$args = array();
		$args[] = array('id', '=', $id);
		$args[] = array('limit', 1);

		if( $array = $this->self->read( $args ) ){
			$return = array_shift( $array );
		}
		return $return;
	}

	public function findManyById( array $ids )
	{
		$return = NULL;

		$args = array();
		$args[] = array('id', 'IN', $ids);

		$return = $this->self->read( $args );

		if( in_array(0, $ids) ){
			$return = $return + array( 0 => $this->self->findById(0) );
		}

		return $return;
	}

	public function findAll()
	{
		$args = array();

		$return = $this->self->read( $args );
		$return = $return + array( 0 => $this->self->findById(0) );

		return $return;
	}

	public function read( array $args = array() )
	{
		$args[] = array( 'sort', 'range', 'desc' );
		$args[] = array( 'sort', 'starts_at', 'asc' );
		$args[] = array( 'sort', 'ends_at', 'asc' );

		$return = $this->crud->read( $args );

		$ids = array_keys($return);
		foreach( $ids as $id ){
			$return[$id] = $this->_arrayToModel( $return[$id] );
		}

		return $return;
	}

	protected function _arrayToModel( array $array )
	{
		$id = array_key_exists('id', $array) ? $array['id'] : NULL;

		$range = array_key_exists('range', $array) ? $array['range'] : SH4_ShiftTypes_Model::RANGE_HOURS;
		$title = $array['title'];
		$start = $array['starts_at'];
		$end = $array['ends_at'];

		$breakStart = array_key_exists('break_starts_at', $array) ? $array['break_starts_at'] : NULL;
		$breakEnd = array_key_exists('break_ends_at', $array) ? $array['break_ends_at'] : NULL;

		$return = new SH4_ShiftTypes_Model( $id, $title, $range, $start, $end, $breakStart, $breakEnd );
		return $return;
	}
}