<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
#[AllowDynamicProperties]
class SH4_Conf_Html_Admin_Controller_Email
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
		$take = array( 'email_from', 'email_fromname', 'email_html' );

		foreach( $take as $k ){
			$this->settings->set( $k, $this->post->get($k) );
		}

		$return = array( 'admin/conf/email', '__Settings Updated__' );
		return $return;
	}
}