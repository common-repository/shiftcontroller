<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class SH4_Shifts_DurationService
{
	public $settings, $t;

	public function __construct(
		HC3_Settings $settings,
		HC3_Time $t
		)
	{
		$this->settings = $settings;
		$this->t = $t;
	}

	public function newCounter()
	{
		$return = new SH4_Shifts_Duration( $this->settings, $this->t );
		return $return;
	}
}