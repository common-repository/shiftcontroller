<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class SH4_ShiftTypes_Html_Admin_View_New
{
	public $self, $ui, $layout, $settings;

	public function __construct(
		HC3_Hooks $hooks,

		HC3_Settings $settings,
		HC3_Ui $ui,
		HC3_Ui_Layout1 $layout
		)
	{
		$this->ui = $ui;
		$this->layout = $layout;

		$this->settings = $hooks->wrap($settings);
		$this->self = $hooks->wrap($this);
	}

	public function renderDays()
	{
		$daysOptions = range( 2, 90, 1 );
		$daysOptions = array_combine( $daysOptions, $daysOptions );

		$out = $this->ui->makeForm(
			'admin/shifttypes/new/days',
			$this->ui->makeList()
				->add( $this->ui->makeInputText( 'title', '__Title__' )->bold() )
				->add( $this->ui->makeInputSelect('start', '__Min__' . ' (' . '__Days__' . ')', $daysOptions, 1) )
				->add( $this->ui->makeInputSelect('end', '__Max__' . ' (' . '__Days__' . ')', $daysOptions, 7) )

				->add( $this->ui->makeInputSubmit( '__Save__')->tag('primary') )
			);

		$this->layout
			->setContent( $out )
			->setBreadcrumb( $this->self->breadcrumb() )
			->setHeader( $this->self->header() )
			;

		$out = $this->layout->render();
		return $out;
	}

	public function renderHours()
	{
		$inputs = array();
		$inputs[] = $this->ui->makeInputText( 'title', '__Title__' )->bold();

		$value = array();
		$minTime = $this->settings->get('datetime_min_time');
		$defaultDuration = $this->settings->get('shifttypes_default_duration');
		if( (NULL !== $minTime) && (NULL !== $defaultDuration) ){
			$value = array( $minTime, $minTime + $defaultDuration );
		}
		$inputs[] = $this->ui->makeInputTimeRange( 'time', '__Time__', $value );

		$noBreak = $this->settings->get( 'shifttypes_nobreak' );

		if( ! $noBreak ){
			$inputs[] = $this->ui->makeCollapseCheckbox(
				'break_on',
				'__Lunch Break__' . '?',
				$this->ui->makeInputTimeRange( 'break', NULL)
				);
		}

		$inputs[] = $this->ui->makeInputSubmit( '__Save__')->tag('primary');

		$out = $this->ui->makeForm(
			'admin/shifttypes/new/hours',
			$this->ui->makeList( $inputs )
			);

		$this->layout
			->setContent( $out )
			->setBreadcrumb( $this->self->breadcrumb() )
			->setHeader( $this->self->header() )
			;

		$out = $this->layout->render();
		return $out;
	}

	public function breadcrumb()
	{
		$return = array();
		$return['admin'] = array( 'admin', '__Administration__' );
		$return['admin/shiftypes'] = array( 'admin/shifttypes', '__Shift Time__' );

		return $return;
	}

	public function header()
	{
		$out = '__Add New__';
		return $out;
	}
}