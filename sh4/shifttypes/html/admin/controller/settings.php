<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class SH4_ShiftTypes_Html_Admin_Controller_Settings
{
	public $post, $settings;

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
		$take = array( 'shifttypes_show_title', 'shifttypes_nobreak', 'shifttypes_default_duration' );

		foreach( $take as $k ){
			$this->settings->set( $k, $this->post->get($k) );
		}

		$k = 'shifttypes_nobreak';
		$v = $this->post->get($k);
		if( $v ){
			$this->settings->set( $k, 1 );
		}
		else {
			$this->settings->reset( $k );
		}

		$return = array( 'admin/shifttypes', '__Settings Updated__' );
		return $return;
	}
}