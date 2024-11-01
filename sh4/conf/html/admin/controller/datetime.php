<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
#[AllowDynamicProperties]
class SH4_Conf_Html_Admin_Controller_Datetime
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
			'datetime_date_format', 'datetime_time_format', 'datetime_week_starts', 'full_day_count_as',
			'datetime_min_time', 'datetime_max_time', 'datetime_step', 'datetime_timezone',
			'datetime_hide_schedule_reports', 'datetime_hide_bottom_date_navigation', 'datetime_month_slim_view', 'datetime_compact_shift_label',
			'default_schedule_view', 'default_schedule_groupby', 'datetime_n_weeks',
			'pref_csv_separator'
			);

		foreach( $take as $k ){
			$v = $this->post->get($k);
			$this->settings->set( $k, $v );
		}

		$k = 'skip_weekdays';
		$v = $this->post->get($k);
		if( $v ){
			$this->settings->set( $k, $v );
		}
		else {
			$this->settings->reset( $k );
		}

		$return = array( 'admin/conf/datetime', '__Settings Updated__' );
		return $return;
	}
}