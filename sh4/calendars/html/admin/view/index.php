<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
#[AllowDynamicProperties]
class SH4_Calendars_Html_Admin_View_Index
{
	public function __construct(
		HC3_Hooks $hooks,

		HC3_Ui $ui,
		HC3_Ui_Layout1 $layout,
		HC3_Request $request,

		SH4_App_Query $appQuery,

		HC3_Users_Presenter $usersPresenter,
		SH4_Calendars_Query $calendarsQuery,
		SH4_Calendars_Presenter $calendarsPresenter,
		SH4_Calendars_Permissions $calendarsPermissions,

		SH4_Notifications_Service $notificationsService,

		SH4_Employees_Query $employees
		)
	{
		$this->request = $request;
		$this->ui = $ui;
		$this->layout = $layout;

		$this->appQuery = $hooks->wrap($appQuery);

		$this->usersPresenter = $hooks->wrap($usersPresenter);

		$this->calendarsQuery = $hooks->wrap( $calendarsQuery );
		$this->calendarsPresenter = $hooks->wrap( $calendarsPresenter );
		$this->calendarsPermissions = $hooks->wrap( $calendarsPermissions );

		$this->employees = $hooks->wrap($employees);
		$this->notificationsService = $hooks->wrap( $notificationsService );

		$this->self = $hooks->wrap($this);
	}

