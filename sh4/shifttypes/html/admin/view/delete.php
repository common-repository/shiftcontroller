<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class SH4_ShiftTypes_Html_Admin_View_Delete
{
	public $self, $ui, $layout, $shiftTypesPresenter, $query;

	public function __construct(
		HC3_Hooks $hooks,
		HC3_Ui $ui,
		HC3_Ui_Layout1 $layout,

		SH4_ShiftTypes_Presenter $shiftTypesPresenter,
		SH4_ShiftTypes_Query $query
		)
	{
		$this->ui = $ui;
		$this->layout = $layout;

		$this->shiftTypesPresenter = $hooks->wrap($shiftTypesPresenter);
		$this->query = $hooks->wrap($query);

		$this->self = $hooks->wrap($this);
	}

	public function render( $id )
	{
		$model = $this->query->findById( $id );

		$out = $this->ui->makeForm(
			'admin/shifttypes/' . $id . '/delete',
			$this->ui->makeInputSubmit( '__Confirm Delete__')->tag('danger')
			);

		$help = array();
		$help[] = '__This will not affect existing shifts.__';
		$help = $this->ui->makeList( $help );

		$out = $this->ui->makeList( array($help, $out) );

		$this->layout
			->setContent( $out )
			->setBreadcrumb( $this->self->breadcrumb($model) )
			->setHeader( $this->self->header($model) )
			;

		$out = $this->layout->render();
		return $out;
	}

	public function header( $model )
	{
		$out = '__Delete__';
		return $out;
	}

	public function breadcrumb( $model )
	{
		$modelTitle = $this->shiftTypesPresenter->presentTitle( $model );

		$return = array();

		$return['admin'] = array( 'admin', '__Administration__' );
		$return['shifttypes'] = array( 'admin/shifttypes', '__Shift Time__' );
		$return['shifttypes/edit'] = $modelTitle;

		return $return;
	}
}