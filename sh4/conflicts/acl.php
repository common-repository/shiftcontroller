<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
interface SH4_Conflicts_IAcl
{
	public function checkView( $shiftId );
}

class SH4_Conflicts_Acl implements SH4_Conflicts_IAcl
{
	public $self, $auth, $shiftsQuery;

	public function __construct(
		HC3_Hooks $hooks,
		SH4_Shifts_Query $shiftsQuery,
		HC3_Auth $auth
		)
	{
		$this->self = $hooks->wrap( $this );
		$this->auth = $hooks->wrap( $auth );
		$this->shiftsQuery = $hooks->wrap( $shiftsQuery );
	}

	public function checkView( $shiftId )
	{
		$return = FALSE;

		$shift = $this->shiftsQuery->findById( $shiftId );
		if( ! ($shift && $shift->getId()) ){
			return $return;
		}

		$currentUser = $this->auth->getCurrentUser();
		$currentUserId = $currentUser->getId();
		if( ! $currentUserId ){
			return $return;
		}

		$return = TRUE;
		return $return;
	}
}