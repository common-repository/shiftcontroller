<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
interface HC3_ITime
{
	public function setTimezone( $tz );
	public function setNow();
	public function formatToDatepicker();
	public function formatDate( $format = '' );
	public function formatDateWithWeekday( $format = '' );
	public function formatDateDb();
	public function setDateDb( $date );
	public function setDateTimeDb( $datetime );
	public function formatTime();
	public function formatDateTimeDb();
	public function formatTimeDb();
	public function setStartDay();
	public function getWeekStartsOn();
	public function setStartWeek();
	public function setStartMonth();
	public function setEndMonth();
	public function setStartYear();
	public function setEndYear();
	public function setEndWeek();
	public function getYear();
	public function getDay();
	public function getWeekday();
	public function getMonthName();
	public function getWeekdayName();
	public function formatDateRange( $date1, $date2, $with_weekday = FALSE );
	public function getMonthMatrix( $skipWeekdays = array() );
	public function getParts();
	public function getWeekdays();
	public function sortWeekdays( $wds );
	public function formatDuration( $seconds );
	public function getTimeInDay();
}

class HC3_Time extends DateTime implements HC3_ITime
{
	public $timeFormat = 'g:ia';
	public $dateFormat = 'j M Y';
	public $weekStartsOn = 0;

	protected $_months = array();
	protected $_weekdays = array();

	var $timezone = '';

	function __construct( HC3_Settings $settings = NULL )
	{
		parent::__construct();

		$localiseThis = array('__Jan__', '__Feb__', '__Mar__', '__Apr__', '__May__', '__Jun__', '__Jul__', '__Aug__', '__Sep__', '__Oct__', '__Nov__', '__Dec__');

		$this->_months = array('Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec');
		$this->_weekdays = array('Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat');

		if( $settings ){
			if( $time_format = $settings->get('datetime_time_format') ){
				$this->timeFormat = $time_format;
			}
			if( $date_format = $settings->get('datetime_date_format') ){
				$this->dateFormat = $date_format;
			}
			if( $week_starts = $settings->get('datetime_week_starts') ){
				$this->weekStartsOn = $week_starts;
			}

			$tz = '';

			$tz = $settings->get('datetime_timezone');
			if( ! $tz ){
				$tz = '';
			}
			$tz = trim( $tz );

			if( ! strlen($tz) ){
				$tz = $this->getDefaultTimezone();
				$tz = trim( $tz );
			}

			if( strlen($tz) ){
				$this->setTimezone( $tz );
			}
		}

		// if( defined('WPINC') ){
			// $tz = get_option('timezone_string');
			// if( ! strlen($tz) ){
				// $offset = get_option('gmt_offset');
				// if( $offset ){
					// $tz = 'Etc/GMT';
					// if( $offset > 0 ){
						// $tz .= '+' . $offset;
					// }
					// else {
						// $tz .= '-' . -$offset;
					// }
				// }
			// }

			// $this->setTimezone( $tz );
		// }
	}

#[\ReturnTypeWillChange]
	public function setTimezone( $tz )
	{
		if( is_array($tz) )
			$tz = $tz[0];

		if( ! $tz )
			$tz = date_default_timezone_get();

	// check timezone
		$timezones = $this->getTimezones();

		if( isset($timezones[$tz]) OR ('UTC' == $tz) ){
			$this->timezone = $tz;
			$tz = new DateTimeZone( $tz );
			parent::setTimezone( $tz );
		}
	}

	public function setNow()
	{
		$this->setTimestamp( time() );
		return $this;
	}

	public function formatToDatepicker()
	{
		$dateFormat = $this->dateFormat;

		$pattern = array(
			//day
			'd',	//day of the month
			'j',	//3 letter name of the day
			'l',	//full name of the day
			'z',	//day of the year

			//month
			'F',	//Month name full
			'M',	//Month name short
			'n',	//numeric month no leading zeros
			'm',	//numeric month leading zeros

			//year
			'Y', //full numeric year
			'y'	//numeric year: 2 digit
			);

		$replace = array(
			'dd','d','DD','o',
			'MM','M','m','mm',
			'yyyy','y'
		);
		foreach($pattern as &$p){
			$p = '/'.$p.'/';
		}
		return preg_replace( $pattern, $replace, $dateFormat );
	}

