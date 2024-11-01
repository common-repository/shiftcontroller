<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
interface SH4_Schedule_IAcl
{
	public function checkMy( $params = array() );
}

class SH4_Schedule_Acl implements SH4_Schedule_IAcl
{
	public $self, $appQuery, $auth;

	public function __construct(
		HC3_Hooks $hooks,

		SH4_App_Query $appQuery,
		HC3_Auth $auth
		)
	{
		$this->self = $hooks->wrap( $this );

		$this->appQuery = $hooks->wrap( $appQuery );
		$this->auth = $hooks->wrap( $auth );
	}

	public function checkMy( $params = array() )
	{
		$return = FALSE;

		$currentUser = $this->auth->getCurrentUser();
		$currentUserId = $currentUser->getId();
		if( ! $currentUserId ){
			return $return;
		}

		$currentUserId = $currentUser->getId();
		if( ! $currentUserId ){
			return $return;
		}

	// as employee
		$meEmployee = $this->appQuery->findEmployeeByUser( $currentUser );
		if( ! $meEmployee ){
			return $return;
		}

		$return = TRUE;
		return $return;
	}
}