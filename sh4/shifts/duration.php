<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
interface SH4_Shifts_IDuration
{
	public function reset();
	public function add( SH4_Shifts_Model $shift );
	public function getQty();
	public function getDuration();
	public function getDurationHours();
	public function formatDuration();
}

class SH4_Shifts_Duration implements SH4_Shifts_IDuration
{
	protected $qty = 0;
	protected $duration = 0;
	protected $shifts = array();
	protected $qtyTimeoff = 0;
	protected $durationTimeoff = 0;
	protected $settings;
	protected $t;

	public function __construct(
		HC3_Settings $settings,
		HC3_Time $t
		)
	{
		$this->settings = $settings;
		$this->t = $t;
	}

	public function reset()
	{
		$this->qty = 0;
		$this->duration = 0;
		$this->qtyTimeoff = 0;
		$this->durationTimeoff = 0;
		$this->shifts = array();
		return $this;
	}

	public function add( SH4_Shifts_Model $model )
	{
		$id = $model->getId();
		if( isset($this->shifts[$id]) ){
			return $this;
		}
		$this->shifts[$id] = $id;

		$start = $model->getStart();
		$end = $model->getEnd();

		$this->t->setDateTimeDb( $start );
		$startTs = $this->t->getTimestamp();
		$startDayStart = $this->t->setStartDay()->getTimestamp();
		$startDate = $this->t->formatDateDb();
		$startDayTs = $startTs - $startDayStart;

		$this->t->setDateTimeDb( $end );
		$endTs = $this->t->getTimestamp();
		$endDayStart = $this->t->setStartDay()->getTimestamp();
		$endDate = $this->t->formatDateDb();
		$endDayTs = $endTs - $endDayStart;

		$duration = abs( $endTs - $startTs );
		if( ($startDayTs == 0) && ($endDayTs == 0) ){
			$fullDayCountAs = $this->settings->get('full_day_count_as');

			// $durationDays = $duration / (24 * 60 * 60);

			$durationDays = 0;
			$rexDate = $startDate;
			$this->t->setDateDb( $rexDate );
			while( $rexDate < $endDate ){
				$durationDays++;
				$rexDate = $this->t
					->modify( '+1 day' )
					->formatDateDb()
					;
			}

			$duration = $durationDays * $fullDayCountAs;
			// echo "DD = '$durationDays'<br>";
		}

		$noBreak = $this->settings->get( 'shifttypes_nobreak' );
		if( ! $noBreak ){
			$breakStart = $model->getBreakStart();
			$breakEnd = $model->getBreakEnd();

			if( ! ((NULL === $breakStart) && (NULL === $breakEnd)) ){
				$this->t->setDateTimeDb( $breakStart );
				$breakStartTs = $this->t->getTimestamp();
				$this->t->setDateTimeDb( $breakEnd );
				$breakEndTs = $this->t->getTimestamp();

				$breakDuration = abs( $breakEndTs - $breakStartTs );
				$duration = $duration - $breakDuration;
			}
		}

		$this->qty++;
		$this->duration += $duration;

	// is timeoff
		$calendar = $model->getCalendar();
		if( $calendar->isTimeoff() ){
			$this->qtyTimeoff++;
			$this->durationTimeoff += $duration;
		}

		return $this;
	}

	public function getDuration()
	{
		return $this->duration;
	}

	public function getDurationTimeoff()
	{
		return $this->durationTimeoff;
	}

	public function getDurationHours()
	{
		$return = $this->getDuration();
		$return = $return / (60 * 60);
		$return = sprintf( '%0.2f', $return );
		return $return;
	}

	public function formatDuration( $duration = NULL )
	{
		if( NULL === $duration ){
			$duration = $this->getDuration();
		}

		$hours = floor( $duration / (60 * 60) );
		$remain = $duration - $hours * (60 * 60);
		$minutes = floor( $remain / 60 );

		$hoursView = $hours;
		$minutesView = sprintf( '%02d', $minutes );

		$return = $hoursView . ':' . $minutesView;
		// $return = gmdate( "H:i", $this->getDuration() );
		return $return;
	}

	public function getQty()
	{
		return $this->qty;
	}

	public function getQtyTimeoff()
	{
		return $this->qtyTimeoff;
	}
}