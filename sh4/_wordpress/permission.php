<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class SH4_Permission implements HC3_IPermission
{
	protected $_adminIds = array();
	protected $_adminRoles = array( 'administrator', 'developer', 'sh4_admin' );

	public $usersQuery;

	public function __construct(
		HC3_Hooks $hooks,
		HC3_Users_Query $usersQuery
		)
	{
		$this->usersQuery = $hooks->wrap( $usersQuery );
	}

	public function isAdmin( HC3_Users_Model $user )
	{
		$return = FALSE;

		$id = $user->getId();

		if( in_array($id, $this->_adminIds) ){
			$return = TRUE;
			return $return;
		}

		$wpUser = get_userdata( $id );

		if( ! isset($wpUser->roles) ){
			return $return;
		}

		$thisRoles = $wpUser->roles;
		if( array_intersect($this->_adminRoles, $thisRoles) ){
			$return = TRUE;
		}

		return $return;
	}

	public function findAdmins()
	{
		$args = array();
		$args[] = array('role', 'IN', $this->_adminRoles);

		$return = $this->usersQuery->read( $args );

		if( $this->_adminIds ){
			$args = array();
			$args[] = array('id', '=', $this->_adminIds);
			$return2 = $this->usersQuery->read( $args );
			$return = array_merge( $return, $return2 );
		}

		return $return;
	}
}