	public function formatDateWithWeekday( $format = '' )
	{
		$out = array();
		$out[] = $this->getWeekdayName();
		$out[] = $this->formatDate( $format );
		$out = join(', ', $out);
		return $out;
	}

	public function formatDate( $format = '' )
	{
		if( ! strlen($format) ){
			$format = $this->dateFormat;
		}

		$return = $this->format( $format );

	// replace English months to localized ones
		$replace_from = array();
		$replace_to = array();
		foreach( $this->_months as $month ){
			$replace_from[] = $month;
			$replace_to[] = '__' . $month . '__';
		}

		$return = str_replace( $replace_from, $replace_to, $return );
		return $return;
	}

	public function formatDateDb()
	{
		$dateFormat = 'Ymd';
		$return = $this->format( $dateFormat );
		return $return;
	}

	public function setDateDb( $date )
	{
		list( $year, $month, $day ) = $this->_splitDate( $date );
		$year = (int) $year;
		$month = (int) $month;
		$day = (int) $day;

		$this->setDate( $year, $month, $day );
		$this->setTime( 0, 0, 0 );
		return $this;
	}

	public function setDateTimeDb( $datetime )
	{
		$date = substr($datetime, 0, 8);
		$this->setDateDb( $date );

		$hours = substr($datetime, 8, 2);
		$minutes = substr($datetime, 10, 2);

		$hours = (int) $hours;
		$minutes = (int) $minutes;

		$this->setTime( $hours, $minutes, 0 );

		return $this;
	}

	public function formatTime( $format = NULL )
	{
		if( NULL === $format ) $format = $this->timeFormat;

		if( '12short' === $format ){
			$ret = $this->format( 'g:ia' );
			$ret = str_replace( ':00', '', $ret );
		}
		elseif( '12xshort' === $format ){
			$ret = $this->format( 'g:ia' );
			$ret = str_replace( 'am', 'a', $ret );
			$ret = str_replace( 'pm', 'p', $ret );
			$ret = str_replace( ':00', '', $ret );
		}
		elseif( '24short' === $format ){
			$ret = $this->format( 'H:i' );
			$ret = preg_replace( '/0(\d\:)/', '${1}', $ret );
			$ret = str_replace( ':00', '', $ret );
		}
		else {
			$ret = $this->format( $format );
		}

		return $ret;
	}

	protected function _splitDate( $string )
	{
		$year = substr( $string, 0, 4 );
		$month = substr( $string, 4, 2 );
		$day = substr( $string, 6, 4 );
		$return = array( $year, $month, $day );
		return $return;
	}

	public function formatDateTimeDb()
	{
		$date = $this->formatDateDb();
		$time = $this->formatTimeDb();
		$return = $date . $time;
		return $return;
	}

	public function formatTimeDb()
	{
		$h = $this->format('G');
		$m = $this->format('i');

		$h = str_pad( $h, 2, 0, STR_PAD_LEFT );
		$m = str_pad( $m, 2, 0, STR_PAD_LEFT );

		$return = $h . $m;
		return $return;
	}

	public function setStartDay()
	{
		$this->setTime( 0, 0, 0 );
		return $this;
	}

	public function setEndDay()
	{
		$this->setTime( 23, 59, 0 );
		return $this;
	}

	public function getTimeInDay()
	{
		$dateTimeDb = $this->formatDateTimeDb();
	// 202404042030

		$hourString = substr( $dateTimeDb, 8, 2 );
		$minuteString = substr( $dateTimeDb, 10, 2 );

		$h = (int) $hourString;
		$m = (int) $minuteString;

		$ret = 60 * 60 * $h + 60 * $m;

		return $ret;
	}

	public function getWeekStartsOn()
	{
		return $this->weekStartsOn;
	}

	public function setStartWeek()
	{
		$this->setStartDay();
		$weekDay = $this->getWeekday();

		while( $weekDay != $this->weekStartsOn ){
			$this->modify( '-1 day' );
			$weekDay = $this->getWeekday();
		}

		return $this;
	}

	public function setStartMonth()
	{
		$year = $this->format('Y');
		$month = $this->format('m');
		$day = '01';

		$date = $year . $month . $day;
		$this->setDateDb( $date );
		$this->setTime( 0, 0, 0 );

		return $this;
	}

