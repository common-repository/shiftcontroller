<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class SH4_Schedule_Html_Controller_ExactTime
{
	public $t, $post;

	public function __construct(
		HC3_Time $t,
		HC3_Hooks $hooks,
		HC3_Post $post
		)
	{
		$this->t = $t;
		$this->post = $hooks->wrap($post);
	}

	public function execute()
	{
		$p = array();

		$date = $this->post->get('date');
		$time = $this->post->get('time');

		$dateTimeDb = $this->t->setDateDb( $date )
			->modify( '+' . $time . ' seconds' )
			->formatDateTimeDb()
		;

		$p['start'] = null;
		$p['end'] = null;
		$p['time'] = $dateTimeDb;

		$ret = array( array('-referrer-', $p), null );
		return $ret;
	}

	public function executeMy()
	{
		$p = array();

		$date = $this->post->get('date');
		$time = $this->post->get('time');

		$dateTimeDb = $this->t->setDateDb( $date )
			->modify( '+' . $time . ' seconds' )
			->formatDateTimeDb()
		;

		$p['start'] = null;
		$p['end'] = null;
		$p['time'] = $dateTimeDb;

		$ret = array( array('-referrer-', $p), null );
		return $ret;
	}
}