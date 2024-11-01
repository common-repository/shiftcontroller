<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
#[AllowDynamicProperties]
class SH4_Shifts_View_Widget
{
	protected $ui = NULL;
	protected $t = NULL;
	protected $shiftTemplates = array();

	public function __construct(
		HC3_Hooks $hooks,
		HC3_Ui $ui,
		HC3_Time $t,
		HC3_Settings $settings,
		HC3_Users_Presenter $usersPresenter,
		SH4_App_Query $appQuery,
		HC3_Auth $auth,

		SH4_Shifts_View_Common $shiftsViewCommon,

		SH4_ShiftTypes_Query $shiftTypes,
		SH4_ShiftTypes_Presenter $shiftTypesPresenter,
		SH4_Employees_Presenter	$employeesPresenter,

		SH4_Calendars_Presenter	$calendarPresenter,

		SH4_Shifts_Conflicts $conflicts
		)
	{
		$this->self = $hooks->wrap( $this );
		$this->settings = $hooks->wrap( $settings );
		$this->auth = $hooks->wrap( $auth );
		$this->ui = $ui;
		$this->t = $t;

		$this->conflicts = $hooks->wrap( $conflicts );
		$this->usersPresenter = $hooks->wrap( $usersPresenter );
		$this->employeesPresenter = $hooks->wrap( $employeesPresenter );
		$this->calendarPresenter = $hooks->wrap( $calendarPresenter );

		// $this->shiftTypes = array();
		$this->shiftTypesPresenter = $hooks->wrap($shiftTypesPresenter);
		$this->shiftsViewCommon = $hooks->wrap( $shiftsViewCommon );

		$this->shiftTypes = array();
		if( $settings->get('shifttypes_show_title') ){
			$this->shiftTypes = $hooks->wrap( $shiftTypes )->findAll();
		}
		$this->appQuery = $hooks->wrap($appQuery);
	}

	public function prepare( SH4_Shifts_Model $model, $iknow = array() )
	{
		static $cache = array();

		$id = $model->getId();

		if( isset($cache[$id]) ){
			return $cache[$id];
		}

		$out = array();

		$calendar = $model->getCalendar();
		$color = $calendar->getColor();

		$employee = $model->getEmployee();

		$start = $model->getStart();
		$this->t->setDateTimeDb($start);
		$startDate = $this->t->formatDateDb();

		$dateView = $this->t->formatDate();
		$weekdayView = $this->t->getWeekdayName();
		$dateView = $weekdayView . ', ' . $dateView;

		$startView = $this->t->formatTime();
		$startInDay = $model->getStartInDay();
		$endInDay = $model->getEndInDay();

		$end = $model->getEnd();
		$endView = $this->t->setDateTimeDb($end)->formatTime();

		$timeInDay = $startInDay . '-' . $endInDay;

		$breakStartInDay = NULL;
		$breakEndInDay = NULL;
		$breakStart = $model->getBreakStart();
		$breakEnd = $model->getBreakEnd();

		if( $breakStart && $breakEnd ){
			$this->t->setDateTimeDb( $breakStart );
			$breakStartTs = $this->t->getTimestamp();
			$this->t->setStartDay();
			$startDay = $this->t->getTimestamp();
			$breakStartInDay = $breakStartTs - $startDay;

			$this->t->setDateTimeDb( $breakEnd );
			$breakEndTs = $this->t->getTimestamp();
			$breakEndInDay = $breakEndTs - $startDay;
		}
		$shiftKey = $startInDay . '-' . $endInDay . '-' . $breakStartInDay . '-' . $breakEndInDay;

		if( (0 == $startInDay) && (0 == $endInDay) ){
			$endDate = $this->t->setDateTimeDb($end)->modify('-1 second')->formatDateDb();
			if( $startDate != $endDate ){
				$dateView = $this->t->formatDateRange( $startDate, $endDate );
				// $dateView = $weekdayView . ', ' . $dateView;
				$iknow = HC3_Functions::removeFromArray( $iknow, 'date' );
			}
		}

		if( ! in_array('date', $iknow) ){
			$out['date'] = $dateView;
		}

		if( ! in_array('time', $iknow) ){
			$hasAllDayType = NULL;

			$timeView = NULL;
			$timeMiscView = NULL;
			reset( $this->shiftTypes );
			foreach( $this->shiftTypes as $shiftType ){
				$thisTimeInDay = $shiftType->getStart() . '-' . $shiftType->getEnd();

				if( '0-86400' == $thisTimeInDay ){
					$hasAllDayType = $shiftType->getTitle();
				}

				// if( $thisTimeInDay == $timeInDay ){
					// $timeView = $shiftType->getTitle();
					// $timeMiscView = $startView . ' - ' . $endView;
					// break;
				// }

				$thisKey = $shiftType->getStart() . '-' . $shiftType->getEnd() . '-' . $shiftType->getBreakStart() . '-' . $shiftType->getBreakEnd();
				if( $thisKey == $shiftKey ){
					// $timeView = $shiftType->getTitle();
					$timeView = $this->shiftTypesPresenter->presentTitle( $shiftType );
					$timeMiscView = $startView . ' - ' . $endView;
					break;
				}
			}

			if( ! $timeView ){
				if( (0 == $startInDay) && ((24*60*60 == $endInDay) OR (0 == $endInDay)) ){
					$timeView = $hasAllDayType ? $hasAllDayType : '__All Day__';
				}
				else {
				// overnight shift
					if( $endInDay > 24*60*60 ){
						$endView = '&gt;' . $endView;
					}

					$timeView = $startView . ' - ' . $endView;
				}
			}

			$out['time'] = $timeView;
			if( $timeMiscView ){
				// $out['timemisc'] = $timeMiscView;
			}
		}

		if( ! in_array('calendar', $iknow) ){
			$calendarView = $this->calendarPresenter->presentTitle( $calendar );

			if( $model->isDraft() ){
				$calendarView
					->addAttr('class', 'hc-bg-striped')
					;
			}

			$out['calendar'] = $calendarView;
		}

		if( ! in_array('employee', $iknow) ){
			$employeeView = $this->employeesPresenter->presentTitle( $employee );
			$out['employee'] = $employeeView;
		}

		$cache[$id] = $out;

		return $out;
	}

