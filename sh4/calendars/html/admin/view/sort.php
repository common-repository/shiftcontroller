<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
#[AllowDynamicProperties]
class SH4_Calendars_Html_Admin_View_Sort
{
	public function __construct(
		HC3_Hooks $hooks,

		HC3_Post $post,
		HC3_Ui $ui,
		HC3_Ui_Layout1 $layout,
		HC3_Request $request,

		SH4_Calendars_Presenter $presenter,
		SH4_Calendars_Query $query,
		SH4_Calendars_Command $command
		)
	{
		$this->request = $request;
		$this->ui = $ui;
		$this->layout = $layout;
		$this->post = $hooks->wrap($post);

		$this->presenter = $hooks->wrap($presenter);
		$this->command = $hooks->wrap($command);
		$this->query = $hooks->wrap($query);

		$this->self = $hooks->wrap($this);
	}

	public function post()
	{
		$entries = $this->query->findActive();

		$action = $this->post->get( 'action' );

		foreach( $entries as $e ){
			$id = $e->getId();
			if( ! $id ){
				continue;
			}

			if( 'reset' == $action ){
				$thisSortOrder = 0;
			}
			else {
				$thisSortOrder = $this->post->get( 'sortorder_' . $id );
			}

			$this->command->changeSortOrder( $e, $thisSortOrder );
		}

		$ret = array( 'admin/calendars', '__Sort Order Updated__' );
		return $ret;
	}

	public function get()
	{
		$entries = $this->query->findActive();

		$tableColumns = $this->self->listingColumns();

		$tableRows = array();
		foreach( $entries as $e ){
			$row = $this->self->listingCell($e);
			$tableRows[] = $row;
		}

		$buttons = array();
		$buttons[] = $this->ui->makeInputSubmit( '__Save Sort Order__', 'action' )
			->setAttr( 'value', 'set' )
			->tag('primary')
			;
		$buttons[] = $this->ui->makeInputSubmit( '__Reset To Defaults__', 'action' )
			->setAttr( 'value', 'reset' )
			->tag('secondary')
			;
		$buttons = $this->ui->makeListInline( $buttons );

		$rowSubmit = array();
		$rowSubmit['sortorder'] = $buttons;

		$tableRows[] = $rowSubmit;

		$content = $this->ui->makeTable( $tableColumns, $tableRows );

		$form = $this->ui->makeForm(
			'admin/calendars/sort',
			$content
			);

		$this->layout
			->setContent( $form )
			->setBreadcrumb( $this->self->breadcrumb() )
			->setHeader( $this->self->header() )
			->setMenu( $this->self->menu() )
			;

		$out = $this->layout->render();
		return $out;
	}

	public function breadcrumb()
	{
		$return = array();
		$return['admin'] = array( 'admin', '__Administration__' );
		$return['admin/calendars'] = array( 'admin/calendars', '__Calendars__' );
		return $return;
	}

	public function menu()
	{
		$return = array();
		return $return;
	}

	public function header()
	{
		$out = '__Sort Order__';
		return $out;
	}

	public function listingColumns()
	{
		$return = array(
			'title' 		=> '__Name__',
			'sortorder'	=> '__Sort Order__',
			);
		return $return;
	}

	public function listingCell( $model )
	{
		$return = array();
		$id = $model->getId();

		// $titleView = $model->getTitle();
		$titleView = $this->presenter->presentTitle( $model );

		if( $model->isArchived() ){
			$titleView = $this->ui->makeSpan($titleView)
				->tag('font-style', 'line-through')
				;
		}

		// $titleView = $this->ui->makeSpan( $titleView )
			// ->tag('font-size', 4)
			// ->tag('font-style', 'bold')
			// ;
		$return['title'] = $titleView;

		if( $id ){
			$sortOrder = $model->getSortOrder();
			$sortOrderView = $this->ui->makeInputText( 'sortorder_' . $id, NULL, $sortOrder );
			$sortOrderView = $this->ui->makeBlock( $sortOrderView )
				->addAttr( 'style', 'width: 4em;' )
				;
			$return['sortorder'] = $sortOrderView;
		}

		return $return;
	}

	public function listingCellMenu( $model )
	{
		$return = array();
		return $return;
	}
}