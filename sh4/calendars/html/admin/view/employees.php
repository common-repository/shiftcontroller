<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
#[AllowDynamicProperties]
class SH4_Calendars_Html_Admin_View_Employees
{
	public function __construct(
		HC3_Hooks $hooks,
		HC3_Ui $ui,
		HC3_Ui_Layout1 $layout,

		SH4_App_Query $appQuery,

		SH4_Employees_Query $employeesQuery,
		SH4_Employees_Presenter $employeesPresenter,

		SH4_Calendars_Query $calendarsQuery,
		SH4_Calendars_Presenter $calendarsPresenter
	)
	{
		$this->self = $hooks->wrap($this);
		$this->ui = $ui;
		$this->layout = $layout;

		$this->appQuery = $hooks->wrap( $appQuery );

		$this->employeesQuery = $hooks->wrap( $employeesQuery );
		$this->employeesPresenter = $hooks->wrap( $employeesPresenter );

		$this->calendarsQuery = $hooks->wrap( $calendarsQuery );
		$this->calendarsPresenter = $hooks->wrap( $calendarsPresenter );
	}

	public function render( $id )
	{
		$model = $this->calendarsQuery->findById($id);

		$currentEmployees = $this->appQuery->findEmployeesForCalendar( $model );
		$currentEmployeesIds = array_keys( $currentEmployees );

		$employees = $this->employeesQuery->findActive();

		$employeesView = array();
		foreach( $employees as $employee ){
			$employeeId = $employee->getId();
			$checked = in_array( $employeeId, $currentEmployeesIds ) ? TRUE : FALSE;

			$thisView = $this->employeesPresenter->presentTitle( $employee );
			$thisView = $this->ui->makeInputCheckbox( 'employee[]', $thisView, $employeeId, $checked );

			$descriptionView = $employee->getDescription();
			if( strlen($descriptionView) ){
				$maxDesc = 40;

				$shortDescriptionView = $descriptionView;
				if( strlen($descriptionView) > $maxDesc ){
					$shortDescriptionView = substr($descriptionView, 0, $maxDesc) . '...';
				}

				if( strlen($descriptionView) > $maxDesc ){
					$descriptionView = $this->ui->makeCollapse( $shortDescriptionView, $descriptionView )
						->arrow( NULL )
						->hideToggle()
						;
				}
				$descriptionView = $this->ui->makeBlock( $descriptionView )
					->tag('font-style', 'italic')
					->tag('font-size', 2)
					;

				$thisView = $this->ui->makeList( array($thisView, $descriptionView) )
					->gutter(1)
					;
			}

			$thisView = $this->ui->makeBlock( $thisView )
				->tag('border')
				->tag('padding', 1)
				;

			if( $checked ){
				$thisView
					->tag('border-color', 'olive')
					;
			}

			$employeesView[] = $thisView;
		}

		$out = $this->ui->makeGrid();
		foreach( $employeesView as $v ){
			$out->add( $v, 3, 12 );
		}

		$out = $this->ui->makeBlock( $out )
			->addAttr( 'id', 'sh4-calendars-employees' )
			;
		$toggleAll = $this->ui->makeInputCheckbox( 'toggle_all', '__Toggle All__' )
			->addAttr( 'class', 'sh4-calendars-employees-select-all' )
			;

		$buttons = $this->ui->makeInputSubmit( '__Save__')
			->tag('primary')
			;
		$out = $this->ui->makeList( array($toggleAll, $out, $buttons) );

		$out = $this->ui->makeForm(
			'admin/calendars/' . $id . '/employees',
			$out
			);

	// add check all
		$js = $this->renderJs();
		$out = $this->ui->makeCollection( array($js, $out) );

		$this->layout
			->setContent( $out )
			->setBreadcrumb( $this->self->breadcrumb($model) )
			->setHeader( $this->self->header($model) )
			;

		$out = $this->layout->render();
		return $out;
	}

	public function header( SH4_Calendars_Model $model )
	{
		$out = '__Employees__';
		return $out;
	}

	public function breadcrumb( SH4_Calendars_Model $model )
	{
		$calendarId = $model->getId();
		$calendarTitle = $this->calendarsPresenter->presentTitle( $model );

		$return = array();
		$return['admin'] = array( 'admin', '__Administration__' );
		$return['calendars'] = array( 'admin/calendars', '__Calendars__' );
		$return['calendars/edit'] = $calendarTitle;
		return $return;
	}

	public function renderJs()
	{
		ob_start();
?>

<script language="JavaScript">
( function(){

document.addEventListener('DOMContentLoaded', function()
{
	var self = this;
	var el = document.getElementById( 'sh4-calendars-employees' );

	this.toggleAll = function( e ){
		var checkers = el.getElementsByTagName( 'input' );
		for( ii = 0; ii < checkers.length; ii++ ){
			checkers[ii].checked = ! checkers[ii].checked;
		}
	};

	var togglers = document.getElementsByClassName( 'sh4-calendars-employees-select-all' );
	for( ii = 0; ii < togglers.length; ii++ ){
		togglers[ii].addEventListener( 'change', self.toggleAll );
	}
});

})();

</script>

<?php 
		return ob_get_clean();
	}
}