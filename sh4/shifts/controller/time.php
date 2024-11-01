<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
#[AllowDynamicProperties]
class SH4_Shifts_Controller_Time
{
	public function __construct(
		HC3_Hooks $hooks,
		HC3_Post $post,
		HC3_Time $t,

		SH4_Shifts_Conflicts $conflicts,
		HC3_Auth $auth,
		HC3_IPermission $permission,
		SH4_App_Query $appQuery,
		SH4_Calendars_Permissions $calendarsPermissions,

		SH4_ShiftTypes_Query $shiftTypesQuery,

		SH4_Shifts_Query $query,
		SH4_Shifts_Command $command,
		SH4_Employees_Query $employees
		)
	{
		$this->post = $post;
		$this->t = $t;

		$this->shiftTypesQuery = $hooks->wrap($shiftTypesQuery);

		$this->query = $hooks->wrap($query);
		$this->command = $hooks->wrap($command);
		$this->employees = $hooks->wrap($employees);

		$this->auth = $hooks->wrap( $auth );
		$this->permission = $hooks->wrap( $permission );
		$this->conflicts = $hooks->wrap($conflicts);
		$this->appQuery = $hooks->wrap( $appQuery );
		$this->calendarsPermissions = $hooks->wrap( $calendarsPermissions );
	}

	public function execute( $shiftId )
	{
		$shift = $this->query->findById( $shiftId );

		$shiftTypeId = $this->post->get('shifttype');
		if( NULL !== $shiftTypeId ){
			$shiftType = $this->shiftTypesQuery->findById( $shiftTypeId );

			$timeStart = $shiftType->getStart();
			$timeEnd = $shiftType->getEnd();
			$startBreak = $shiftType->getBreakStart();
			$endBreak = $shiftType->getBreakEnd();
		}
		else {
			$time = $this->post->get('time');

			if( NULL === $time ){
				$to = 'schedule';
				$msg = '__Required Field__';
				$return = array( $to, $msg, TRUE );
				return $return;
			}

			list( $timeStart, $timeEnd ) = explode( '-', $time );

			$startBreak = $endBreak = NULL;
			$breakOn = $this->post->get('break_on');
			if( $breakOn ){
				$break = $this->post->get('break');
				list( $startBreak, $endBreak ) = explode( '-', $break );
			}
		}

		$date = $shift->getDateStart();

		$start = $this->t->setDateDb( $date )
			->modify( '+' . $timeStart . ' seconds' )
			->formatDateTimeDb()
			;
		$end = $this->t->setDateDb( $date )
			->modify( '+' . $timeEnd . ' seconds' )
			->formatDateTimeDb()
			;

		if( NULL !== $startBreak ){
			$startBreak = $this->t->setDateDb( $date )
				->modify( '+' . $startBreak . ' seconds' )
				->formatDateTimeDb()
				;
		}
		if( NULL !== $endBreak ){
			$endBreak = $this->t->setDateDb( $date )
				->modify( '+' . $endBreak . ' seconds' )
				->formatDateTimeDb()
				;
		}

// check if it creates a conflict
		$calendar = $shift->getCalendar();
		$calendarId = $calendar->getId();

		// $testModel = new SH4_Shifts_Model( NULL, $calendar, $start, $end, $shift->getEmployee(), $startBreak, $endBreak );
		$testModel = new SH4_Shifts_Model( $shift->getId(), $calendar, $start, $end, $shift->getEmployee(), $startBreak, $endBreak );
		$conflicts = $this->conflicts->get( $testModel );

		$allowed = TRUE;
		if( $conflicts ){
			$isManager = FALSE;

			$currentUser = $this->auth->getCurrentUser();
			$currentUserId = $currentUser->getId();
			if( $currentUserId ){
				if( $this->permission->isAdmin($currentUser) ){
					$isManager = TRUE;
				}
				else {
					$calendarsAsManager = $this->appQuery->findCalendarsManagedByUser( $currentUser );
					if( isset($calendarsAsManager[$calendarId]) ){
						$isManager = TRUE;
					}
				}
			}

			if( ! $isManager ){
				if( ! $this->calendarsPermissions->get($calendar, 'employee_create_own_conflicts') ){
					$allowed = FALSE;
				}
			}
		}

		$to = $this->post->get( 'back' );
		if( $to ){
			$to = json_decode( $to, TRUE );
		}
		else {
			$to = array( 'schedule', array() );
		}

		if( $allowed ){
			$this->command->reschedule( $shift, $start, $end, $startBreak, $endBreak );
			$msg = '__Shift Rescheduled__';
			$return = array( $to, $msg );
		}
		else {
			$msg = '__You cannot create new shifts with conflicts.__';
			$return = array( $to, $msg, TRUE );
		}

		return $return;
	}
}