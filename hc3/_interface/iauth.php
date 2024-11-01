<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
interface HC3_IAuth
{
	public function getCurrentUserId();
	public function getCurrentUser();

	public function getUserByToken( $token );
	public function getTokenByUser( HC3_Users_Model $user );

	// public function findByToken( $token )
	// {
	// 	$return = NULL;

	// 	$args = array();
	// 	$args[] = array('token', '=', $token);

	// 	if( $array = $this->self->read( $args ) ){
	// 		$return = array_shift( $array );
	// 	}
	// 	return $return;
	// }

}