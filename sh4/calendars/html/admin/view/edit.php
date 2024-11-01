<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
#[AllowDynamicProperties]
class SH4_Calendars_Html_Admin_View_Edit
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
		$this->self = $hooks->wrap($this);
		$this->query = $hooks->wrap($query);
	}

	public function render( $id )
	{
		$model = $this->query->findById( $id );

		$form = $this->ui->makeForm(
			'admin/calendars/' . $id,
			$this->self->form($model)
			);

		$this->layout
			->setContent( $form )
			->setBreadcrumb( $this->self->breadcrumb($model) )
			->setHeader( $this->self->header($model) )
			;

		$out = $this->layout->render();
		return $out;
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
		$return['calendars'] = array( 'admin/calendars', '__Calendars__' );
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

		$inputs[] = $idView;
		$inputs[] = $this->ui->makeInputText( 'title', '__Title__', $model->getTitle() )->bold();
		$inputs[] = $this->ui->makeInputRichTextarea( 'description', '__Description__', $model->getDescription() )->setRows(6);

		$typeOptions = array(
			SH4_Calendars_Model::TYPE_SHIFT			=> '__Shift__',
			SH4_Calendars_Model::TYPE_TIMEOFF		=> '__Time Off__',
			// SH4_Calendars_Model::TYPE_AVAILABILITY	=> '__Availability__',
			);

		// if( ! $this->availability->hasAvailability() ){
			// $typeOptions[SH4_Calendars_Model::TYPE_AVAILABILITY] = '__Availability__';
		// }

		$typeView = $this->ui->makeInputRadioSet( 'calendar_type', '__Type__', $typeOptions, $model->getType() );
		// $typeView = $typeOptions[ $model->getType() ];
		// $typeView = $this->ui->makeLabelled( '__Type__', $typeView );
		$inputs[] = $typeView;

		$inputs[] = $this->ui->makeInputColorpicker( 'color', '__Color__', $model->getColor() );

		$inputs = $this->ui->makeList( $inputs );

		$buttons = $this->ui->makeInputSubmit( '__Save__')
			->tag('primary')
			;

		$out = $this->ui->makeList( array($inputs, $buttons) );

		return $out;
	}
}