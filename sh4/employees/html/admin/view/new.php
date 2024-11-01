<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
#[AllowDynamicProperties]
class SH4_Employees_Html_Admin_View_New
{
	public function __construct(
		HC3_Hooks $hooks,
		HC3_Ui $ui,

		SH4_Calendars_Query $calendarsQuery,
		SH4_Calendars_Presenter $calendarsPresenter,

		HC3_Ui_Layout1 $layout
	)
	{
		$this->ui = $ui;
		$this->layout = $layout;

		$this->calendarsQuery = $hooks->wrap( $calendarsQuery );
		$this->calendarsPresenter = $hooks->wrap( $calendarsPresenter );

		$this->self = $hooks->wrap($this);
	}

	public function render()
	{
		$form = $this->ui->makeForm(
			'admin/employees/new',
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

	public function header()
	{
		$out = '__Add New Employee__';
		return $out;
	}

	public function breadcrumb()
	{
		$return = array();
		$return['admin'] = array( 'admin', '__Administration__' );
		$return['employees'] = array( 'admin/employees', '__Employees__' );
		return $return;
	}

	public function form()
	{
		$inputs = array();

		$inputs['title'] = $this->ui->makeInputText( 'title', '__Title__' )->bold();
		$inputs['description'] = $this->ui->makeInputRichTextarea( 'description', '__Description__' )->setRows(6);

		$calendars = $this->calendarsQuery->findActive();
		$calendarsView = array();
		foreach( $calendars as $calendar ){
			$calendarId = $calendar->getId();
			$checked = FALSE;

			$title = $this->calendarsPresenter->presentTitle( $calendar );
			// $thisView = $this->ui->makeInputCheckbox( 'calendar[]', $thisView, $calendarId, $checked );
			$thisView = $this->ui->makeInputCheckbox( 'calendar[]', NULL, $calendarId, $checked );
			$thisView = $this->ui->makeListInline( array($thisView, $title) );

			$thisView = $this->ui->makeBlock( $thisView )
				->tag('border')
				->tag('padding', 1)
				;
			if( $checked ){
				$thisView
					->tag('border-color', 'olive')
					;
			}

			$calendarsView[] = $thisView;
		}

		$outCalendars = $this->ui->makeGrid();
		foreach( $calendarsView as $v ){
			$outCalendars->add( $v, 3, 12 );
		}

		$outCalendars = $this->ui->makeBlock( $outCalendars )
			->addAttr( 'id', 'sh4-calendars-employees' )
			;
		$toggleAll = $this->ui->makeInputCheckbox( 'toggle_all', '__Toggle All__' )
			->addAttr( 'class', 'sh4-calendars-employees-select-all' )
			;
		$outCalendars = $this->ui->makeList( array($toggleAll, $outCalendars) );
		$outCalendars = $this->ui->makeLabelled( '__Calendars__', $outCalendars );

	// add check all
		$js = $this->renderJs();
		$outCalendars = $this->ui->makeCollection( array($js, $outCalendars) );
		$inputs['calendars'] = $outCalendars;

		$inputs = $this->ui->makeList( $inputs );

		$buttons = $this->ui->makeInputSubmit( '__Add New Employee__')
			->tag('primary')
			;

		$out = $this->ui->makeList()
			->add( $inputs )
			->add( $buttons )
			;

		return $out;
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
