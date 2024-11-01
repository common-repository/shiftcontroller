<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
#[AllowDynamicProperties]
class SH4_New_Controller_Common
{
	public function __construct(
		HC3_Hooks $hooks,
		HC3_Post $post,
		HC3_Notificator $notificator
		)
	{
		$this->self = $hooks->wrap( $this );

		$this->post = $hooks->wrap( $post );
		$this->notificator = $notificator;
	}

	public function beforeExecute()
	{
		$isOff = $this->post->get('notifications_turnoff');
		if( $isOff ){
			$this->notificator->setOff();
		}
		else {
			$this->notificator->setOn();
		}
	}

	public function afterExecute( $ids )
	{
	}
}
