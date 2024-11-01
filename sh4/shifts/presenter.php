<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
interface SH4_Shifts_Presenter_
{
	public function presentTitle( SH4_Shifts_Model $model );
	public function presentFullTime( SH4_Shifts_Model $model );
	public function presentDate( SH4_Shifts_Model $model );
	public function presentTime( SH4_Shifts_Model $model );
	public function presentRawTime( SH4_Shifts_Model $model );
	public function presentBreak( SH4_Shifts_Model $model );

	public function presentStartDate( SH4_Shifts_Model $model );
	public function presentStartTime( SH4_Shifts_Model $model );
	public function presentEndDate( SH4_Shifts_Model $model );
	public function presentEndTime( SH4_Shifts_Model $model );
	public function presentStatus( SH4_Shifts_Model $model );

	public function export( SH4_Shifts_Model $model, $withContacts = FALSE );
}

class SH4_Shifts_Presenter implements SH4_Shifts_Presenter_
{
	protected $shiftTypes = array();
	public $self, $t, $appQuery, $shiftsDuration, $shiftTypesPresenter, $settings;

	public function __construct(
		HC3_Hooks $hooks,
		HC3_Time $t,

		SH4_App_Query $appQuery,
		HC3_Settings $settings,

		SH4_Shifts_Duration $shiftsDuration,
		SH4_ShiftTypes_Presenter $shiftTypesPresenter,
		SH4_ShiftTypes_Query $shiftTypes
		)
	{
		$this->t = $t;
		$this->self = $hooks->wrap($this);

		$this->appQuery = $hooks->wrap( $appQuery );
		$this->shiftsDuration = $hooks->wrap( $shiftsDuration );
		$this->shiftTypesPresenter = $hooks->wrap( $shiftTypesPresenter );

		$this->shiftTypes = array();
		if( $settings->get('shifttypes_show_title') ){
			$this->shiftTypes = $hooks->wrap( $shiftTypes )->findAll();
		}

		$this->settings = $hooks->wrap( $settings );
	}

	public function presentFullTime( SH4_Shifts_Model $model )
	{
		$return = array();

		$start = $model->getStart();
		$end = $model->getEnd();

		$this->t->setDateTimeDb($start);
		$startDate = $this->t->formatDateDb();
		$startTs = $this->t->getTimestamp();
		$this->t->setStartDay();
		$startDay = $this->t->getTimestamp();
		$startInDay = $startTs - $startDay;

		$this->t->setDateTimeDb($end);
		$endTs = $this->t->getTimestamp();
		$this->t->setStartDay();
		$startEndDay = $this->t->getTimestamp();
		$endInDay = $endTs - $startEndDay;

		if( (0 == $startInDay) && (0 == $endInDay) ){
			$endDate = $this->t->setDateTimeDb($end)->modify('-1 second')->formatDateDb();
			$return = $this->t->formatDateRange( $startDate, $endDate );
		}
		else {
			$return[] = $this->self->presentDate($model);
			$return[] = $this->self->presentTime($model);
			$return = join(' ', $return);
		}

		return $return;
	}

	public function presentTitle( SH4_Shifts_Model $model )
	{
		$ret = array();

		$ret[] = esc_html( $model->getCalendar()->getTitle() );
		$ret[] = $this->self->presentFullTime($model);
		$ret[] = esc_html( $model->getEmployee()->getTitle() );

		$ret = join(' &middot; ', $ret );
		return $ret;
	}

	public function presentDate( SH4_Shifts_Model $model )
	{
		$this->t->setDateTimeDb( $model->getStart() );
		$return = $this->t->formatDate();
		return $return;
	}

	public function presentRawTime( SH4_Shifts_Model $model )
	{
		$this->t->setDateTimeDb( $model->getStart() );
		$start = $this->t->formatTime();

		$this->t->setDateTimeDb( $model->getEnd() );
		$end = $this->t->formatTime();
		$endInDay = $model->getEndInDay();
		if( $endInDay > 24*60*60 ){
			$end = '&gt;' . $end;
		}

		$return = $start . ' - ' . $end;
		return $return;
	}

	public function presentBreak( SH4_Shifts_Model $model )
	{
		$return = NULL;
		$breakStart = $model->getBreakStart();
		if( NULL === $breakStart ){
			return $return;
		}

		$this->t->setDateTimeDb( $model->getBreakStart() );
		$start = $this->t->formatTime();

		$this->t->setDateTimeDb( $model->getBreakEnd() );
		$end = $this->t->formatTime();

		$return = $start . ' - ' . $end;
		return $return;
	}

