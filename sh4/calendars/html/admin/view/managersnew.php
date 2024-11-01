<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
#[AllowDynamicProperties]
class SH4_Calendars_Html_Admin_View_ManagersNew
{
	public function __construct(
		HC3_Hooks $hooks,
		HC3_Ui $ui,
		HC3_Ui_Layout1 $layout,
		HC3_Request $request,
		HC3_UriAction $uriAction,

		SH4_App_Query $appQuery,
		HC3_IPermission $permission,

		SH4_Calendars_Query $calendarsQuery,
		SH4_Calendars_Presenter $calendarsPresenter,

		HC3_Users_Presenter $usersPresenter,
		HC3_Users_Query $users
	)
	{
		$this->self = $hooks->wrap($this);
		$this->ui = $ui;
		$this->layout = $layout;
		$this->request = $request;
		$this->uriAction = $uriAction;

		$this->permission = $hooks->wrap($permission);
		$this->appQuery = $hooks->wrap($appQuery);
		$this->calendarsQuery = $hooks->wrap($calendarsQuery);
		$this->calendarsPresenter = $hooks->wrap( $calendarsPresenter );

		$this->usersPresenter = $hooks->wrap($usersPresenter);
		$this->users = $hooks->wrap($users);
	}

	public function renderWpRoles( $id )
	{
		$model = $this->calendarsQuery->findById($id);

		$currentManagers = $this->appQuery->findManagersForCalendar( $model );
		$currentManagersIds = array_keys( $currentManagers );

		$currentViewers = $this->appQuery->findViewersForCalendar( $model );
		$currentViewersIds = array_keys( $currentViewers );

		$alreadyIds = array_merge( $currentManagersIds, $currentViewersIds );

		$out = array();

		$wp_roles = new WP_Roles();
		$wordpressRoles = $wp_roles->get_names();
		$wordpressCountUsers = count_users();

		$to = 'admin/calendars/' . $id . '/managers-new';
		$wpRoles = array();
		foreach( $wordpressRoles as $wpRoleId => $wpRoleLabel ){
			$label = $wpRoleLabel;

			$thisWpRoleCount = ( isset($wordpressCountUsers['avail_roles'][$wpRoleId]) ) ? $wordpressCountUsers['avail_roles'][$wpRoleId] : 0;
			if( ! $thisWpRoleCount ) continue;

			$q = array();
			$q[] = array( 'role', '=', $wpRoleId );
			$q[] = array( 'id', '<>', $alreadyIds );

			$users = $this->users->read( $q );
			$thisWpRoleCount = count( $users );
			if( ! $thisWpRoleCount ) continue;

			$label .= ' (';
			$label .= $thisWpRoleCount;
			$label .= ')';

			$wpRoles[ $wpRoleId ] = $label;
		}

		if( $wpRoles ){
			$out[] = '__Select a user from WordPress role__';
			foreach( $wpRoles as $wpRoleId => $wpRoleLabel ){
				$thisParams = array( 'wprole' => $wpRoleId );
				$thisTo = array( $to, $thisParams );
				$out[] = $this->ui->makeAhref( $thisTo, $wpRoleLabel )
					// ->tag('tab-link')
					;
			}
		}
		else {
			$out[] = '__No Available Users__';
		}

		$out = $this->ui->makeList( $out );

		$this->layout
			->setContent( $out )
			->setBreadcrumb( $this->self->breadcrumb($model) )
			->setHeader( $this->self->header($model) )
			;

		$out = $this->layout->render();
		return $out;
	}

	public function render( $id )
	{
		$wpRole = NULL;
		if( defined('WPINC') ){
			$params = $this->request->getParams();
			$wpRole = isset( $params['wprole'] ) ? $params['wprole'] : NULL;
			if( ! $wpRole ){
				return $this->self->renderWpRoles( $id );
			}
		}

		$model = $this->calendarsQuery->findById($id);

		$currentManagers = $this->appQuery->findManagersForCalendar( $model );
		$currentManagersIds = array_keys( $currentManagers );

		$currentViewers = $this->appQuery->findViewersForCalendar( $model );
		$currentViewersIds = array_keys( $currentViewers );

		$alreadyIds = array_merge( $currentManagersIds, $currentViewersIds );

		$q = array();
		if( $wpRole ){
			$q[] = array( 'role', '=', $wpRole );
		}

		$q[] = array( 'id', 'NOTIN', $alreadyIds );
		$users = $this->users->read( $q );

	// remove already managers/viewers
		foreach( $currentManagersIds as $uid ){
			unset( $users[$uid] );
		}
		foreach( $currentViewersIds as $uid ){
			unset( $users[$uid] );
		}

		// $users = $currentViewers + $users;
		// $users = $currentManagers + $users;

		$header = array(
			'title'		=> '__User__',
			'actions'	=> '&nbsp;',
			);

		$keys = array_keys( $header );
		$firstKey = array_shift( $keys );

		$rows = array();
		foreach( $users as $e ){
			$userId = $e->getId();
			$row = array();

			$userView = $this->usersPresenter->presentTitle( $e );
			$row['title'] = $userView;

			$actions = array();
			if( in_array($userId, $currentManagersIds) ){
				if( ! $this->permission->isAdmin($e) ){
					$actions[] = array( 'admin/calendars/' . $id . '/managers/' . $userId . '/remove', NULL, '__Remove From Managers__' );
				}
			}
			else {
				$actions[] = array( 'admin/calendars/' . $id . '/managers/' . $userId . '/add', NULL, '__Add To Managers__' );

				if( in_array($userId, $currentViewersIds) ){
					if( ! $this->permission->isAdmin($e) ){
						$actions[] = array( 'admin/calendars/' . $id . '/viewers/' . $userId . '/remove', NULL, '__Remove From Viewers__' );
					}
				}
				else {
					$actions[] = array( 'admin/calendars/' . $id . '/viewers/' . $userId . '/add', NULL, '__Add To Viewers__' );
				}
			}

			if( $actions ){
				$actions = $this->ui->helperActionsFromArray( $actions );
				if( $actions ){
					$actions = $this->ui->makeListInline( $actions )->separated()->gutter(1);
					$row['actions'] = $actions;
				}
			}

			$rows[] = $row;
		}

		$out = $this->ui->makeTable( $header, $rows );

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
		$out = '__New Manager__';
		return $out;
	}

	public function breadcrumb( $model )
	{
		$calendarId = $model->getId();
		$calendarTitle = $this->calendarsPresenter->presentTitle( $model );

		$ret = array();
		$ret['admin'] = array( 'admin', '__Administration__' );
		$ret['calendars'] = array( 'admin/calendars', '__Calendars__' );
		$ret['calendars/edit'] = $calendarTitle;
		$ret['calendars/edit/managers'] = array( 'admin/calendars/' . $model->getId() . '/managers', '__Managers__' );

		return $ret;
	}
}