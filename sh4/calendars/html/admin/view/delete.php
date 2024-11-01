<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
#[AllowDynamicProperties]
class SH4_Calendars_Html_Admin_View_Delete
{
	public function __construct(
		HC3_Hooks $hooks,
		HC3_Ui $ui,
		HC3_Ui_Layout1 $layout,

		SH4_Calendars_Query $query
		)
	{
		$this->ui = $ui;
		$this->layout = $layout;

		$this->query = $hooks->wrap( $query );
		$this->self = $hooks->wrap($this);
	}

	public function render( $id )
	{
		$model = $this->query->findById( $id );

		$out = $this->ui->makeForm(
			'admin/calendars/' . $id . '/delete',
			$this->ui->makeInputSubmit( '__Confirm Delete__')->tag('danger')
			);

		$help = array();
		$help[] = '__When a calendar is deleted, all its data and information is permanently removed.__';
		$help[] = '__This operation cannot be undone.__';
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
		$return = array();
		$return['admin'] = array( 'admin', '__Administration__' );
		$return['calendars'] = array( 'admin/calendars', '__Calendars__' );
		$return['calendars/edit'] = array( 'admin/calendars/' . $model->getId(), $model->getTitle() );
		return $return;
	}
}