<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
interface SH4_ShiftTypes_Model_
{
	public function getId();
	public function getTitle();
	public function getRange();

	public function getStart();
	public function getEnd();
	public function getBreakStart();
	public function getBreakEnd();

	public function isAllDay();
	public function getDuration();
}

class SH4_ShiftTypes_Model implements SH4_ShiftTypes_Model_
{
	const RANGE_HOURS = 'hours';
	const RANGE_DAYS = 'days';

	private $id = NULL;
	private $title = NULL;
	private $range = NULL;

	private $start = 0;
	private $end = 86400;
	private $breakStart = NULL;
	private $breakEnd = NULL;

	public function __construct( $id, $title, $range, $start, $end, $breakStart = NULL, $breakEnd = NULL )
	{
		$this->id = $id;
		$this->title = $title;
		$this->range = $range;
		$this->start = $start;
		$this->end = $end;
		$this->breakStart = $breakStart;
		$this->breakEnd = $breakEnd;
		return $this;
	}

	public function getId()
	{
		return $this->id;
	}

	public function getTitle()
	{
		return $this->title;
	}

	public function getRange()
	{
		return $this->range;
	}

	public function getStart()
	{
		return $this->start;
	}

	public function getEnd()
	{
		return $this->end;
	}

	public function getBreakStart()
	{
		return $this->breakStart;
	}

	public function getBreakEnd()
	{
		return $this->breakEnd;
	}

	public function isAllDay()
	{
		$return = FALSE;
		if( ($this->getStart() == 0) && ($this->getEnd() == 24*60*60) ){
			$return = TRUE;
		}
		return $return;
	}

	public function getDuration()
	{
		$start = $this->getStart();
		$end = $this->getEnd();

		$return = ( $end > $start ) ? ($end - $start) : (24*60*60 - $start) + $end;
		return $return;
	}
}