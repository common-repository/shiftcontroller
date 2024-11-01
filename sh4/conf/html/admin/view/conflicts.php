<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
#[AllowDynamicProperties]
class SH4_Conf_Html_Admin_View_Conflicts
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
		$pnames = array(
			'conflicts_calendar_only',
		);
		foreach( $pnames as $pname ){
			$values[$pname] = $this->settings->get($pname);
		}
		$inputs = array();

		$inputs[] = $this->ui->makeInputCheckbox(
			'conflicts_calendar_only',
			'__Conflict only if overlapping shifts are in the same calendar__',
			1,
			$values['conflicts_calendar_only']
			);

		$inputs = $this->ui->makeList( $inputs );

		$out = $this->ui->makeForm(
			'admin/conf/conflicts',
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
		$out = '__Conflicts__';
		return $out;
	}

	public function breadcrumb()
	{
		$return = array();
		$return['admin'] = array( 'admin', '__Administration__' );
		return $return;
	}
}