	public function setEndMonth()
	{
		$this->modify('+1 month');

		$year = $this->format('Y');
		$month = $this->format('m');
		$day = '01';

		$date = $year . $month . $day;
		$this->setDateDb( $date );
		$this->modify('-1 day')
			->setEndDay()
			;

		return $this;
	}

	public function setStartYear()
	{
		$year = $this->format('Y');
		$month = '01';
		$day = '01';

		$date = $year . $month . $day;
		$this->setDateDb( $date );
		$this->setTime( 0, 0, 0 );

		return $this;
	}

	public function setEndYear()
	{
		$this->modify('+1 year');

		$year = $this->format('Y');
		$month = '01';
		$day = '01';

		$date = $year . $month . $day;
		$this->setDateDb( $date );
		$this->modify('-1 day')
			->setEndDay()
			;

		return $this;
	}

	public function setEndWeek()
	{
		$this->setStartDay();
		$this->modify( '+1 day' );
		$weekDay = $this->getWeekday();

		while( $weekDay != $this->weekStartsOn ){
			$this->modify( '+1 day' );
			$weekDay = $this->getWeekday();
		}

		$this->modify( '-1 day' )
			->setEndDay()
			;

		return $this;
	}

	public function getYear()
	{
		$return = $this->format('Y');
		return $return;
	}

	public function getDay()
	{
		$return = $this->format('j');
		return $return;
	}

	public function getWeekday()
	{
		$return = $this->format('w');
		return $return;
	}

	public function getWeekNo()
	{
		$return = $this->format('W'); // but it works out of the box for week starts on monday
		$weekday = $this->getWeekday();
		if( ! $weekday ){ // sunday
			if( ! $this->weekStartsOn ){
				$return = $return + 1;
			}
		}
		return $return;
	}

	public function getMonthName()
	{
		$month = $this->format('n') - 1;
		$return = $this->_months[$month];
		$return = '__' . $return . '__';
		return $return;
	}

	public function getWeekdayName()
	{
		$wd = $this->getWeekday();
		$return = $this->_weekdays[$wd];
		$return = '__' . $return . '__';
		return $return;
	}

	public function formatDateRange( $date1, $date2, $with_weekday = FALSE )
	{
		list( $start_date_view, $end_date_view ) = $this->_formatDateRange( $date1, $date2, $with_weekday );

		if( $end_date_view ){
			$return = $start_date_view . ' - ' . $end_date_view;
		}
		else {
			$return = $start_date_view;
		}
		return $return;
	}

	protected function _formatDateRange( $date1, $date2, $with_weekday = FALSE )
	{
		$return = array();
		$skip = array();

		if( $date1 == $date2 ){
			$this->setDateDb( $date1 );
			$view_date1 = $this->formatDate();
			if( $with_weekday ){
				$view_date1 = $this->formatWeekdayShort() . ', ' . $view_date1;
			}
			$return[] = $view_date1;
			$return[] = NULL;
			return $return;
		}

		$this->setDateDb( $date1 );
		$year1 = $this->getYear();
		$month1 = $this->format('n');

		$this->setDateDb( $date2 );
		$year2 = $this->getYear();
		$month2 = $this->format('n');

		if( $year2 == $year1 )
			$skip['year'] = TRUE;
		if( ($year2 == $year1) && ($month2 == $month1) )
			$skip['month'] = TRUE;

		if( $skip ){
			$date_format = $this->dateFormat;
			$date_format_short = $date_format;

			$tags = array('m', 'n', 'M');
			foreach( $tags as $t ){
				$pos_m_original = strpos($date_format_short, $t);
				if( $pos_m_original !== FALSE )
					break;
			}

			if( isset($skip['year']) ){
				$pos_y = strpos($date_format_short, 'Y');
				if( $pos_y == 0 ){
					$date_format_short = substr_replace( $date_format_short, '', $pos_y, 2 );
				}
				else {
					$date_format_short = substr_replace( $date_format_short, '', $pos_y - 1, 2 );
				}
			}

			if( isset($skip['month']) ){
				$tags = array('m', 'n', 'M');
				foreach( $tags as $t ){
					$pos_m = strpos($date_format_short, $t);
					if( $pos_m !== FALSE )
						break;
				}

				// month going first, do not replace
				if( $pos_m_original == 0 ){
					// $date_format_short = substr_replace( $date_format_short, '', $pos_m, 2 );
				}
				else {
					// month going first, do not replace
					if( $pos_m == 0 ){
						$date_format_short = substr_replace( $date_format_short, '', $pos_m, 2 );
					}
					else {
						$date_format_short = substr_replace( $date_format_short, '', $pos_m - 1, 2 );
					}
				}
			}

			if( $pos_y == 0 ){ // skip year in the second part
				$date_format1 = $date_format;
				$date_format2 = $date_format_short;
			}
			else {
				$date_format1 = $date_format_short;
				$date_format2 = $date_format;
			}

			$this->setDateDb( $date1 );

			$view_date1 = $this->formatDate( $date_format1 );
			if( $with_weekday ){
				$view_date1 = $this->formatWeekdayShort() . ', ' . $view_date1;
			}
			$return[] = $view_date1;

			$this->setDateDb( $date2 );
			$view_date2 = $this->formatDate( $date_format2 );
			if( $with_weekday ){
				$view_date2 = $this->formatWeekdayShort() . ', ' . $view_date2;
			}
			$return[] = $view_date2;
		}
		else {
			$this->setDateDb( $date1 );
			$view_date1 = $this->formatDate();
			if( $with_weekday ){
				$view_date1 = $this->formatWeekdayShort() . ', ' . $view_date1;
			}
			$return[] = $view_date1;

			$this->setDateDb( $date2 );
			$view_date2 = $this->formatDate();
			if( $with_weekday ){
				$view_date2 = $this->formatWeekdayShort() . ', ' . $view_date2;
			}
			$return[] = $view_date2;
		}

		return $return;
	}

