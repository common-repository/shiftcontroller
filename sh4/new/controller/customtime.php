<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
#[AllowDynamicProperties]
class SH4_New_Controller_CustomTime
{
	public function __construct(
		HC3_Hooks $hooks,

		HC3_Request $request,
		HC3_Post $post,

		HC3_Time $t
		)
	{
		$this->t = $t;
		$this->request = $request;
		$this->post = $hooks->wrap( $post );

		$this->self = $hooks->wrap($this);
	}

	public function execute( $calendarId )
	{
		$thisId = array();

		$params = $this->request->getParams();

		$time = $this->post->get('time');
		list( $start, $end ) = explode('-', $time);
		$thisId[] = $start;
		$thisId[] = $end;

		$startBreak = $endBreak = NULL;
		$breakOn = $this->post->get('break_on');
		if( $breakOn ){
			$break = $this->post->get('break');
			list( $startBreak, $endBreak ) = explode( '-', $break );
		}

		if( $breakOn ){
			$errors = array();

			if( ($end > 24*60*60) && ($startBreak < $start) ){
				$startBreak = 24*60*60 + $startBreak;
				$endBreak = 24*60*60 + $endBreak;
			}

			if( ($startBreak >= $end) OR ($startBreak < $start) ){
				$msg = '__Lunch break should be within shift hours.__';
				$errors['break'] = $msg;
			}
			if( ($endBreak > $end) OR ($endBreak <= $start) ){
				$msg = '__Lunch break should be within shift hours.__';
				$errors['break'] = $msg;
			}

			if( $errors ){
				$return = array( '-referrer-', $errors, TRUE );
				return $return;
			}
		}

		if( $breakOn ){
			$thisId[] = $startBreak;
			$thisId[] = $endBreak;
		}

		$thisId = join('-', $thisId);

		$to = 'new';
		$toParams = $params;
		$toParams['calendar'] = $calendarId;
		$toParams['shifttype'] = $thisId;
		$to = array( $to, $toParams );

		$return = array( $to, NULL );
		return $return;
	}
}
