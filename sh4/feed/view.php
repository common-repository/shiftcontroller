<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
#[AllowDynamicProperties]
class SH4_Feed_View
{
	public function __construct(
		HC3_Hooks $hooks,
		HC3_Time $t,
		HC3_Auth $auth,

		HC3_Translate $translate,
		SH4_Shifts_Presenter $shiftsPresenter,

		SH4_App_Query $appQuery,
		SH4_Shifts_Query $shiftsQuery
	)
	{
		$this->t = $t;
		$this->auth = $auth;
		$this->translate = $translate;

		$this->shiftsPresenter = $hooks->wrap( $shiftsPresenter );
		$this->appQuery = $hooks->wrap( $appQuery );
		$this->shiftsQuery = $hooks->wrap( $shiftsQuery );
	}

	protected function _shifts( $token, $calendarId = NULL, $employeeId = NULL, $fromDate = NULL, $toDate = NULL )
	{
		$user = $this->auth->getUserByToken( $token );

		if( ! $user ){
			echo "wrong link";
			exit;
			return;
		}

		if( NULL === $fromDate ){
			$fromDate = $this->t->setNow()->formatDateDb();
			$toDate = $this->t->modify('+1 year')->formatDateDb();
		}

		$start = $this->t->setDateDb( $fromDate )->formatDateTimeDb();
		$end = $this->t->setDateDb( $toDate )->modify('+1 day')->formatDateTimeDb();

		$this->shiftsQuery
			->setStart( $start )
			->setEnd( $end )
			;

		$shifts = $this->shiftsQuery->find();

		$moreKeys = array();
		foreach( array_keys($_GET) as $k ){
			if( 'misc' == substr($k, 0, strlen('misc')) ) $moreKeys[] = $k;
		}

	// filter shifts
		$ids = array_keys( $shifts );
		foreach( $ids as $id ){
			$shift = $shifts[$id];

			if( $shift->getStart() >= $end ){
				unset( $shifts[$id] );
				continue;
			}

			if( $shift->getEnd() <= $start ){
				unset( $shifts[$id] );
				continue;
			}

			$shiftCalendar = $shift->getCalendar();
			$shiftCalendarId = $shiftCalendar->getId();

			$shiftEmployee = $shift->getEmployee();
			$shiftEmployeeId = $shiftEmployee->getId();

			if( NULL !== $calendarId ){
				if( 't' == $calendarId ){
					if( ! $shiftCalendar->isTimeoff() ){
						unset( $shifts[$id] );
						continue;
					}
				}
				elseif( 's' == $calendarId ){
					if( ! $shiftCalendar->isShift() ){
						unset( $shifts[$id] );
						continue;
					}
				}
				elseif( 'x' != $calendarId ){
					if( $shiftCalendarId != $calendarId ){
						unset( $shifts[$id] );
						continue;
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
					if( $shiftEmployeeId != $employeeId ){
						unset( $shifts[$id] );
						continue;
					}
				}
			// }

		// anything more in get params to filter out?
			if( $moreKeys ){
				$thisOut = $this->shiftsPresenter->export( $shifts[$id], true, $user );
				$skipThis = false;
				foreach( $moreKeys as $k ){
					if( ! array_key_exists($k, $thisOut) ){
						$skipThis = true;
						break;
					}

					$v = sanitize_text_field( $_GET[$k] );
					if( $thisOut[$k] != $v ){
						$skipThis = true;
						break;
					}
				}

				if( $skipThis ){
					unset( $shifts[$id] );
					continue;
				}
			}
		}

		$shifts = $this->appQuery->filterShiftsForUser( $user, $shifts );
		return $shifts;
	}

	public function renderJson( $token, $calendarId = NULL, $employeeId = NULL, $fromDate = NULL, $toDate = NULL )
	{
		$shifts = $this->_shifts( $token, $calendarId, $employeeId, $fromDate, $toDate );
		$user = $this->auth->getUserByToken( $token );

		$separator = ',';
		$out = array();
		foreach( $shifts as $shift ){
			$thisOut = $this->shiftsPresenter->export( $shift, TRUE, $user );
			$keys = array_keys( $thisOut );

			reset( $keys );
			foreach( $keys as $k ){
				$thisOut[$k] = $this->translate->translate( $thisOut[$k] );
			}

			// $thisOut = HC3_Functions::buildCsv( $thisOut, $separator );
			$out[] = $thisOut;
		}

		$out = json_encode( $out, JSON_UNESCAPED_UNICODE );
		echo $out;
		exit;

		// echo $out;
		// exit;

		$fileName = 'feed';
		$fileName .= '-' . date('Y-m-d_H-i') . '.csv';
		HC3_Functions::pushDownload( $fileName, $out );
		exit;
	}

	public function render( $token, $calendarId = NULL, $employeeId = NULL, $fromDate = NULL, $toDate = NULL )
	{
		$shifts = $this->_shifts( $token, $calendarId, $employeeId, $fromDate, $toDate );
		$user = $this->auth->getUserByToken( $token );

		$separator = ',';

		$shiftsOut = array();
		$header = array();

		foreach( $shifts as $shift ){
			$thisOut = $this->shiftsPresenter->export( $shift );

			$thisHeader = array_keys( $thisOut );
			foreach( $thisHeader as $th ){
				if( ! isset($header[$th]) ){
					$header[$th] = $th;
				}
			}

			$shiftsOut[] = $thisOut;
		}

		$keys = array_keys( $header );
		$out = array();
		reset( $shiftsOut );
		foreach( $shiftsOut as $shiftOut ){
			$thisOut = array();

			reset( $keys );
			foreach( $keys as $k ){
				if( isset($shiftOut[$k]) ){
					$thisOut[$k] = $this->translate->translate( $shiftOut[$k] );
				}
				else {
					$thisOut[$k] = null;
				}
			}

			$thisOut = HC3_Functions::buildCsv( $thisOut, $separator );
			$out[] = $thisOut;
		}

		if( $out ){
			$header = HC3_Functions::buildCsv( $header, $separator );
			$header = array_unshift( $out, $header );
		}

		$out = join("\n", $out);

		// echo $out;
		// exit;

		$fileName = 'feed';
		$fileName .= '-' . date('Y-m-d_H-i') . '.csv';
		HC3_Functions::pushDownload( $fileName, $out );
		exit;
	}
}