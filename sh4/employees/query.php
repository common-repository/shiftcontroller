<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
interface SH4_Employees_IQuery
{
	public function countActive();
	public function countArchived();
	public function findAll();
	public function countAll();
	public function findActive();
	public function findById( $id );
	public function findManyById( array $ids );
	public function findActiveById( $id );
	public function findManyActiveById( array $ids );
	public function findArchived();
}

class SH4_Employees_Query implements SH4_Employees_IQuery
{
	protected $_storage = array();
	public $self, $crud;

	public function __construct(
		HC3_Hooks $hooks,
		HC3_CrudFactory $crudFactory
		)
	{
		$crudFactory = $hooks->wrap( $crudFactory );

		$this->crud = $hooks->wrap( $crudFactory->make('employee') );
		$this->self = $hooks->wrap( $this );
	}

	public function _sort( $a, $b )
	{
		$c1 = $a->getSortOrder();
		$c2 = $b->getSortOrder();

		if( $c1 != $c2 ){
			return ( $c1 > $c2 ) ? 1 : -1;
		}

		$c1 = $a->getTitle();
		$c2 = $b->getTitle();
		$c1 = strtolower($c1);
		$c2 = strtolower($c2);

		$ret = strcmp( $c1, $c2 );
		return $ret;
	}

	public function findById( $id )
	{
		$return = NULL;

		if( array_key_exists($id, $this->_storage) ){
			$return = $this->_storage[$id];
			return $return;
		}

		if( $id == 0 ){
			$array = array();
			$array['id'] = 0;
			$array['title'] = '-' . '__Open Shift__' . '-';

			$return = $this->_arrayToModel( $array );
			return $return;
		}

		if( $id == -1 ){
			$array = array();
			$array['id'] = -1;
			$array['title'] = '-' . '__Assigned Shift__' . '-';

			$return = $this->_arrayToModel( $array );
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

		if( ! $ids ){
			return $return;
		}

		$args = array();
		$args[] = array('id', 'IN', $ids);

		$return = $this->self->read( $args );

		return $return;
	}

	public function findActiveById( $id )
	{
		$return = NULL;

		if( $id == 0 ){
			$return = $this->self->findById($id);
			return $return;
		}

		if( array_key_exists($id, $this->_storage) ){
			$return = $this->_storage[$id];
			return $return;
		}

		$args = array();
		$args[] = array('status', '=', SH4_Employees_Model::STATUS_ACTIVE);
		$args[] = array('id', '=', $id);
		$args[] = array('limit', 1);

		if( $array = $this->self->read( $args ) ){
			$return = array_shift( $array );
		}
		return $return;
	}

	public function findManyActiveById( array $ids )
	{
		$return = NULL;

		$args = array();
		$args[] = array('status', '=', SH4_Employees_Model::STATUS_ACTIVE);
		$args[] = array('id', 'IN', $ids);

		$return = $this->self->read( $args );

		if( in_array(0, $ids) ){
			$return = array( 0 => $this->self->findById(0) ) + $return;
		}

		return $return;
	}

	public function countActive()
	{
		$args = array();
		$args[] = array('status', '=', SH4_Employees_Model::STATUS_ACTIVE);
		return $this->crud->count( $args );
	}

	public function countArchived()
	{
		$args = array();
		$args[] = array('status', '=', SH4_Employees_Model::STATUS_ARCHIVE);
		return $this->crud->count( $args );
	}

	public function countAll()
	{
		$args = array();
		return $this->crud->count( $args );
	}

	public function findAll()
	{
		$args = array();

		$return = $this->self->read( $args );
		$return = array( 0 => $this->self->findById(0) ) + $return;

		return $return;
	}

	public function findActive()
	{
		$args = array();
		$args[] = array('status', '=', SH4_Employees_Model::STATUS_ACTIVE);

		$return = $this->self->read( $args );
		$return = array( 0 => $this->self->findById(0) ) + $return;
		return $return;
	}

	public function findArchived()
	{
		$args = array();
		$args[] = array('status', '=', SH4_Employees_Model::STATUS_ARCHIVE);
		return $this->self->read( $args );
	}

	public function read( array $args = array() )
	{
		$args[] = array( 'sort', 'title', 'asc' );

		$return = $this->crud->read( $args );
		$ids = array_keys($return);

		foreach( $ids as $id ){
			$return[$id] = $this->_arrayToModel( $return[$id] );
			$this->_storage[$id] = $return[$id];
		}
		uasort( $return, array($this, '_sort') );

		return $return;
	}

	protected function _arrayToModel( array $array )
	{
		static $sortOrder = 1;

		$id = array_key_exists('id', $array) ? $array['id'] : NULL;
		$title = $array['title'];
		$status = array_key_exists('status', $array) ? $array['status'] : NULL;
		$description = array_key_exists('description', $array) ? $array['description'] : NULL;

		$thisSortOrder = 0;
		if( $id ){
			$thisSortOrder = (array_key_exists('show_order', $array) && $array['show_order']) ? $array['show_order'] : $sortOrder;
			$sortOrder = $thisSortOrder + 1;
		}

		$return = new SH4_Employees_Model( $id, $title, $description, $status, $thisSortOrder );
		return $return;
	}
}