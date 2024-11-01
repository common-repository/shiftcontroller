<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
#[AllowDynamicProperties]
class SH4_Conf_Html_Admin_Controller_ShiftStatus
{
	public function __construct(
		HC3_Hooks $hooks,
		HC3_Post $post,
		HC3_Settings $settings
		)
	{
		$this->post = $hooks->wrap($post);
		$this->settings = $hooks->wrap($settings);
	}

	public function execute()
	{
		$take = array(
			'shifts_no_draft', 'shifts_confirm_publish'
			);

		foreach( $take as $k ){
			$v = $this->post->get($k);
			$this->settings->set( $k, $v );
		}

		$return = array( 'admin/conf/shiftstatus', '__Settings Updated__' );
		return $return;
	}
}