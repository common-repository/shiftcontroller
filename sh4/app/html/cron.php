<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class SH4_App_Html_Cron
{
	public $self, $cron;

	public function __construct(
		HC3_Cron $cron,
		HC3_Hooks $hooks
		)
	{
		$this->cron = $hooks->wrap( $cron );
		$this->self = $hooks->wrap( $this );
	}

	public function run()
	{
		// $f = realpath( __DIR__ . '/../../../_cron.txt' );
		// echo __METHOD__ . " = " . $f . ' XOM';
		$this->cron->run();
	}
}