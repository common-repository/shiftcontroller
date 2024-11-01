<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
#[AllowDynamicProperties]
class SH4_Conf_Html_Admin_View_Datetime
{
	protected $ui = NULL;
	protected $settings = NULL;

	public function __construct(
		HC3_Hooks $hooks,

		HC3_Ui $ui,
		HC3_Ui_Layout1 $layout,
		HC3_Time $t,

		HC3_Settings $settings
		)
	{
		$this->ui = $ui;
		$this->layout = $layout;
		$this->t = $t;

		$this->settings = $hooks->wrap($settings);
		$this->self = $hooks->wrap($this);
	}

	public function render()
	{
		$values = array();
		$pnames = array(
			'datetime_date_format', 'datetime_time_format', 'datetime_week_starts', 'full_day_count_as',
			'skip_weekdays', 'datetime_timezone',
			'datetime_min_time', 'datetime_max_time', 'datetime_step',
			'datetime_hide_schedule_reports', 'datetime_hide_bottom_date_navigation', 'datetime_month_slim_view', 'datetime_compact_shift_label',
			'default_schedule_view', 'default_schedule_groupby', 'datetime_n_weeks',
			'pref_csv_separator'
		);
		foreach( $pnames as $pname ){
			$values[$pname] = $this->settings->get($pname);
		}
		$inputs = array();

		$inputs[] = $this->ui->makeInputSelect(
			'datetime_date_format',
			'__Date Format__',
			array(
				'd/m/Y'	=> date('d/m/Y'),
				'd-m-Y'	=> date('d-m-Y'),
				'n/j/Y'	=> date('n/j/Y'),
				'Y/m/d'	=> date('Y/m/d'),
				'd.m.Y'	=> date('d.m.Y'),
				'j M Y'	=> date('j M Y'),
				'Y-m-d'	=> date('Y-m-d'),
				),
			$values['datetime_date_format']
			);

		$options = array( 'g:ia', 'g:i A', '12short', '12xshort', 'H:i', '24short', );
		$inputOptions = array();

		$testTimes = array( '202103130800', '202103131400', '202103131515' );

		foreach( $options as $e ){
			$view = array( );
			foreach( $testTimes as $test ){
				$this->t->setDateTimeDb( $test );
				$view[] = $this->t->formatTime( $e );
			}
			$view = join( ', ', $view );
			$inputOptions[ $e ] = $view;
		}

		$inputs[] = $this->ui->makeInputSelect(
			'datetime_time_format',
			'__Time Format__',
			$inputOptions,
			$values['datetime_time_format']
			);

		$inputs[] = $this->ui->makeInputSelect(
			'datetime_week_starts',
			'__Week Starts On__',
			array(
				0	=> '__Sun__',
				1	=> '__Mon__',
				2	=> '__Tue__',
				3	=> '__Wed__',
				4	=> '__Thu__',
				5	=> '__Fri__',
				6	=> '__Sat__',
				),
			$values['datetime_week_starts']
			);

		$countAsOptionsValues = range( 1, 24 );
		$countAsOptionsKeys = array();
		foreach( $countAsOptionsValues as $v ){
			$countAsOptionsKeys[] = $v * (60 * 60);
		}
		$countAsOptions = array_combine( $countAsOptionsKeys, $countAsOptionsValues );

		$inputs[] = $this->ui->makeInputSelect(
			'full_day_count_as',
			'__Full Day Counts As__' . ' (' . '__Hours__' . ')',
			$countAsOptions,
			$values['full_day_count_as']
			);

		$inputs = $this->ui->makeGrid( $inputs );

		$moreInputs = array();

	// min/max time
		$moreInputs2 = array();
		$moreInputs2[] = $this->ui->makeInputTime(
			'datetime_min_time',
			'__Min Start Time__',
			$values['datetime_min_time'],
			'nolimit'
			);
		$moreInputs2[] = $this->ui->makeInputTime(
			'datetime_max_time',
			'__Max End Time__',
			$values['datetime_max_time'],
			'nolimit'
			);

		$stepOptions = array( 5*60 => 5, 10*60 => 10, 15*60 => 15, 20*60 => 20, 30*60 => 30, 60*60 => 60 );
		$moreInputs2[] = $this->ui->makeInputSelect(
			'datetime_step',
			'__Time Increment__' . ' (' . '__Minutes__' . ')',
			$stepOptions,
			$values['datetime_step']
			);

		$moreInputs2 = $this->ui->makeGrid( $moreInputs2 );
		$moreInputs[] = $moreInputs2;

	// timezone
		$timezoneOptions = $this->t->getTimezones();

		$defaultLabel = $this->t->getDefaultTimezone();
		$defaultLabel = ' - ' . '__Default Timezone__' . ' - ' . ' [' . $defaultLabel . ']';

		$timezoneOptions = array_merge( array('' => $defaultLabel), $timezoneOptions );

		$moreInputs[] = $this->ui->makeInputSelect(
			'datetime_timezone',
			'__Timezone__',
			$timezoneOptions,
			$values['datetime_timezone']
			);

		$this->t->setNow();
		$currentTimeView = $this->t->formatDateWithWeekday()  . ' ' . $this->t->formatTime();
		$currentTimeView = '<div class="hc-muted2">' . $currentTimeView . '</div>';

		$moreInputs[] = $currentTimeView;

	// disabled weekdays
		$disabledWeekdays = array();
		$weekdays = $this->t->getWeekdays();

		$currentDisabled = $this->settings->get( 'skip_weekdays', TRUE );
		foreach( $weekdays as $wkd => $wkdName ){
			$isChecked = in_array( $wkd, $currentDisabled ) ? TRUE : FALSE;
			$disabledWeekdays[] = $this->ui->makeInputCheckbox( 'skip_weekdays[]', $wkdName, $wkd, $isChecked );
		}
		$disabledWeekdays = $this->ui->makeListInline( $disabledWeekdays );
		$disabledWeekdays = $this->ui->makeLabelled( '__Days Of Week Disabled__', $disabledWeekdays );
		$moreInputs[] = $disabledWeekdays;

	// hide schedule reports
		$hideScheduleReports = $this->ui->makeInputCheckbox( 'datetime_hide_schedule_reports', '__Hide Hours Reports In Schedule View__', 1, $values['datetime_hide_schedule_reports'] );
		$moreInputs[] = $hideScheduleReports;

	// hide bottom date navigation
		$hideBottomDateNavigation = $this->ui->makeInputCheckbox( 'datetime_hide_bottom_date_navigation', '__Hide Bottom Date Navigation In Schedule View__', 1, $values['datetime_hide_bottom_date_navigation'] );
		$moreInputs[] = $hideBottomDateNavigation;

	// month slim view
		$moreInputs[] = $this->ui->makeInputCheckbox( 'datetime_month_slim_view', '__Slim Month Schedule Grouped View__', 1, $values['datetime_month_slim_view'] );

		$thisOptions = array( 'abbr' => '__Abbreviation__', 'none' => '__Space__' );
		$moreInputs[] = $this->ui->makeInputSelect( 'datetime_compact_shift_label', '__Shift Label In Compact View__', $thisOptions, $values['datetime_compact_shift_label'] );

	// default schedule view
		$moreInputs3 = array();
		// $defaultView = $this->ui->makeInputCheckbox( 'datetime_hide_schedule_reports', '__Hide Hours Reports In Schedule View__', 1, $values['datetime_hide_schedule_reports'] );
		$defaultViewOptions = array( 'day' => '__Day__', 'week' => '__Week__', 'month' => '__Month__', '4weeks' => '__4 Weeks__', 'list' => '__List__' );
		$defaultView = $this->ui->makeInputSelect(
			'default_schedule_view',
			'__Default Schedule View__',
			$defaultViewOptions,
			$values['default_schedule_view']
		);
		$moreInputs3[] = $defaultView;

		$defaultGroupbyOptions = array( 'employee' => '__Employee__', 'calendar' => '__Calendar__', 'none' => '__None__' );
		$defaultGroupby = $this->ui->makeInputSelect(
			'default_schedule_groupby',
			'__Default Schedule Group By__',
			$defaultGroupbyOptions,
			$values['default_schedule_groupby']
		);
		$moreInputs3[] = $defaultGroupby;

		$nWeeksOptions = array( 3 => 3, 4 => 4, 5 => 5, 6 => 6  );
		$nWeeks = $this->ui->makeInputSelect(
			'datetime_n_weeks',
			'__Multiple Weeks View__',
			$nWeeksOptions,
			$values['datetime_n_weeks']
		);
		$moreInputs3[] = $nWeeks;

		$moreInputs3 = $this->ui->makeGrid( $moreInputs3 );
		$moreInputs[] = $moreInputs3;

		$moreInputs[] = $this->ui->makeInputSelect(
			'pref_csv_separator',
			'__CSV Delimiter__',
			array(
				','	=> ',',
				';'	=> ';',
				),
			$values['pref_csv_separator']
		);

		$moreInputs = $this->ui->makeList( $moreInputs );

		$inputs = $this->ui->makeList( array($inputs, $moreInputs) );

		$out = $this->ui->makeForm(
			'admin/conf/datetime',
			$this->ui->makeList(
				array( $inputs, $this->ui->makeInputSubmit( '__Save__')->tag('primary') )
				)
			);

		$this->layout
			->setContent( $out )
			->setBreadcrumb( $this->self->breadcrumb() )
			->setHeader( $this->self->header() )
			// ->setMenu( $this->self->menu() )
			;

		$out = $this->layout->render();
		return $out;
	}

	public function header()
	{
		$out = '__Date and Time__';
		return $out;
	}

	public function breadcrumb()
	{
		$return = array();
		$return['admin'] = array( 'admin', '__Administration__' );
		return $return;
	}
}