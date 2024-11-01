<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
#[AllowDynamicProperties]
class SH4_Employees_Html_Admin_View_Settings
{
	public function __construct(
		HC3_Hooks $hooks,
		HC3_Ui $ui,
		HC3_Ui_Layout1 $layout,
		HC3_Post $post,
		HC3_Settings $settings
	)
	{
		$this->ui = $ui;
		$this->layout = $layout;
		$this->self = $hooks->wrap($this);
		$this->post = $hooks->wrap($post);
		$this->settings = $hooks->wrap($settings);
	}

	public function get()
	{
		$form = $this->ui->makeForm(
			'admin/employees/settings',
			$this->self->form()
			);

		$this->layout
			->setContent( $form )
			->setBreadcrumb( $this->self->breadcrumb() )
			->setHeader( $this->self->header() )
			;

		$out = $this->layout->render();
		return $out;
	}

	public function post()
	{
		$take = array( 'shifts_show_employee_user' );
		foreach( $take as $k ){
			$this->settings->set( $k, $this->post->get($k) );
		}

		$v = $this->post->get('shifts_employee_create_mindate_qty') . ' ' . $this->post->get('shifts_employee_create_mindate_measure');
		$this->settings->set( 'shifts_employee_create_mindate', $v );

		$v = $this->post->get('shifts_employee_create_maxdate_qty') . ' ' . $this->post->get('shifts_employee_create_maxdate_measure');
		$this->settings->set( 'shifts_employee_create_maxdate', $v );

		$return = array( 'admin/employees/settings', '__Settings Updated__' );
		return $return;
	}

	public function breadcrumb()
	{
		$return = array();
		$return['admin'] = array( 'admin', '__Administration__' );
		$return['admin/employees'] = array( 'admin/employees', '__Employees__' );
		return $return;
	}

	public function menu()
	{
		$return = array();
		return $return;
	}

	public function header()
	{
		$out = '__Settings__';
		return $out;
	}

	public function form()
	{
		$values = array();
		$pnames = array( 'shifts_show_employee_user', 'shifts_employee_create_mindate', 'shifts_employee_create_maxdate' );
		foreach( $pnames as $pname ){
			$values[$pname] = $this->settings->get($pname);
		}

		$inputs = array();

		$inputs[] = $this->ui->makeInputCheckbox( 'shifts_show_employee_user', '__Show Employee Username In Schedule__', 1, $values['shifts_show_employee_user'] );


		$help = '__If you allow employees to create shifts, they can do this within the following date range:__';
		$inputs[] = $help;

		$option = [ 'day' => '__Day__', 'week' => '__Week__', 'month' => '__Month__', 'year' => '__Year__' ];

		$minDate = [];
		$vs = explode( ' ', $values['shifts_employee_create_mindate'] );
		$minDate[] = $this->ui->makeInputText( 'shifts_employee_create_mindate_qty', '', $vs[0] );
		$minDate[] = $this->ui->makeInputSelect( 'shifts_employee_create_mindate_measure', '', $option, $vs[1] );
		$minDate = $this->ui->makeListInline( $minDate );
		$minDate = $this->ui->makeLabelled( '__Earliest Date__', $minDate );

		$examples = [];
		$examples[] = '0 - ' . '__Beginning of today__';
		$examples[] = '1 ' . '__Day__' . ' - ' . '__Beginning of tomorrrow__';
		$examples[] = '0 ' . '__Week__' . ' - ' . '__Beginning of current week__';
		$examples[] = '1 ' . '__Week__' . ' - ' . '__Beginning of next week__';
		$examples[] = '-1 ' . '__Week__' . ' - ' . '__Beginning of previous week__';
		$examples[] = '0 ' . '__Month__' . ' - ' . '__Beginning of current month__';

		$help = '<small>__Examples__</small><br>';
		$help .= '<ul>';
		foreach( $examples as $e ) $help .= '<li>' . $e . '</li>';
		$help .= '</ul>';

		$minDate = $this->ui->makeList( array($minDate, $help) )->gutter(0);

		$maxDate = [];
		$vs = explode( ' ', $values['shifts_employee_create_maxdate'] );
		$maxDate[] = $this->ui->makeInputText( 'shifts_employee_create_maxdate_qty', '', $vs[0] );
		$maxDate[] = $this->ui->makeInputSelect( 'shifts_employee_create_maxdate_measure', '', $option, $vs[1] );
		$maxDate = $this->ui->makeListInline( $maxDate );
		$maxDate = $this->ui->makeLabelled( '__Latest Date__', $maxDate );

		$examples = [];
		$examples[] = '0 - ' . '__End of today__';
		$examples[] = '1 ' . '__Day__' . ' - ' . '__End of tomorrrow__';
		$examples[] = '0 ' . '__Week__' . ' - ' . '__End of current week__';
		$examples[] = '1 ' . '__Week__' . ' - ' . '__End of next week__';
		$examples[] = '0 ' . '__Month__' . ' - ' . '__End of current month__';

		$help = '<small>__Examples__</small><br>';
		$help .= '<ul>';
		foreach( $examples as $e ) $help .= '<li>' . $e . '</li>';
		$help .= '</ul>';

		$maxDate = $this->ui->makeList( array($maxDate, $help) )->gutter(0);

		$inputDates = $this->ui->makeGrid( array($minDate, $maxDate) );

		$inputs[] = $inputDates;
		// $maxDate = $this->ui->makeInputSelect( 'shifts_employee_create_after' )


		$inputs = $this->ui->makeList( $inputs );

		$buttons = $this->ui->makeInputSubmit( '__Save__')
			->tag('primary')
			;

		$out = $this->ui->makeList()
			->add( $inputs )
			->add( $buttons )
			;

		return $out;
	}
}