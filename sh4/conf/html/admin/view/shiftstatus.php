<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
#[AllowDynamicProperties]
class SH4_Conf_Html_Admin_View_ShiftStatus
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
			'shifts_no_draft', 'shifts_confirm_publish',
		);
		foreach( $pnames as $pname ){
			$values[$pname] = $this->settings->get($pname);
		}
		$inputs = array();

		$inputs[] = $this->ui->makeInputCheckbox(
			'shifts_no_draft',
			'__No Draft Status__',
			1,
			$values['shifts_no_draft']
			);
		$inputs[] = $this->ui->makeInputCheckbox(
			'shifts_confirm_publish',
			'__Confirm Publish Action__',
			1,
			$values['shifts_confirm_publish']
			);

		$inputs = $this->ui->makeList( $inputs );

		$out = $this->ui->makeForm(
			'admin/conf/shiftstatus',
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
		$out = '__Shift Status__';
		return $out;
	}

	public function breadcrumb()
	{
		$return = array();
		$return['admin'] = array( 'admin', '__Administration__' );
		return $return;
	}
}