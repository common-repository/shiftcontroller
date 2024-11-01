<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class HC3_Users_Query implements HC3_Users_IQuery
{
	protected $crud;
	protected $self;

	public function __construct( HC3_Hooks $hooks )
	{
		$this->crud = new HC3_Crud_Wordpress_User();
		$this->self = $hooks->wrap( $this );
	}

	public function findByEmail( $email )
	{
		$return = NULL;

		$args = array();
		$args[] = array('email', '=', $email);
		$args[] = array('limit', 1);

		if( $array = $this->self->read( $args ) ){
			$return = array_shift( $array );
		}
		return $return;
	}

	public function findById( $id )
	{
		$return = NULL;

		if( ! $id ){
			$array = array();
			$array['display_name'] = '-Anonymous-';
			$array['username'] = 'anonymous';
			$array['email'] = NULL;

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
		$return = array();
		$args = array();
		$args[] = array('id', 'IN', $ids);

		$return = $this->self->read( $args );
		return $return;
	}

	public function findAll()
	{
		$args = array();
		return $this->self->read( $args );
	}

	public function read( array $args = array() )
	{
		$args[] = array( 'sort', 'display_name', 'asc' );

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

		$displayName = $array['display_name'];
		$username = $array['username'];
		$email = $array['email'];

		$return = new HC3_Users_Model( $id, $username, $email, $displayName );
		return $return;
	}
}