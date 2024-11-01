<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
#[AllowDynamicProperties]
class SH4_Shifts_View_Conflicts
{
	public function __construct( 
		HC3_Hooks $hooks,

		HC3_Ui $ui,
		HC3_Ui_Layout1 $layout,

		SH4_Shifts_View_Common $common,
		SH4_Shifts_Query $query,
		SH4_Shifts_Conflicts $conflicts
		)
	{
		$this->ui = $ui;
		$this->layout = $layout;

		$this->query = $hooks->wrap($query);

		$this->conflicts = $hooks->wrap($conflicts);
		$this->common = $hooks->wrap($common);
		$this->self = $hooks->wrap($this);
	}

	public function render( $id )
	{
	// model
		$model = $this->query->findById($id);

		$out = NULL;

		$conflicts = $this->conflicts->get( $model );
		if( $conflicts ){
			$conflictsView = array();
			foreach( $conflicts as $conflict ){
				$conflictsView[] = $conflict->render();
			}

			$out = $this->ui->makeList( $conflictsView );
		}

		$this->layout
			->setContent( $out )
			->setBreadcrumb( $this->common->breadcrumb($model) )
			->setHeader( $this->self->header($model) )
			;
		$out = $this->layout->render();

		return $out;
	}

	public function header()
	{
		$out = '__Conflicts__';
		return $out;
	}
}