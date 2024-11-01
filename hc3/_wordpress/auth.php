<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class HC3_Auth implements HC3_IAuth
{
	public $self, $hooks, $usersQuery;

	public function __construct(
		HC3_Hooks $hooks,
		HC3_Users_Query $usersQuery
		)
	{
		$this->hooks = $hooks;
		$this->self = $hooks->wrap( $this );
		$this->usersQuery = $hooks->wrap( $usersQuery );
	}

	public function getCurrentUserId()
	{
		$return = get_current_user_id();
		return $return;
	}

	public function getCurrentUser()
	{
		$id = $this->self->getCurrentUserId();
		$return = $this->usersQuery->findById( $id );
		if( ! $return ){
			$return = $this->usersQuery->findById(0);
		}
		return $return;
	}

	public function getUserByToken( $token )
	{
		$return = NULL;

		$args = array();
		$prefix = $this->hooks->getPrefix();
		$metaName = $prefix . 'token';
		$args[] = array( $metaName, '=', $token );
		$args[] = array( 'limit', 1 );

		$users = $this->usersQuery->read( $args );
		if( $users ){
			$return = array_shift( $users );
		}

		return $return;
	}

	public function getTokenByUser( HC3_Users_Model $user )
	{
		$return = NULL;

		$id = $user->getId();
		if( $id ){
			$prefix = $this->hooks->getPrefix();
			$metaName = $prefix . 'token';
			$return = get_user_meta( $id, $metaName, TRUE );

			if( ! strlen($return) ){
				$return = HC3_Functions::generateRand(12);
				update_user_meta( $id, $metaName, $return );
			}
		}

		return $return;
	}

}