	public function renderTitle( $model )
	{
		$prepared = $this->self->prepare( $model );

		$out = array();
		foreach( $prepared as $k => $v ){
			$v = strip_tags( $v );
			$out[] = $v;
		}
		$out = join( "\n", $out );
		return $out;
	}

	public function render( SH4_Shifts_Model $model, $iknow = array(), $hori = FALSE, $noLink = FALSE, $groupedQty = NULL )
	{
		$out = $this->self->prepare( $model, $iknow );
		$return = $this->self->renderPrepared( $model, $out, $iknow, $hori, $noLink, $groupedQty );
		return $return;
	}

	public function renderCompact( SH4_Shifts_Model $model, $iknow = array(), $groupedQty = NULL )
	{
		$id = $model->getId();
		$calendar = $model->getCalendar();
		$color = $calendar->getColor();
		$employee = $model->getEmployee();

		$out = '&nbsp;';

		$label = null;

		if( ! in_array('calendar', $iknow) ){
			$label = $calendar->getTitle();
		}
		elseif( ! in_array('employee', $iknow) ){
			$label = $employee->getTitle();
		}

		$what = $this->settings->get( 'datetime_compact_shift_label' );
		if( 'none' == $what ){
			$label = null;
		}

		if( null !== $label ){
	// abbreviation
			$label = str_replace( '-', ' ', $label );
			$label = str_replace( '_', ' ', $label );
			$label = str_replace( '  ', ' ', $label );
			$label = str_replace( '  ', ' ', $label );

			$label = explode( ' ', $label );

			$finalLabel = array();
			foreach( $label as $l ){
				$l = substr( $l, 0, 1 );
				$finalLabel[] = $l;
				if( count($finalLabel) >= 2 ){
					break;
				}
			}

			$label = join( '', $finalLabel );
		}
		else {
			$label = '&nbsp;';
		}

		if( NULL !== $groupedQty ){
			$label = $groupedQty . 'x';
		}

		$out = $this->ui->makeBlock( $label )
			->padding(1)
			->tag('nowrap')
			->tag('align', 'center')
			// ->tag('font-size', 2)
			;

		$out
			->tag('border')
			;


		$conflicts = array();
		$currentUser = $this->auth->getCurrentUser();
		$currentUserId = $currentUser->getId();
		if( $currentUserId ){
			$conflicts = $this->conflicts->get( $model );
		}

		if( $conflicts ){
			$out
				->tag('border-color', 'red')
			;
		}
		else {
			$out
				->tag('border-color', $color)
			;
		}

		if( $model->isDraft() ){
			$out
				->addAttr('style', 'border-style: dashed;')
				;
		}

		$finalOut = array();
		$finalOut[] = $out;

		$out = $this->ui->makeBlock( $this->ui->makeCollection($finalOut) )
			->addAttr('style', 'position: relative;')
			;

		$out
			->addAttr('class', 'hc-bg-lighten-2')
			;

		$out = $this->ui->makeBlock( $out )
			->tag('bgcolor', $color)
			;
		if( $model->isDraft() ){
			$out
				->addAttr('class', 'hc-bg-striped')
				;
			$out
				->tag('muted', 1)
				;
		}

		$title = $this->self->renderTitle( $model );
		$out
			->addAttr('title', $title)
			;

		return $out;
	}