	public function getMonthMatrix( $skipWeekdays = array() )
	{
		$overlap = TRUE; // if to show dates of prev/next month
		$overlap = FALSE; // if to show dates of prev/next month

		$matrix = array();
		$currentMonthDay = 0;

		$this->setStartMonth();
		if( $overlap ){
			$this->setStartWeek();
		}
		$startDate = $this->formatDateDb();

// echo "END DATE = $endDate<br>";

		$this->setEndMonth();
		if( $overlap ){
			// $this->setEndWeek();
		}
		$endDate = $this->formatDateDb();
// echo "START/END DATE = $startDate/$endDate<br>";

		$rexDate = $startDate;
		if( $overlap ){
			$this->setDateDb( $startDate );
			$this->setStartWeek();
			$rexDate = $this->formatDateDb();
		}

		$this->setDateDb( $startDate );
		$this->setStartWeek();
		$rexDate = $this->formatDateDb();

// echo "START DATE = $startDate, END DATE = $endDate, REX DATE = $rexDate<br>";

		$this->setDateDb( $rexDate );
		while( $rexDate <= $endDate ){
			$week = array();
			$weekSet = FALSE;
			for( $weekDay = 0; $weekDay <= 6; $weekDay++ ){
				$thisWeekday = $this->getWeekday();
				$setDate = $rexDate;

				if( ! $overlap ){
					if( 
						( $rexDate > $endDate ) OR
						( $rexDate < $startDate )
						){
						$setDate = NULL;
						}
				}

				// $week[ $thisWeekday ] = $setDate;

				if( (! $skipWeekdays) OR (! in_array($thisWeekday, $skipWeekdays)) ){
					$week[] = $setDate;
					if( NULL !== $setDate ){
						$weekSet = TRUE;
					}
				}

				$this->modify('+1 day');
				$rexDate = $this->formatDateDb();

				// if( $exact && ($rexDate >= $endDate) ){
					// break;
				// }
			}

			if( $weekSet )
				$matrix[] = $week;
		}
		return $matrix;
	}

	public function getWeeksMatrix( $weeks = 4, $skipWeekdays = array() )
	{
		$matrix = array();
		$currentMonthDay = 0;

		$this->setStartWeek();
		$startDate = $this->formatDateDb();

// echo "END DATE = $endDate<br>";
		$this->modify( '+' . $weeks . ' weeks' )->modify('-1 day');
		$endDate = $this->formatDateDb();
// echo "START/END DATE = $startDate/$endDate<br>";

		$rexDate = $startDate;

// echo "START DATE = $startDate, END DATE = $endDate, REX DATE = $rexDate<br>";

		$this->setDateDb( $rexDate );
		while( $rexDate <= $endDate ){
			$week = array();
			$weekSet = FALSE;
			for( $weekDay = 0; $weekDay <= 6; $weekDay++ ){
				$thisWeekday = $this->getWeekday();
				$setDate = $rexDate;

				// $week[ $thisWeekday ] = $setDate;

				if( (! $skipWeekdays) OR (! in_array($thisWeekday, $skipWeekdays)) ){
					$week[] = $setDate;
					if( NULL !== $setDate ){
						$weekSet = TRUE;
					}
				}

				$this->modify('+1 day');
				$rexDate = $this->formatDateDb();

				// if( $exact && ($rexDate >= $endDate) ){
					// break;
				// }
			}

			if( $weekSet )
				$matrix[] = $week;
		}
		return $matrix;
	}

