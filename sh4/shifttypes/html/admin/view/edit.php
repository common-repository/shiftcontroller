<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class SH4_ShiftTypes_Html_Admin_View_Edit
{
	public $self, $ui, $layout, $settings, $shiftTypesPresenter, $query;

	public function __construct(
		HC3_Hooks $hooks,
		HC3_Ui $ui,
		HC3_Ui_Layout1 $layout,

		HC3_Settings $settings,
		SH4_ShiftTypes_Presenter $shiftTypesPresenter,
		SH4_ShiftTypes_Query $query
		)
	{
		$this->ui = $ui;
		$this->layout = $layout;

		$this->settings = $hooks->wrap($settings);
		$this->shiftTypesPresenter = $hooks->wrap($shiftTypesPresenter);
		$this->query = $hooks->wrap($query);
		$this->self = $hooks->wrap($this);
	}

	public function render( $id )
	{
		$model = $this->query->findById( $id );

		switch( $model->getRange() ){
			case $model::RANGE_DAYS:
				$form = $this->self->formDays( $model );
				break;

			default:
				$form = $this->self->formHours( $model );
				break;
		}

		$out = $this->ui->makeForm(
			'admin/shifttypes/' . $id . '/edit',
			$form
			);

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
		$out = '__Edit__';
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

	public function formHours( $model )
	{
		$timeHelp = '__Changing time here will not affect existing shifts.__';

		$time = array( $model->getStart(), $model->getEnd() );

		$start = $model->getStart();
		$end = $model->getEnd();

		$inputs = array();
		$inputs[] = $this->ui->makeInputText( 'title', '__Title__', $model->getTitle() )->bold();
		$inputs[] = $this->ui->makeInputTimeRange( 'time', '__Time__', $time );
		$inputs[] = $timeHelp;

		$noBreak = $this->settings->get( 'shifttypes_nobreak' );

		if( ! $noBreak ){
			$breakOn = FALSE;
			$breakStart = $model->getBreakStart();
			$breakEnd = $model->getBreakEnd();

			if( ! ((NULL === $breakStart) && (NULL === $breakEnd)) ){
				$breakOn = TRUE;
				if( $breakStart > 24*60*60 ){
					$breakStart = $breakStart - 24*60*60;
				}
				$breakInput = $this->ui->makeInputTimeRange( 'break', NULL, array($breakStart, $breakEnd) );
			}
			else {
				$breakInput = $this->ui->makeInputTimeRange( 'break', NULL );
			}
			$breakInput = $this->ui->makeCollapseCheckbox(
				'break_on',
				'__Lunch Break__' . '?',
				$breakInput
				);
			if( $breakOn ){
				$breakInput->expand();
			}

			$inputs[] = $breakInput;
		}

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

	public function formDays( $model )
	{
		$daysOptions = range( 2, 90, 1 );
		$daysOptions = array_combine( $daysOptions, $daysOptions );

		$inputs = $this->ui->makeList()
			->add( $this->ui->makeInputText( 'title', '__Title__', $model->getTitle() )->bold() )
			->add( $this->ui->makeInputSelect('start', '__Min__' . ' (' . '__Days__' . ')', $daysOptions, $model->getStart()) )
			->add( $this->ui->makeInputSelect('end', '__Max__' . ' (' . '__Days__' . ')', $daysOptions, $model->getEnd()) )
			;

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