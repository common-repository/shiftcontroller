<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
#[AllowDynamicProperties]
class SH4_Notifications_Html_Admin_Controller_TurnOnOff
{
	public function __construct(
		HC3_Hooks $hooks,
		HC3_Post $post,
		HC3_Notificator $notificator
		)
	{
		$this->post = $post;
		$this->notificator = $notificator;
	}

	public function execute()
	{
		$isOff = $this->post->get('notifications_turnoff');
		if( $isOff ){
			$this->notificator->setOff();
		}
		else {
			$this->notificator->setOn();
		}

		$to = '-referrer-';
		$msg = NULL;
		$return = array( $to, $msg );

		return $return;
	}
}