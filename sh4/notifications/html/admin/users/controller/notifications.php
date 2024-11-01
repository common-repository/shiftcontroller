<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
#[AllowDynamicProperties]
class SH4_Notifications_Html_Admin_Users_Controller_Notifications
{
	public function __construct(
		HC3_Hooks $hooks,
		HC3_Post $post,

		HC3_Users_Query $usersQuery,
		HC3_Notificator $notificator
		)
	{
		$this->post = $hooks->wrap($post);

		$this->usersQuery = $hooks->wrap( $usersQuery );
		$this->notificator = $hooks->wrap( $notificator );
	}

	public function executeOn( $userId )
	{
		$user = $this->usersQuery->findById( $userId );
		$this->notificator->setOnForUser( $user );
		$msg = '__Notifications Turned On__';

		$to = '-referrer-';
		$return = array( $to, $msg );
		return $return;
	}

	public function executeOff( $userId )
	{
		$user = $this->usersQuery->findById( $userId );
		$this->notificator->setOffForUser( $user );
		$msg = '__Notifications Turned Off__';

		$to = '-referrer-';
		$return = array( $to, $msg );
		return $return;
	}
}