	public function getParts()
	{
		$full = $this->formatDateTimeDb();

		$year = substr( $full, 0, 4 );
		$month = substr( $full, 4, 2 );
		$day = substr( $full, 6, 2 );
		$hour = substr( $full, 8, 2 );
		$min = substr( $full, 10, 2 );

		$return = array( $year, $month, $day, $hour, $min );
		return $return;
	}

	public function getWeekdays()
	{
		$return = array();

		$wkds = array( 0, 1, 2, 3, 4, 5, 6 );
		$wkds = $this->sortWeekdays( $wkds );

		reset( $wkds );
		foreach( $wkds as $wkd ){
			$return[ $wkd ] = '__' . $this->_weekdays[$wkd] . '__';
		}
		return $return;
	}

	public function sortWeekdays( $wds )
	{
		$return = array();
		$later = array();

		sort( $wds );
		reset( $wds );
		foreach( $wds as $wd ){
			if( $wd < $this->weekStartsOn )
				$later[] = $wd;
			else
				$return[] = $wd;
		}
		$return = array_merge( $return, $later );
		return $return;
	}

	public function formatDuration( $seconds )
	{
		$hours = floor( $seconds / (60 * 60) );
		$remain = $seconds - $hours * (60 * 60);
		$minutes = floor( $remain / 60 );

		$hoursView = $hours;
		$minutesView = sprintf( '%02d', $minutes );

		$return = $hoursView . ':' . $minutesView;
		// $return = gmdate( "H:i", $this->getDuration() );
		return $return;
	}

	public function getTimezones()
	{
		$skipStarts = array('Brazil/', 'Canada/', 'Chile/', 'Etc/', 'Mexico/', 'US/');
		$skipStarts = array( 'Etc/' );
		// $skipStarts = array();
		$return = array();

		if( defined('DateTimeZone::ALL_WITH_BC') )
			$timezones = timezone_identifiers_list( DateTimeZone::ALL_WITH_BC );
		else
			$timezones = timezone_identifiers_list();

		reset( $timezones );
		foreach( $timezones as $tz ){
			if( false === strpos($tz, "/") )
				continue;

			$skipIt = false;
			reset( $skipStarts );
			foreach( $skipStarts as $skip ){
				if( substr($tz, 0, strlen($skip)) == $skip ){
					$skipIt = true;
					break;
				}
			}
			if( $skipIt )
				continue;

			$tzTitle = $this->timezoneTitle( $tz );
			$return[ $tz ] = $tzTitle;
		}

		return $return;
	}

	public function timezoneTitle( $tz, $showOffset = FALSE )
	{
		if( is_array($tz) )
			$tz = $tz[0];

		$tzobj = new DateTimeZone( $tz );
		$dtobj = new DateTime();
		$dtobj->setTimezone( $tzobj );

		if( $showOffset ){
			$offset = $tzobj->getOffset( $dtobj );
			$offsetString = 'GMT';
			$offsetString .= ($offset >= 0) ? '+' : '';
			$offsetString = $offsetString . ( $offset/(60 * 60) );
			$return = $tz . ' (' . $offsetString . ')';
		}
		else {
			$return = $tz;
		}

		return $return;
	}

	public function getDefaultTimezone()
	{
		$return = date_default_timezone_get();

		if( defined('WPINC') ){
			$tz = get_option('timezone_string');
			if( ! strlen($tz) ){
				$offset = get_option('gmt_offset');
				if( $offset ){
					$tz = 'Etc/GMT';
					if( $offset > 0 ){
						$tz .= '+' . $offset;
					}
					else {
						$tz .= '-' . -$offset;
					}
				}
			}

			if( strlen($tz) ){
				$return = $tz;
			}
		}

		return $return;
	}
}