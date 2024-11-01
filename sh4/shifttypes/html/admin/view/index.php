<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class SH4_ShiftTypes_Html_Admin_View_Index
{
	public $self, $ui, $layout, $appQuery, $calendarsQuery, $calendarsPresenter, $query, $shiftTypesPresenter;

	public function __construct(
		HC3_Hooks $hooks,

		HC3_Ui $ui,
		HC3_Ui_Layout1 $layout,

		SH4_App_Query $appQuery,
		SH4_Calendars_Query $calendarsQuery,
		SH4_Calendars_Presenter $calendarsPresenter,

		SH4_ShiftTypes_Query $query,
		SH4_ShiftTypes_Presenter $shiftTypesPresenter
		)
	{
		$this->ui = $ui;
		$this->layout = $layout;

		$this->appQuery = $hooks->wrap( $appQuery );
		$this->calendarsQuery = $hooks->wrap( $calendarsQuery );
		$this->calendarsPresenter = $hooks->wrap( $calendarsPresenter );

		$this->query = $hooks->wrap($query);
		$this->shiftTypesPresenter = $hooks->wrap( $shiftTypesPresenter );
		$this->self = $hooks->wrap($this);
	}

	public function render()
	{
		$entries = $this->query->findAll();

		$tableColumns = $this->listingColumns();

		$keys = array_keys( $tableColumns );
		$firstKey = array_shift( $keys );

		$tableRows = array();
		foreach( $entries as $e ){
			$row = $this->listingCell($e);

		// actions for first cell
			$actions = array();
			$itemMenu = $this->listingCellMenu( $e );
			foreach( $itemMenu as $item ){
				list( $href, $label ) = $item;
				$link = $this->ui->makeAhref( $href, $label );
				$actions[] = $link;
			}

			if( $actions ){
				$actions = $this->ui->makeListInline($actions)
					->gutter(1)
					->separated()
					;
				$row[$firstKey] = $this->ui->makeList( array($row[$firstKey], $actions) )->gutter(1);
			}

			$tableRows[] = $row;
		}

		if( $entries ){
			$content = $this->ui->makeTable( $tableColumns, $tableRows );
		}
		else {
			$content = '__At least one shift time is required for a calendar.__';
		}

		$this->layout
			->setContent( $content )
			->setBreadcrumb( $this->self->breadcrumb() )
			->setHeader( $this->self->header() )
			->setMenu( $this->self->menu() )
			;

		$out = $this->layout->render();
		return $out;
	}

	public function listingColumns()
	{
		$return = array(
			'title'		=> '__Title__',
			'time'		=> '&nbsp;',
			'calendars'	=> '__Calendars__',
			);
		return $return;
	}

	public function listingCell( $e )
	{
		$return = array();

		$id = $e->getId();

		$titleView = $e->getTitle();
		$titleView = esc_html( $titleView );
		$titleView = $this->ui->makeSpan( $titleView )
			->tag('font-size', 4)
			->tag('font-style', 'bold')
			;

		$timeView = $this->shiftTypesPresenter->presentTime( $e );

		$return['title'] = $titleView;
		$return['time'] = $timeView;

		$calendars = array();
		$allCalendars = $this->calendarsQuery->findActive();
		foreach( $allCalendars as $calendar ){
			$thisShiftTypes = $this->appQuery->findShiftTypesForCalendar( $calendar );
			if( isset($thisShiftTypes[$id]) ){
				$calendarId = $calendar->getId();
				$calendars[ $calendarId ] = $calendar;
			}
		}

		$calendarsView = array();
		foreach( $calendars as $calendar ){
			$calendarsView[] = $this->calendarsPresenter->presentTitle($calendar);
		}
		$calendarsView = $this->ui->makeListInline( $calendarsView )->gutter(2);
		$return['calendars'] = $calendarsView;

		return $return;
	}

	public function breadcrumb()
	{
		$return = array();
		$return['admin'] = array( 'admin', '__Administration__' );
		$return['shifttypes'] = array( 'admin/shifttypes', '__Shift Time__' );

		return $return;
	}

	public function header()
	{
		$out = '__Shift Time__';
		return $out;
	}

	public function menu()
	{
		$return = array(
			'newhours' => array( 'admin/shifttypes/new/hours', '__Add New__' . ' (' . '__Hours__' . ')' ),
			'newdays' => array( 'admin/shifttypes/new/days', '__Add New__' . ' (' . '__Days__' . ')' ),
			'settings' => array( 'admin/shifttypes/settings', '__Settings__' )
			);
		return $return;
	}

	public function listingCellMenu( $model )
	{
		$id = $model->getId();

		$return = array();

		if( $id ){
			$return['edit'] = array( 'admin/shifttypes/' . $id . '/edit', '__Edit__' );
			$return['delete'] = array( 'admin/shifttypes/' . $id . '/delete', '__Delete__' );
		}

		return $return;
	}
}