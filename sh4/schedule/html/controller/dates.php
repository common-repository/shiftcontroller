<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class SH4_Schedule_Html_Controller_Dates
{
	public $post;

	public function __construct(
		HC3_Hooks $hooks,
		HC3_Post $post
		)
	{
		$this->post = $hooks->wrap($post);
	}

	public function execute()
	{
		$p = array();

		$start = $this->post->get('start');
		$p['start'] = $start;

		$end = $this->post->get('end');
		if( $end ){
			$p['end'] = $end;
		}

		$p['time'] = null;

		// $ret = array( array('-referrer-', $p), null );
		$ret = array( array('schedule', $p), null );

		return $ret;
	}

	public function executeMy()
	{
		$p = array();

		$start = $this->post->get('start');
		$p['start'] = $start;

		$end = $this->post->get('end');
		if( $end ){
			$p['end'] = $end;
		}

		$p['time'] = null;

		// $ret = array( array('-referrer-', $p), null );
		$ret = array( array('myschedule', $p), null );

		return $ret;
	}
}