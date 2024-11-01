<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
#[AllowDynamicProperties]
class SH4_Notifications_Html_User_Controller_Profile_Notifications
{
	public function __construct(
		HC3_Hooks $hooks,
		HC3_Post $post,

		HC3_Auth $auth,
		HC3_Notificator $notificator
		)
	{
		$this->post = $hooks->wrap($post);

		$this->auth = $hooks->wrap( $auth );
		$this->notificator = $hooks->wrap( $notificator );
	}

	public function execute()
	{
		$user = $this->auth->getCurrentUser();

		$turnoff = $this->post->get('turnoff');

		if( $turnoff ){
			$this->notificator->setOffForUser( $user );
			$msg = '__Notifications Turned Off__';
		}
		else {
			$this->notificator->setOnForUser( $user );
			$msg = '__Notifications Turned On__';
		}

		$return = array( 'user/profile', $msg );
		return $return;
	}
}