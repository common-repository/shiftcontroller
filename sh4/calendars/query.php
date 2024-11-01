<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
interface SH4_Calendars_IQuery
{
	public function countActive();
	public function countArchived();
	public function findAll();
	public function countAll();
	public function findActive();
	public function findById( $id );
	public function findActiveById( $id );
	public function findManyActiveById( array $ids );
	public function findArchived();
}

class SH4_Calendars_Query implements SH4_Calendars_IQuery
{
	protected $_storage = array();

	public $self, $crud;

	public function __construct(
		HC3_Hooks $hooks,
		HC3_CrudFactory $crudFactory
		)
	{
		$this->crud = $hooks->wrap( $crudFactory->make('calendar') );
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

	public function countActive()
	{
		$args = array();
		$args[] = array('status', '=', SH4_Calendars_Model::STATUS_ACTIVE);
		return $this->crud->count( $args );
	}

	public function countArchived()
	{
		$args = array();
		$args[] = array('status', '=', SH4_Calendars_Model::STATUS_ARCHIVE);
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
		return $this->self->read( $args );
	}

	public function findActive()
	{
		$args = array();
		$args[] = array('status', '=', SH4_Calendars_Model::STATUS_ACTIVE);
		return $this->self->read( $args );
	}

	public function findById( $id )
	{
		$return = NULL;

		if( array_key_exists($id, $this->_storage) ){
			$return = $this->_storage[$id];
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

	public function findActiveById( $id )
	{
		$return = NULL;

		$args = array();
		$args[] = array('status', '=', SH4_Calendars_Model::STATUS_ACTIVE);
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
		$args[] = array('status', '=', SH4_Calendars_Model::STATUS_ACTIVE);
		$args[] = array('id', 'IN', $ids);

		$return = $this->self->read( $args );
		return $return;
	}

	public function findManyById( array $ids )
	{
		$ret = [];

		$args = array();
		$args[] = array( 'id', 'IN', $ids );
		$ret = $this->self->read( $args );

		return $ret;
	}

	public function findArchived()
	{
		$args = array();
		$args[] = array('status', '=', SH4_Calendars_Model::STATUS_ARCHIVE);
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
		$color = array_key_exists('color', $array) ? $array['color'] : NULL;
		$status = array_key_exists('status', $array) ? $array['status'] : NULL;
		$description = array_key_exists('description', $array) ? $array['description'] : NULL;

		$type = SH4_Calendars_Model::TYPE_SHIFT;
		if( array_key_exists('calendar_type', $array) ){
			$type = $array['calendar_type'];
		}

		$thisSortOrder = 0;
		if( $id ){
			$thisSortOrder = (array_key_exists('show_order', $array) && $array['show_order']) ? $array['show_order'] : $sortOrder;
			$sortOrder = $thisSortOrder + 1;
		}

		$return = new SH4_Calendars_Model( $id, $title, $status, $color, $description, $type, $thisSortOrder );
		return $return;
	}
}
