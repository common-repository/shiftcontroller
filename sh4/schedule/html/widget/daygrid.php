<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
interface SH4_Schedule_Html_Widget_IDayGrid
{
	public function reset();
	public function setRange( $timeMin, $timeMax );
	public function add( $start, $end, $content );
	public function render();
}

class SH4_Schedule_Html_Widget_DayGrid implements SH4_Schedule_Html_Widget_IDayGrid
{
	protected $timeMin = 0;
	protected $timeMax = 86400;
	protected $slots = array();
	public $self, $ui, $t;

	public function __construct(
		HC3_Hooks $hooks,
		HC3_Time $t,
		HC3_Ui $ui
		)
	{
		$this->self = $hooks->wrap( $this );
		$this->ui = $ui;
		$this->t = $t;
	}

	public function reset()
	{
		$this->slots = array();
		return $this;
	}

	public function setRange( $timeMin, $timeMax )
	{
		$this->timeMin = $this->t->setDateTimeDb( $timeMin )->getTimestamp();
		$this->timeMax = $this->t->setDateTimeDb( $timeMax )->getTimestamp();
	}

	public function add( $start, $end, $content )
	{
		$slot = new stdClass();

		$start = $this->t->setDateTimeDb( $start )->getTimestamp();
		$end = $this->t->setDateTimeDb( $end )->getTimestamp();

		if( ($end <= $this->timeMin) OR ($start >= $this->timeMax) ){
			return;
		}

		if( $start < $this->timeMin ){
			$start = $this->timeMin;
		}

		if( $end > $this->timeMax ){
			$end = $this->timeMax;
		}

		$slot->start	= $start;
		$slot->end		= $end;
		$slot->content	= $content;
		$slot->offset	= 0;

		$this->slots[] = $slot;
	}

	public function render()
	{
	/* slots */
		$thisLength = $this->timeMax - $this->timeMin;
		$top = 0;

		$slots = $this->slots;

	/* split by rows */
		$rows = array();
		foreach( $slots as $slot ){
			/* find suitable row */
			$myRow = count($rows);
			for( $ri = 0; $ri < count($rows); $ri++ ){
				$failedRow = FALSE;
				foreach( $rows[$ri] as $checkSlot ){
					if( 
						( $checkSlot->start < $slot->end ) &&
						( $checkSlot->end > $slot->start )
						){
						$failedRow = TRUE;
					}
					if( $failedRow ){
						break;
					}
				}
				if( ! $failedRow ){
					$myRow = $ri;
					break;
				}
			}
			if( ! isset($rows[$myRow]) ){
				$rows[$myRow] = array();
			}
			$rows[$myRow][] = $slot;
		}

	/* add offset */
		for( $ri = 0; $ri < count($rows); $ri++ ){
			for( $si = 0; $si < count($rows[$ri]); $si++ ){
				$checkWith = $si ? $rows[$ri][$si-1]->end : $this->timeMin;
				$offset = $rows[$ri][$si]->start - $checkWith;
				$rows[$ri][$si]->offset = $offset;
			}
		}

		$out = array();
		foreach( $rows as $row ){
			$rowView = $this->ui->makeGrid()
				->gutter(0)
				;

			foreach( $row as $slot ){
				$left = floor( 100 * 100 * (($slot->start - $this->timeMin) / $thisLength ) ) / 100;

				$slotDuration = $slot->end - $slot->start;
				if( $slotDuration + $slot->start > $this->timeMax ){
					$slotDuration = $this->timeMax - $slot->start;
				}

				$width = floor( 100 * 100 * ($slotDuration / $thisLength ) ) / 100;
				$offset = floor( 100 * 100 * ($slot->offset / $thisLength ) ) / 100;

				// echo "left = $left, width = $width, offset = $offset<br>";

				$rowView
					->add( $slot->content, $width . '%', $width . '%', $offset . '%' )
					;
			}

			$out[] = $rowView;
		}

		$out = $this->ui->makeList( $out )
			->gutter(1)
			;
		return $out;
	}
}