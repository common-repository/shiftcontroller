<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
#[AllowDynamicProperties]
class SH4_Employees_Html_Admin_View_Edit
{
	public function __construct(
		HC3_Hooks $hooks,
		HC3_Ui $ui,
		HC3_Ui_Layout1 $layout,
		SH4_Employees_Query $query
	)
	{
		$this->ui = $ui;
		$this->layout = $layout;
		$this->query = $hooks->wrap($query);
		$this->self = $hooks->wrap($this);
	}

	public function render( $id )
	{
		$model = $this->query->findById($id);

		$form = $this->ui->makeForm(
			'admin/employees/' . $id,
			$this->self->form( $model )
			);

		$this->layout
			->setContent( $form )
			->setBreadcrumb( $this->self->breadcrumb($model) )
			->setHeader( $this->self->header($model) )
			->setMenu( $this->self->menu($model) )
			;

		$out = $this->layout->render();
		return $out;
	}

	public function menu( $model )
	{
		$ret = array();
		return $ret;
	}

	public function header( $model )
	{
		$out = '__Edit__';
		return $out;
	}

	public function breadcrumb( $model )
	{
		$return = array();
		$return['admin'] = array( 'admin', '__Administration__' );
		$return['employees'] = array( 'admin/employees', '__Employees__' );
		return $return;
	}

	public function form( $model )
	{
		$inputs = array();
		$idView = $model->getId();

		$idLabel = $this->ui->makeBlock('id')
			->tag('mute')
			->tag('font-size', 2)
			;
		$idView = $this->ui->makeListInline( array($idLabel, $idView) )
			->gutter(1)
			;

		$inputs['id'] = $idView;
		$inputs['title'] = $this->ui->makeInputText( 'title', '__Title__', $model->getTitle() )->bold();
		$inputs['description'] = $this->ui->makeInputRichTextarea( 'description', '__Description__', $model->getDescription() )->setRows(6);

		$inputs = $this->ui->makeList( $inputs );

		$buttons = $this->ui->makeInputSubmit( '__Save__' )
			->tag('primary')
			;

		$out = $this->ui->makeList()
			->add( $inputs )
			->add( $buttons )
			;

		return $out;
	}
}
