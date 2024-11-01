<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class SH4_App_Acl
{
	public $appQuery, $auth, $permission;

	public function __construct(
		HC3_Hooks $hooks,

		SH4_App_Query $appQuery,

		HC3_Auth $auth,
		HC3_IPermission $permission
		)
	{
		$this->appQuery = $hooks->wrap( $appQuery );
		$this->auth = $hooks->wrap( $auth );
		$this->permission = $hooks->wrap( $permission );
	}

	public function checkUserProfile()
	{
		$haveRoles = false;

		$user = $this->auth->getCurrentUser();

	// admin?
		if( ! $haveRoles ){
			if( $this->permission->isAdmin($user) ){
				$haveRoles = true;
			}
		}

	// manager?
		if( ! $haveRoles ){
			$calendars = $this->appQuery->findCalendarsManagedByUser( $user );
			if( $calendars ){
				$haveRoles = true;
			}
		}

	// viewer?
		if( ! $haveRoles ){
			$calendars = $this->appQuery->findCalendarsViewedByUser( $user );
			if( $calendars ){
				$haveRoles = true;
			}
		}

	// employee?
		if( ! $haveRoles ){
			$employee = $this->appQuery->findEmployeeByUser( $user );
			if( $employee ){
				$haveRoles = true;
			}
		}

		$ret = $haveRoles ? true : false;
		return $ret;
	}

	public function checkUser()
	{
		$return = FALSE;

		$currentUser = $this->auth->getCurrentUser();
		$currentUserId = $currentUser->getId();
		if( ! $currentUserId ){
			return $return;
		}

		$return = TRUE;
		return $return;
	}

	public function checkAdmin()
	{
		$return = FALSE;

		$currentUser = $this->auth->getCurrentUser();
		$currentUserId = $currentUser->getId();

		if( ! $currentUserId ){
			return $return;
		}

		if( $this->permission->isAdmin($currentUser) ){
			$return = TRUE;
		}

		return $return;
	}

	public function checkManager()
	{
		$return = FALSE;

		$currentUser = $this->auth->getCurrentUser();
		$currentUserId = $currentUser->getId();
		if( ! $currentUserId ){
			return $return;
		}

		$calendars = $this->appQuery->findCalendarsManagedByUser( $currentUser );
		if( ! $calendars ){
			return $return;
		}

		$return = TRUE;

		return $return;
	}

	public function checkEmployee()
	{
		$return = FALSE;

		$currentUser = $this->auth->getCurrentUser();
		$currentUserId = $currentUser->getId();
		if( ! $currentUserId ){
			return $return;
		}

		$employee = $this->appQuery->findEmployeeByUser( $currentUser );
		if( ! $employee ){
			return $return;
		}

		$return = TRUE;

		return $return;
	}
}