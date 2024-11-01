<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class SH4_Ical_View
{
	public $self, $t, $ical, $auth, $appQuery, $shiftsQuery, $myUnique, $setting;

	public function __construct(
		HC3_Hooks $hooks,
		HC3_Time $t,
		HC3_Auth $auth,
		HC3_Settings $setting,

		SH4_App_Query $appQuery,
		SH4_Shifts_Query $shiftsQuery,
		SH4_Ical_Lib_iCalCreator $ical
	)
	{
		$this->t = $t;
		$this->ical = $ical;
		$this->auth = $auth;

		$this->appQuery = $hooks->wrap( $appQuery );
		$this->shiftsQuery = $hooks->wrap( $shiftsQuery );
		$this->self = $hooks->wrap( $this );
		$this->setting = $hooks->wrap( $setting );

		$this->myUnique = 'shiftcontroller';
	}

	public function renderDescription( SH4_Shifts_Model $shift )
	{
		$calendar = $shift->getCalendar();
		$employee = $shift->getEmployee();

		$calendarView = $calendar->getTitle();
		$calendarDescription = $calendar->getDescription();
		if( strlen($calendarDescription) ){
			// $calendarView .= ' (' . $calendarDescription . ')';
			$calendarView .= '\\n' . $calendarDescription;
		}
		$employeeView = $employee->getTitle();

		$return = $employeeView . ' @ ' . $calendarView;
		return $return;
	}

	public function makeIcalEvent( $shift, $user )
	{
		static $t2 = null;

		if( null === $t2 ){
			$t2 = clone $this->t;
			$t2->setTimezone('UTC');
		}

		$event = new hc_vevent(); // initiate a new EVENT

		$shiftId = $shift->getId();
		$calendar = $shift->getCalendar();
		$employee = $shift->getEmployee();
		$start = $shift->getStart();
		$end = $shift->getEnd();

		$calendarView = $calendar->getTitle();
		$employeeView = $employee->getTitle();

		$event->setProperty( 'uid', 'obj-' . $shiftId . '-' . $this->myUnique );

		$summary = $employeeView . ' @ ' . $calendarView;
		$description = $this->self->renderDescription( $shift, $user );
		$description = preg_replace( '/\R+/', '\\n', $description );
		// $description = str_replace( "\n", " ", $description );
		// $description = nl2br( $description );
		$description = strip_tags( $description );

		$isMultiDay = $shift->isMultiDay();

		$this->t->setDateTimeDb( $start );
		$t2->setTimestamp( $this->t->getTimestamp() );

		if( $isMultiDay ){
		// for multiday timezone may shift date so use utc
			// $date = $t2->formatDateDb();
			$date = $this->t->formatDateDb();
			$event->setProperty( 'dtstart', $date, array('VALUE' => 'DATE'));
		}
		else {
			list( $year, $month, $day, $hour, $min ) = $t2->getParts(); 
			$event->setProperty( 'dtstart', $year, $month, $day, $hour, $min, 00, 'Z' );  // 24 dec 2006 19.30
		}

		$this->t->setDateTimeDb( $end );
		$t2->setTimestamp( $this->t->getTimestamp() );

		if( $isMultiDay ){
		// for multiday timezone may shift date so use utc
			// $date = $t2->formatDateDb();
			$date = $this->t->formatDateDb();
			$event->setProperty( 'dtend', $date, array('VALUE' => 'DATE'));
		}
		else {
			list( $year, $month, $day, $hour, $min ) = $t2->getParts(); 
			$event->setProperty( 'dtend', $year, $month, $day, $hour, $min, 00, 'Z' );  // 24 dec 2006 19.30
		}

		// $event->setProperty( 'location', $calendarView );
		$event->setProperty( 'description', $description );
		$event->setProperty( 'summary', $summary );

		if( $shift->isPublished() ){
			$event->setProperty( 'status', 'TENTATIVE' );
		}
		else {
			$event->setProperty( 'status', 'CONFIRMED' );
		}

		return $event;
	}

	public function render( $token, $calendarId = NULL, $employeeId = NULL )
	{
		$user = $this->auth->getUserByToken( $token );

		if( ! $user ){
			echo "wrong link";
			exit;
			return;
		}

		if( null !== $calendarId ){
			if( false !== strpos($calendarId, ',') ){
				$calendarId = explode( ',', $calendarId );
			}
		}

		if( null !== $employeeId ){
			if( false !== strpos($employeeId, ',') ){
				$employeeId = explode( ',', $employeeId );
			}
		}

		$userId = $user->getId();
		$pname = 'ical_max_advance' . '_' . $userId;
		$maxAdvance = $this->setting->get( $pname );

		$ok = true;

		if( ! $maxAdvance ){
			$ok = false;
		}
		else {
			if( false === strpos($maxAdvance, ' ') ){
				$ok = false;
			}
		}

		if( ! $ok ){
			$maxAdvance = '3 month';
		}

	/* 1 month before and 3 months after */
		$start = $this->t->setNow()->modify('-1 month')->formatDateTimeDb();
		$end = $this->t->setNow()->modify('+' . $maxAdvance)->formatDateTimeDb();

		$this->shiftsQuery
			->setStart( $start )
			->setEnd( $end )
			;

		$shifts = $this->shiftsQuery->find();

	// filter shifts
		$ids = array_keys( $shifts );
		foreach( $ids as $id ){
			$shift = $shifts[$id];

			$shiftCalendar = $shift->getCalendar();
			$shiftCalendarId = $shiftCalendar->getId();

			$shiftEmployee = $shift->getEmployee();
			$shiftEmployeeId = $shiftEmployee->getId();

			if( NULL !== $calendarId ){
				if( 't' == $calendarId ){
					if( ! $shiftCalendar->isTimeoff() ){
						unset( $shifts[$id] );
					}
				}
				elseif( 's' == $calendarId ){
					if( ! $shiftCalendar->isShift() ){
						unset( $shifts[$id] );
					}
				}
				elseif( 'x' != $calendarId ){
					if( is_array($calendarId) ){
						if( ! in_array($shiftCalendarId, $calendarId) ){
							unset( $shifts[$id] );
						}
					}
					else {
						if( $shiftCalendarId != $calendarId ){
							unset( $shifts[$id] );
						}
					}
				}
			}

			// if( (NULL !== $calendarId) && ('x' != $calendarId) ){
				// if( (NULL !== $calendarId) && ('x' != $calendarId) ){
					// if( $shiftCalendarId != $calendarId ){
						// unset( $shifts[$id] );
					// }
				// }
			// }
			// else {
				if( (NULL !== $employeeId) && ('x' != $employeeId) ){
					if( is_array($employeeId) ){
						if( ! in_array($shiftEmployeeId, $employeeId) ){
							unset( $shifts[$id] );
						}
					}
					else {
						if( $shiftEmployeeId != $employeeId ){
							unset( $shifts[$id] );
						}
					}
				}
			// }
		}

		$shifts = $this->appQuery->filterShiftsForUser( $user, $shifts );

		$timezoneObj = $this->t->getTimezone();
		$timezone = $timezoneObj->getName();

		$cal = $this->ical;

		$cal->setConfig( 'unique_id', $this->myUnique );
//		$cal->setProperty( 'method', 'publish' );
		$cal->setProperty( 'method', 'request' );
		$cal->setProperty( 'x-wr-timezone', $timezone );

		$vtz = new hc_vtimezone();
		$vtz->setProperty( 'tzid', $timezone );
		$cal->addComponent( $vtz );

		reset( $shifts );
		foreach( $shifts as $shift ){
			$event = $this->self->makeIcalEvent( $shift, $user );
			$cal->addComponent( $event );
		}

		$ret = $cal->createCalendar();

		// header('Content-Type: text/calendar');
		echo $ret;
		exit;
	}
}