	public function presentTime( SH4_Shifts_Model $model )
	{
		$ret = $this->self->presentRawTime( $model );

		$start = $model->getStart();
		$end = $model->getEnd();

		$startInDay = $this->t->setDateTimeDb( $start )->getTimeInDay();
		$startDate = $this->t->setDateTimeDb( $start )->formatDateDb();
		$endInDay = $this->t->setDateTimeDb( $end )->getTimeInDay();
		$endDate = $this->t->setDateTimeDb( $end )->formatDateDb();
		if( $endDate > $startDate ){
			$endInDay = 24*60*60 + $endInDay;
		}

		$breakStartInDay = null;
		$breakEndInDay = null;
		$breakStart = $model->getBreakStart();
		$breakEnd = $model->getBreakEnd();
		if( $breakStart && $breakEnd ){
			$breakStartInDay = $this->t->setDateTimeDb( $breakStart )->getTimeInDay();
			$breatStartDate = $this->t->setDateTimeDb( $breakStart )->formatDateDb();
			$breakEndInDay = $this->t->setDateTimeDb( $breakEnd )->getTimeInDay();
			$breakEndDate = $this->t->setDateTimeDb( $breakEnd )->formatDateDb();
			if( $breakEndDate > $breatStartDate ){
				$breakEndInDay = 24*60*60 + $breakEndInDay;
			}
		}

		$timeInDay = $startInDay . '-' . $endInDay;
		$shiftKey = $startInDay . '-' . $endInDay . '-' . $breakStartInDay . '-' . $breakEndInDay;

	// if we have a matching shifttype
		reset( $this->shiftTypes );
		foreach( $this->shiftTypes as $shiftType ){
			$thisKey = $shiftType->getStart() . '-' . $shiftType->getEnd() . '-' . $shiftType->getBreakStart() . '-' . $shiftType->getBreakEnd();
			if( $thisKey == $shiftKey ){
				// $ret = $shiftType->getTitle();
				$ret = $this->shiftTypesPresenter->presentTitle( $shiftType );
				break;
			}
		}

		return $ret;
	}

	public function presentStartDate( SH4_Shifts_Model $model )
	{
		$this->t->setDateTimeDb( $model->getStart() );
		$return = $this->t->formatDate();
		return $return;
	}

	public function presentStartTime( SH4_Shifts_Model $model )
	{
		$this->t->setDateTimeDb( $model->getStart() );
		$return = $this->t->formatTime();
		return $return;
	}

	public function presentEndDate( SH4_Shifts_Model $model )
	{
		$this->t->setDateTimeDb( $model->getEnd() );
		$return = $this->t->formatDate();
		return $return;
	}

	public function presentEndTime( SH4_Shifts_Model $model )
	{
		$this->t->setDateTimeDb( $model->getEnd() );
		$return = $this->t->formatTime();
		return $return;
	}

	public function presentStatus( SH4_Shifts_Model $model )
	{
		$return = $model->isPublished() ? '__Published__' : '__Draft__';
		return $return;
	}

	public function export( SH4_Shifts_Model $model, $withContacts = FALSE, $forUser = null )
	{
		$return = array();

		$return['id'] = $model->getId();

		$return['start_date'] = $this->presentStartDate( $model );
		$return['start_time'] = $this->presentStartTime( $model );
		$return['end_date'] = $this->presentEndDate( $model );
		$return['end_time'] = $this->presentEndTime( $model );

		$return['start'] = $model->getStart();
		$return['end'] = $model->getEnd();

		$breakStart = $model->getBreakStart();
		$breakEnd = $model->getBreakEnd();
		if( null !== $breakStart ){
			$return['break_start'] = $breakStart;
			$return['break_end'] = $breakEnd;
		}

		$this->shiftsDuration->reset();
		$this->shiftsDuration->add( $model );
		$duration = $this->shiftsDuration->getDurationHours();
		$return['duration'] = $duration;

		$calendar = $model->getCalendar();
		$return['calendar'] = $calendar->getTitle();
		$return['calendar_id'] = $calendar->getId();

		$employee = $model->getEmployee();
		$return['employee'] = $employee->getTitle();
		$return['employee_id'] = $employee->getId();

		$showUserEmployee = $this->settings->get('shifts_show_employee_user');
		if( $showUserEmployee ){
			$user = $this->appQuery->findUserByEmployee( $employee );
			$return['employee_username'] = $user ? $user->getUsername() : NULL;
		}

		if( $withContacts ){
			$email = NULL;
			$user = $this->appQuery->findUserByEmployee( $employee );
			if( $user ){
				$email = $user->getEmail();
			}
			$return['employee_email'] = $email;
		}

		$return['status'] = $this->presentStatus( $model );
		$return['status_id'] = $model->getStatus();

		// $return['start'] = $model->getStart();
		// $return['end'] = $model->getEnd();

		return $return;
	}
}