	public function render()
	{
		$this->request
			->initParam('status', 'active')
			;

		$params = $this->request->getParams();
		$currentStatus = $params['status'];

		switch( $currentStatus ){
			case 'all':
				$entries = $this->calendarsQuery->findAll();
				break;
			case 'active':
				$entries = $this->calendarsQuery->findActive();
				break;
			case 'archive':
				$entries = $this->calendarsQuery->findArchived();
				break;
		}

		$tableColumns = $this->self->listingColumns();

		$keys = array_keys( $tableColumns );
		$firstKey = array_shift( $keys );

		$tableRows = array();
		foreach( $entries as $e ){
			$row = $this->self->listingCell($e);

		// actions for first cell
			$actions = array();
			$itemMenu = $this->self->listingCellMenu( $e );
			$actions = $this->ui->helperActionsFromArray( $itemMenu );
			if( $actions ){
				$actions = $this->ui->makeListInline($actions)
					->gutter(1)
					->separated()
					;
				$row[$firstKey] = $this->ui->makeList( array($row[$firstKey], $actions) )->gutter(1);
			}

			$tableRows[] = $row;
		}

		$content = $this->ui->makeTable( $tableColumns, $tableRows );

	// form
		$option = array();
		// $option[] = '- ' . '__With Selected__' . ' -';
		$option['archive'] = '__Archive__';
		$option['restore'] = '__Restore__';
		$option['delete'] = '__Delete__';

		$allowed = array();
		foreach( $entries as $model ){
			if( $model->isArchived() ){
				$allowed['restore'] = 1;
				$allowed['delete'] = 1;
			}
			else {
				$allowed['archive'] = 1;
			}
		}
		$option = array_intersect_key( $option, $allowed );

		if( $option ){
			array_unshift( $option, '- ' . '__Action__' . ' -' );

			$selectAction = $this->ui->makeInputSelect( 'action', '__With Selected__', $option );
			$buttons = $this->ui->makeInputSubmit( '__Apply Action__' )->tag('primary');

			$hidden = $this->ui->makeInputHidden( 'id', null );
			$js =  <<<EOT
<script>
function sh4CalendarForm(){
var idValue = [];
var idInputs = document.getElementsByName("hc-id[]");
for( var ii = 0; ii < idInputs.length; ii++ ){
	if( idInputs[ii].checked ){
		idValue.push( idInputs[ii].value );
	}
}

if( idValue.length ){
	var idInput = document.getElementsByName("hc-id")[0];
	idInput.value = idValue.join( ':' );
	return true;
}
else {
	return false;
}
}
</script>
EOT;

			$form = $this->ui->makeList( array($js, $hidden, $selectAction, $buttons) );
			$form = $this->ui->makeForm(
				'admin/calendars',
				$form
			);
			$form->setId( 'sh4-calendar-form' );
			$form->setAttr( 'onsubmit', 'return sh4CalendarForm();' );

			$content = $this->ui->makeList( array($content, $form) );
		}

		$byStatus = $this->self->byStatus( $entries );
		if( $byStatus ){
			$byStatusView = array();
			foreach( $byStatus as $bys ){
				list( $href, $hrefLabel, $countAddon ) = $bys;

				$thisSelected = FALSE;
				if( isset($href[1]['status']) && $href[1]['status'] == $currentStatus ){
					$thisSelected = TRUE;
				}

				$thisOne = $thisSelected ? $this->ui->makeSpan( $hrefLabel ) : $this->ui->makeAhref( $href, $hrefLabel );
				if( strlen($countAddon) ){
					$thisOne = $this->ui->makeListInline( array($thisOne, '(' . $countAddon . ')') )->gutter(1);
				}

				if( $thisSelected ){
					$thisOne
						->tag('font-style', 'bold')
						;
				}

				$byStatusView[] = $thisOne;
			}
			$byStatusView = $this->ui->makeListInline( $byStatusView )->separated();

			$content = $this->ui->makeList( array($byStatusView, $content) )->gutter(1);
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

	public function breadcrumb()
	{
		$return = array();
		$return['admin'] = array( 'admin', '__Administration__' );
		return $return;
	}

	public function menu()
	{
		$return = array(
			'new' => array( 'admin/calendars/new', '__Add New__'  )
			);
		$return['sort'] = array( 'admin/calendars/sort',	'__Sort Order__' );

		return $return;
	}

	public function header()
	{
		$out = '__Calendars__';
		return $out;
	}

	public function byStatus( $entries )
	{
		$return = array();

		$count1 = $this->calendarsQuery->countActive();
		$count2 = $this->calendarsQuery->countArchived();

		$return['all'] = array( array('admin/calendars', array('status' => 'all')), '__All__', ($count1 + $count2) );

		if( $count1 = $this->calendarsQuery->countActive() ){
			$return['active'] = array( array('admin/calendars', array('status' => 'active')), '__Active__', $count1 );
		}
		if( $count2 = $this->calendarsQuery->countArchived() ){
			$return['archived'] = array( array('admin/calendars', array('status' => 'archive')), '__Archived__', $count2 );
		}

		return $return;
	}

	public function listingColumns()
	{
		$return = array(
			// 'title'	=> '__Name__',
			'title'	=> NULL,
			'details' => NULL,
			);
		return $return;
	}

	public function listingCell( SH4_Calendars_Model $model )
	{
		$return = array();

		$id = $model->getId();

		$idView = $model->getId();
		$idLabel = $this->ui->makeBlock('id')
			->tag('mute')
			->tag('font-size', 2)
			;
		$idView = $this->ui->makeListInline( array($idLabel, $idView) )
			->gutter(1)
			;

		$titleView = $this->calendarsPresenter->presentTitle( $model );
		// $titleView = $this->ui->makeList( array($titleView, $idView) )->gutter(0);

		$checkbox = $this->ui->makeInputCheckbox( 'id[]', null, $model->getId() );
		$titleView = $this->ui->makeListInline( array($checkbox,$titleView, $idView) )->gutter(1);
		$titleView = $this->ui->makeElement( 'label', $titleView );

		$return['title'] = $titleView;

		$detailsView = array();
		$details = $this->self->listingCellDetails( $model );
		foreach( $details as $d ){
			$dView = array();
			$dView[] = $this->ui->makeAhref( $d[0], $d[1] )
				->tag('nice-link')
				;

			if( isset($d[2]) ){
				$countView = $this->ui->makeSpan( $d[2] )
					->tag('color', 'white')
					->tag('padding', 'x1')
					;

				$ok = isset($d[3]) ? $d[3] : ( ($d[2] > 0) ? TRUE : FALSE);

				if( $ok ){
					$countView->tag('bgcolor', 'olive');
				}
				else {
					$countView->tag('bgcolor', 'maroon');
				}
				$dView[] = $countView;
			}

			$dView = $this->ui->makeListInline( $dView )->gutter(0);
			$detailsView[] = $dView;
		}
		if( $detailsView ){
			$detailsView = $this->ui->makeListInline( $detailsView )
				->gutter(2)
				// ->separated()
				;
			$return['details'] = $detailsView;
		}

		return $return;
	}

	public function listingCellMenu( SH4_Calendars_Model $model )
	{
		$id = $model->getId();

		$return = array(
			'edit' => array( 'admin/calendars/' . $id, '__Edit__' ),
			);

		$allNotifications = $this->notificationsService->findAll();
		$allNotifications = array_keys( $allNotifications );
		$notificationsCount = 0;
		foreach( $allNotifications as $notificationId ){
			if( $this->notificationsService->isOn($model, $notificationId) ){
				$notificationsCount++;
			}
		}
		$label = '__Notifications__' . ' [' . $notificationsCount . ']';

		$return['notifications'] = array( 'admin/notifications/' . $id, $label );

		$allPermissionsOptions = $this->calendarsPermissions->categorizedOptions();
		$thisPermissionsOptions = $this->calendarsPermissions->getAll( $model );

		$permissionCounts = array();
		foreach( $allPermissionsOptions as $group => $perms ){
			// $permissionCounts[ $group ] = array( 0, count($perms) );
			$permissionCounts[ $group ] = 0;
		}

		foreach( $thisPermissionsOptions as $k => $v ){
			if( ! $v ){
				continue;
			}

			$ka = explode( '_', $k );
			$group = $ka[0];

			if( ! isset($allPermissionsOptions[$group]) ){
				continue;
			}

			if( ! in_array($k, $allPermissionsOptions[$group]) ){
				continue;
			}

			$permissionCounts[ $group ]++;
		}
		$permissionCountsView = join( '/', $permissionCounts );

		$label = '__Permissions__' . ' [' . $permissionCountsView . ']';
		$return['permissions'] = array( 'admin/calendars/' . $id . '/prm', $label);

		if( $model->isArchived() ){
			$return['restore'] = array( 'admin/calendars/' . $id . '/restore', NULL, '__Restore__');
			$return['delete'] = array( 'admin/calendars/' . $id . '/delete', '__Delete__' );
		}
		else {
			$return['archive'] = array( 'admin/calendars/' . $id . '/archive', NULL, '__Archive__' );
		}

		return $return;
	}

	public function listingCellDetails( SH4_Calendars_Model $model )
	{
		$id = $model->getId();
		$return = array();

		$employees = $this->appQuery->findEmployeesForCalendar( $model );
	// don't count open shifts
		if( isset($employees[0]) ){
			$openOn = TRUE;
			unset( $employees[0] );
		}
		else {
			$openOn = FALSE;
		}
		$return['employees'] = array( 'admin/calendars/' . $id . '/employees', '__Employees__', count($employees) ) ;

		$shiftTypes = $this->appQuery->findShiftTypesForCalendar( $model );
		$return['shifttypes'] = array( 'admin/calendars/' . $id . '/shifttypes', '__Shift Time__', count($shiftTypes) ) ;

		$managers = $this->appQuery->findManagersForCalendar( $model );
		$return['managers'] = array( 'admin/calendars/' . $id . '/managers', '__Managers__', count($managers) ) ;

		$viewers = $this->appQuery->findViewersForCalendar( $model );
		if( count($viewers) ){
			$return['viewers'] = array( 'admin/calendars/' . $id . '/managers', '__Viewers__', count($viewers) ) ;
		}

		$openLabel = $openOn ? '&#x2713;' : '&times;';
		$return['open'] = array( 'admin/calendars/' . $id . '/employees', '__Open Shifts__', $openLabel, $openOn ) ;

		return $return;
	}
}