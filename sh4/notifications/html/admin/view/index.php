<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
#[AllowDynamicProperties]
class SH4_Notifications_Html_Admin_View_Index
{
	public function __construct(
		HC3_Hooks $hooks,

		HC3_Ui $ui,
		HC3_Ui_Layout1 $layout,

		SH4_Calendars_Query $calendarsQuery,
		SH4_Calendars_Presenter $calendarsPresenter,

		SH4_Notifications_Service $notificationsService
		)
	{
		$this->ui = $ui;
		$this->layout = $layout;

		$this->calendarsQuery = $hooks->wrap( $calendarsQuery );
		$this->calendarsPresenter = $hooks->wrap( $calendarsPresenter );

		$this->notificationsService = $hooks->wrap( $notificationsService );
		$this->self = $hooks->wrap($this);
	}

	public function render( $calendarId )
	{
		$calendar = $this->calendarsQuery->findById( $calendarId );

		$entries = $this->notificationsService->findAll();

		$tableColumns = $this->listingColumns();

		$keys = array_keys( $tableColumns );
		$firstKey = array_shift( $keys );

		$tableRows = array();
		foreach( $entries as $id => $e ){
			$row = $this->listingCell( $calendar, $id, $e );

		// actions for first cell
			$actions = array();
			$itemMenu = $this->listingCellMenu( $calendar, $id, $e );
			foreach( $itemMenu as $item ){
			// form
				if( count($item) == 3 ){
					list( $href, $formContent, $btnLabel ) = $item;
					$btn = $this->ui->makeInputSubmit($btnLabel)
						->tag('nice-link')
						;
					$formContent = $formContent ? $this->ui->makeListInline( array($formContent, $btn) ) : $btn;
					$item = $this->ui->makeForm( $href, $formContent );
					if( ! $item ){
						continue;
					}
				}
			// link
				else {
					list( $href, $label ) = $item;
					$item = $this->ui->makeAhref( $href, $label );
					if( ! $item ){
						continue;
					}
					$item->tag('nice-link');
				}
				$actions[] = $item;
			}

			if( $actions ){
				$actions = $this->ui->makeListInline($actions)
					->gutter(1)
					->separated()
					;
				$row[$firstKey] = $this->ui->makeList( array($row[$firstKey], $actions) )->gutter(1);

				$row[$firstKey] = $this->ui->makeBlock( $row[$firstKey] )
					->tag('padding', 2)
					->tag('border')
					;
			}

			$tableRows[] = $row;
		}

		// $content = $this->ui->makeTable( $tableColumns, $tableRows );

		$content = $this->ui->makeGrid();
		foreach( $tableRows as $r ){
			$r = array_shift( $r );
			$content->add( $r, 4, 12 );
		}

		$this->layout
			->setContent( $content )
			->setBreadcrumb( $this->self->breadcrumb($calendar) )
			->setHeader( $this->self->header($calendar) )
			->setMenu( $this->self->menu($calendar) )
			;

		$out = $this->layout->render();
		return $out;
	}

	public function menu( $calendar )
	{
		$return = array();
		return $return;
	}

	public function header( $calendar )
	{
		$out = '__Notifications__';
		return $out;
	}

	public function breadcrumb( SH4_Calendars_Model $calendar )
	{
		$calendarId = $calendar->getId();
		$calendarTitle = $this->calendarsPresenter->presentTitle( $calendar );

		$return = array();
		$return['admin'] = array( 'admin', '__Administration__' );
		$return['calendars'] = array( 'admin/calendars', '__Calendars__' );
		$return['calendars/edit'] = array( 'admin/calendars/' . $calendarId, $calendarTitle );

		return $return;
	}

	public function listingColumns()
	{
		$return = array(
			'title'	=> '__Notification__',
			);
		return $return;
	}

	public function listingCell( SH4_Calendars_Model $calendar, $notificationId, $model )
	{
		$return = array();

		$titleView = $model->getTitle();
		// $titleView = $notificationId;

		$titleView = $this->ui->makeSpan( $titleView )
			->tag('font-size', 4)
			// ->tag('font-style', 'bold')
			;

		if( $this->notificationsService->isOn($calendar, $notificationId) ){
			$titleView
				->tag('font-style', 'bold')
				;
		}
		else {
			$titleView
				->tag('font-style', 'line-through')
				;
		}

		$return['title'] = $titleView;

		return $return;
	}

	public function listingCellMenu( SH4_Calendars_Model $calendar, $notificationId, $model )
	{
		$return = array();
		$calendarId = $calendar->getId();

		$return['edit'] = array( 'admin/notifications/' . $calendarId . '/' . $notificationId, '__Edit__' );

		if( $this->notificationsService->isOn($calendar, $notificationId) ){
			$return['disable'] = array( 'admin/notifications/' . $calendarId . '/' . $notificationId . '/disable', NULL, '__Disable__' );
		}
		else {
			$return['enable'] = array( 'admin/notifications/' . $calendarId . '/' . $notificationId . '/enable', NULL, '__Enable__' );
		}

		return $return;
	}
}