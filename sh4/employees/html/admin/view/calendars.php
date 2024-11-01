<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
#[AllowDynamicProperties]
class SH4_Employees_Html_Admin_View_Calendars
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
		$model = $this->employeesQuery->findById($id);

		$current = $this->appQuery->findCalendarsForEmployee( $model );
		$currentIds = array_keys( $current );

		$calendars = $this->calendarsQuery->findActive();

		$calendarsView = array();
		foreach( $calendars as $calendar ){
			$calendarId = $calendar->getId();
			$checked = in_array( $calendarId, $currentIds ) ? TRUE : FALSE;

			$title = $this->calendarsPresenter->presentTitle( $calendar );
			// $thisView = $this->ui->makeInputCheckbox( 'calendar[]', $thisView, $calendarId, $checked );
			$thisView = $this->ui->makeInputCheckbox( 'calendar[]', NULL, $calendarId, $checked );
			$thisView = $this->ui->makeListInline( array($thisView, $title) );

			$thisView = $this->ui->makeBlock( $thisView )
				->tag('border')
				->tag('padding', 2)
				;
			if( $checked ){
				$thisView
					->tag('border-color', 'olive')
					;
			}

			$calendarsView[] = $thisView;
		}

		$out = $this->ui->makeListInline( $calendarsView );

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
			'admin/employees/' . $id . '/calendars',
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

	public function header( SH4_Employees_Model $model )
	{
		$out = '__Calendars__';
		return $out;
	}

	public function breadcrumb( SH4_Employees_Model $model )
	{
		$id = $model->getId();
		$title = $this->employeesPresenter->presentTitle( $model );

		$return = array();
		$return['admin'] = array( 'admin', '__Administration__' );
		$return['employees'] = array( 'admin/employees', '__Employees__' );
		$return['employees/edit'] = $title;
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