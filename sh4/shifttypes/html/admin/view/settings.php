<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class SH4_ShiftTypes_Html_Admin_View_Settings
{
	public $self, $ui, $layout, $settings;

	public function __construct(
		HC3_Hooks $hooks,

		HC3_Ui $ui,
		HC3_Ui_Layout1 $layout,

		HC3_Settings $settings
		)
	{
		$this->ui = $ui;
		$this->layout = $layout;

		$this->settings = $hooks->wrap($settings);
		$this->self = $hooks->wrap($this);
	}

	public function render()
	{
		$pnames = array( 'shifttypes_show_title', 'shifttypes_nobreak', 'shifttypes_default_duration' );
		foreach( $pnames as $pname ){
			$values[$pname] = $this->settings->get($pname);
		}
		$inputs = array();

		$inputs[] = $this->ui->makeInputCheckbox(
			'shifttypes_show_title',
			'__Show Shift Time Title In Schedule__',
			1,
			$values['shifttypes_show_title']
			);

		$inputs[] = $this->ui->makeInputCheckbox(
			'shifttypes_nobreak',
			'__No Lunch Break__',
			1,
			$values['shifttypes_nobreak']
			);

		$options = range( 0.5, 24, 0.5 );
		$defaultDurationOptions = array();
		foreach( $options as $e ){
			$defaultDurationOptions[ $e*60*60 ] = $e;
		}
		$inputs[] = $this->ui->makeInputSelect(
			'shifttypes_default_duration',
			'__Default Shift Duration__' . ' (' . '__Hours__' . ')',
			$defaultDurationOptions,
			$values['shifttypes_default_duration']
			);

		$inputs = $this->ui->makeList( $inputs );

		$out = $this->ui->makeForm(
			'admin/shifttypes/settings',
			$this->ui->makeList(
				array( $inputs, $this->ui->makeInputSubmit('__Save__')->tag('primary') )
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
		$out = '__Settings__';
		return $out;
	}

	public function breadcrumb()
	{
		$return = array();
		$return['admin'] = array( 'admin', '__Administration__' );
		$return['admin/shiftypes'] = array( 'admin/shifttypes', '__Shift Time__' );
		return $return;
	}
}