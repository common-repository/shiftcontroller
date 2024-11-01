<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
interface SH4_Shifts_Model_
{
	public function getId();
	public function setId( $id );
	public function getTitle();
	public function getCalendar();
	public function getEmployee();

	public function getStart();
	public function getEnd();
	public function setStart( $start );
	public function setEnd( $end );

	public function getBreakStart();
	public function getBreakEnd();

	public function getStartInDay();
	public function getEndInDay();
	public function getBreakStartInDay();
	public function getBreakEndInDay();

	public function getDateStart();
	public function getDateEnd();

	public function isMultiDay();

	public function isPublished();
	public function isDraft();
	public function isOpen();

	public function setRawData( $data );
	public function setRawDataByKey( $key, $value );
	public function getRawData( $key );
	public function getRawDataKeys();

	public function getGroupingId();
}

class SH4_Shifts_Model implements SH4_Shifts_Model_
{
	const STATUS_PUBLISH = 'publish';
	const STATUS_DRAFT = 'draft';

	public $id = NULL;
	public $calendar = NULL;

	public $start = NULL;
	public $end = NULL;
	public $breakStart = NULL;
	public $breakEnd = NULL;

	public $employee = NULL;
	public $status = self::STATUS_DRAFT;

	public $raw = array();

	public function __construct( $id, SH4_Calendars_Model $calendar, $start, $end, SH4_Employees_Model $employee, $breakStart = NULL, $breakEnd = NULL, $status = self::STATUS_DRAFT )
	{
		$this->id = $id;
		$this->calendar = $calendar;
		$this->start = $start;
		$this->end = $end;
		$this->breakStart = $breakStart;
		$this->breakEnd = $breakEnd;
		$this->employee = $employee;
		$this->status = $status;
	}  

	public function getGroupingId()
	{
		$ret = array();

		$ret[] = $this->getStart();
		$ret[] = $this->getEnd();
		$ret[] = $this->getCalendar()->getId();
		$ret[] = $this->isPublished() ? 1 : 0;

		$moreKeys = $this->getRawDataKeys();
		$moreKeys = array_filter( $moreKeys, function($e){ return ('meta_' === substr($e, 0, strlen('meta_'))); } );
		
		foreach( $moreKeys as $k ){
			$v = $this->getRawData( $k );
			if( is_string($v) ){
				$ret[] = $v;
			}
		}

		$ret = join( '-', $ret );
		return $ret;
	}

	public function setRawData( $data )
	{
		$this->raw = $data;
		return $this;
	}

	public function setRawDataByKey( $key, $value )
	{
		$this->raw[$key] = $value;
		return $this;
	}

	public function getRawData( $key )
	{
		$return = array_key_exists( $key, $this->raw ) ? $this->raw[$key] : NULL;
		return $return;
	}

	public function getRawDataKeys()
	{
		return array_keys( $this->raw );
	}

	public function getId()
	{
		return $this->id;
	}

	public function setId( $id )
	{
		$this->id = $id;
		return $this;
	}

	public function getStatus()
	{
		return $this->status;
	}

	public function isPublished()
	{
		return ($this->status == self::STATUS_PUBLISH);
	}

	public function isDraft()
	{
		return ($this->status == self::STATUS_DRAFT);
	}

	public function getTitle()
	{
		$return = array();
		$return[] = $this->getStart();
		$return[] = $this->getEnd();
		$return = join('-', $return);
		return $return;
	}

	public function getCalendar()
	{
		return $this->calendar;
	}

	public function getEmployee()
	{
		return $this->employee;
	}

	public function isOpen()
	{
		$employee = $this->getEmployee();
		$employeeId = $employee->getId();

		$return = ( $employeeId == 0 ) ? TRUE : FALSE;
		return $return;
	}

	public function getStart()
	{
		return $this->start;
	}

	public function getEnd()
	{
		return $this->end;
	}

	public function setStart( $start )
	{
		$this->start = $start;
		return $this;
	}

	public function setEnd( $end )
	{
		$this->end = $end;
		return $this;
	}

	public function getBreakStart()
	{
		return $this->breakStart;
	}

	public function getBreakEnd()
	{
		return $this->breakEnd;
	}

	public function getStartInDay()
	{
		$full = $this->getStart();

		$hour = substr( $full, 8, 2 );
		$minute = substr( $full, 10, 2 );
		$return = $hour * 60 * 60 + $minute * 60;

		return $return;
	}

	public function getEndInDay()
	{
		$full = $this->getEnd();

		$hour = substr( $full, 8, 2 );
		$minute = substr( $full, 10, 2 );
		$return = $hour * 60 * 60 + $minute * 60;

		if( $return ){
			$startInDay = $this->getStartInDay();
		// overnight shifts
			if( $return <= $startInDay ){
				$return = 24*60*60 + $return;
			}
		}

		if( 0 == $return ){
			$return = 24*60*60;
		}

		return $return;
	}

	public function getBreakStartInDay()
	{
		$return = NULL;

		$full = $this->getBreakStart();
		if( NULL === $full ){
			return $return;
		}

		$hour = substr( $full, 8, 2 );
		$minute = substr( $full, 10, 2 );
		$return = $hour * 60 * 60 + $minute * 60;

		return $return;
	}

	public function getBreakEndInDay()
	{
		$return = NULL;

		$full = $this->getBreakEnd();
		if( NULL === $full ){
			return $return;
		}

		$hour = substr( $full, 8, 2 );
		$minute = substr( $full, 10, 2 );
		$return = $hour * 60 * 60 + $minute * 60;

		if( 0 == $return ){
			$return = 24*60*60;
		}

		return $return;
	}

	public function getDateStart()
	{
		$full = $this->getStart();
		$return = substr( $full, 0, 8 );
		return $return;
	}

	public function getDateEnd()
	{
		$full = $this->getEnd();
		$return = substr( $full, 0, 8 );
		return $return;
	}

	public function isMultiDay()
	{
		$startInDay = $this->getStartInDay();
		$endInDay = $this->getEndInDay();

		$return = FALSE;
		if( (0 == $startInDay) && ((24*60*60 == $endInDay) OR (0 == $endInDay)) ){
			$return = TRUE;
		}

		return $return;
	}
}