	public function renderPrepared( SH4_Shifts_Model $model, $out, $iknow = array(), $hori = FALSE, $noLink = FALSE, $groupedQty = NULL )
	{
		$colorFont = TRUE;
		$colorFont = FALSE;

		$id = $model->getId();
		$calendar = $model->getCalendar();

		if( $calendar->isAvailability() ){
			$iknow[] = 'calendar';
		}

		$color = $calendar->getColor();
		$icons = $this->shiftsViewCommon->icons( $model, $iknow );

	// conflicts
		$conflicts = array();
		if( ! in_array('conflicts', $iknow) ){
			$currentUser = $this->auth->getCurrentUser();
			$currentUserId = $currentUser->getId();
			if( $currentUserId ){
				$conflicts = $this->conflicts->get( $model );
			}
		}

	// highlight
		// $highlight = array('employee', 'time', 'date');
		$highlight = array('employee', 'time');

		if( $calendar->isShift() ){
			foreach( $highlight as $hl ){
				if( ! array_key_exists($hl, $out) ){
					continue;
				}
				$out[$hl] = $this->ui->makeSpan( $out[$hl] )
					// ->tag('font-size', 4)
					->tag('font-style', 'bold')
					;
			}
		}

		$mute = array('timemisc');

		foreach( $mute as $mt ){
			if( ! array_key_exists($mt, $out) ){
				continue;
			}
			$out[$mt] = $this->ui->makeSpan( $out[$mt] )
				->tag('font-size', 2)
				->tag('muted')
				;
		}

		$keys = array_keys($out);
		$firstKey = array_shift($keys);

		if( NULL !== $groupedQty ){
			$out[$firstKey] = $groupedQty . ' x ' . $out[$firstKey];
		}

	// LINK
		if( $id < 0 ) $noLink = TRUE;

		if( ! $noLink ){
			$to = 'shifts/' . $id;
			$out[$firstKey] = $this->ui->makeAhref( $to, $out[$firstKey] )
				// ->tag('unstyled-link')
				->tag('block')
				->tag('print')
				->addAttr('class', 'sh4-widget-loader')
				;

			if( $colorFont ){
				if( ! $calendar->isAvailability() ){
					$out[$firstKey]
						->tag('color', $color)
						->addAttr( 'class', 'hc-underline' );
						;
				}
			}
		}

		if( $hori ){
			$return = $this->ui->makeGrid();
			$width = '1-' . count($out);
			foreach( $out as $o ){
				$return->add( $o, $width, 12 );
			}
			$out = $return;
		}
		else {
			$out = $this->ui->makeList($out)->gutter(0);
		}

		$out = $this->ui->makeBlock( $out )
			->padding(1)
			// ->tag('font-size', 2);
			->tag('nowrap')
			;

		$out
			->tag('border')
			;

// return $out;

		if( $conflicts ){
			$out
				->tag('border-color', 'red')
			;
		}
		else {
			$out
				->tag('border-color', $color)
			;
		}

		if( $model->isDraft() ){
			$out
				->addAttr('style', 'border-style: dashed;')
				;
		}

		$finalOut = array();

		if( $icons ){
			$iconsList = array();
			foreach( $icons as $field => $thisIcons ){
				$iconsList = array_merge( $iconsList, $thisIcons );
			}

			$icons = $this->ui->makeListInline( $iconsList )->gutter(1);
			$icons = $this->ui->makeBlock($icons)
				->addAttr('style', 'position: absolute; right:.2em; top:.2em; z-index: 100;')
				;
			$finalOut[] = $icons;
		}

		$finalOut[] = $out;

		$out = $this->ui->makeBlock( $this->ui->makeCollection($finalOut) )
			->addAttr('style', 'position: relative;')
			;

		$out
			->addAttr('class', 'hc-bg-lighten-2')
			;

		$out = $this->ui->makeBlock( $out )
			// ->tag('bgcolor', $color)
			;

		if( ! $calendar->isAvailability() ){
			$out
				->tag('bgcolor', $color)
				;

			if( $colorFont ){
				$out
					->tag('color', $color)
					;
			}
		}

		if( $model->isDraft() ){
			if( ! $calendar->isAvailability() ){
				$out
					->addAttr('class', 'hc-bg-striped')
					;
			}
			$out
				->tag('muted', 1)
				;
		}


		$title = $this->self->renderTitle( $model );
		$out
			->addAttr('title', $title)
			;

		return $out;
	}
}