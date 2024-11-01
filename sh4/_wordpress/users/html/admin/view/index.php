<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
#[AllowDynamicProperties]
class SH4_Users_Html_Admin_View_Index
{
	public function __construct(
		HC3_Hooks $hooks,

		HC3_Ui $ui,
		HC3_Ui_Layout1 $layout,

		HC3_IPermission $permission,

		SH4_App_Query $appQuery,
		SH4_Calendars_Presenter $calendarsPresenter,
		SH4_Calendars_Query $calendarsQuery,

		SH4_Employees_Presenter $employeesPresenter,
		HC3_Users_Presenter $usersPresenter,
		HC3_Users_Query $usersQuery
		)
	{
		$this->self = $hooks->wrap($this);
		$this->ui = $ui;
		$this->layout = $layout;

		$this->permission = $hooks->wrap($permission);

		$this->calendarsQuery = $hooks->wrap( $calendarsQuery );
		$this->calendarsPresenter = $hooks->wrap( $calendarsPresenter );

		$this->appQuery = $hooks->wrap( $appQuery );
		$this->employeesPresenter = $hooks->wrap( $employeesPresenter );
		$this->usersPresenter = $hooks->wrap( $usersPresenter );
		$this->usersQuery = $hooks->wrap( $usersQuery );
	}

	public function render()
	{
		$entries = $this->appQuery->findAllUsersWithEmployee();

		$managers = array();
		$calendars = $this->calendarsQuery->findAll();
		foreach( $calendars as $calendar ){
			$thisManagers = $this->appQuery->findManagersForCalendar( $calendar );
			$managers = $managers + $thisManagers;
		}

		$admins = $this->permission->findAdmins();

		$entries = $admins + $managers + $entries;

		$tableColumns = $this->self->listingColumns();

		$keys = array_keys( $tableColumns );
		$firstKey = array_shift( $keys );

		$tableRows = array();
		foreach( $entries as $e ){
			$row = $this->self->listingCell($e);

		// actions for first cell
			$actions = array();
			$itemMenu = $this->self->listingCellMenu( $e );
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
			}

			$tableRows[] = $row;
		}

		$content = $this->ui->makeTable( $tableColumns, $tableRows );

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
			'new' => array( admin_url('user-new.php'), '__Add New__' )
			);
		return $return;
	}

	public function header()
	{
		$out = '__Users__';
		return $out;
	}

	public function listingColumns()
	{
		$return = array(
			'title'		=> '__WordPress User__' . ' / ' . '__Role__',
			'role'		=> '__ShiftController Role__',
			'employee'	=> '__Linked Employee__',
			'calendar'	=> '__Managed Calendars__',
			'notes'		=> '__Notes__',
			);
		return $return;
	}

	public function listingCell( HC3_Users_Model $model )
	{
		$return = array();
		$id = $model->getId();

		$titleView = $this->usersPresenter->presentTitle( $model );

		$wpUser = get_userdata( $id );
		$wpRole = NULL;
		if( isset($wpUser->roles) ){
			global $wp_roles;
			$wpRole = array();
			foreach( $wpUser->roles as $wpr ){
				$wpRole[] = $wp_roles->roles[ $wpr ]['name'];
			}
			$wpRole = join(', ', $wpRole);
			$titleView = $this->ui->makeList( array($titleView, $wpRole) )->gutter(0);
		}
		$return['title'] = $titleView;

		$employee = $this->appQuery->findEmployeeByUser( $model );
		$calendars = $this->appQuery->findCalendarsManagedByUser( $model );

		$employeeActions = array();
		if( $employee ){
			$employeeView = $this->employeesPresenter->presentTitle( $employee );
			$employeeActions[] = array( 'admin/users/' . $id . '/employee/0', NULL, '__Unlink__' );
		}
		else {
			$employeeView = '__N/A__';
			$employeeActions[] = array( 'admin/users/' . $id . '/employee', '__Link To Employee__' );
		}

		$employeeActions = $this->ui->helperActionsFromArray( $employeeActions );
		if( $employeeActions ){
			$employeeActions = $this->ui->makeListInline( $employeeActions )->gutter(1)->separated();
			$employeeView = $this->ui->makeList( array($employeeView, $employeeActions) )->gutter(0);
		}

		$return['employee'] = $employeeView;

		$isAdmin = $this->permission->isAdmin( $model );

		$calendarsView = array();
		$roleView = array();

		if( $employee ){
			$roleView[] = '__Employee__';
		}

		if( $isAdmin ){
			$roleView[] = '__Administrator__';
			$calendarsView[] = '__All__';
		}
		elseif( $calendars ){
			$roleView[] = '__Manager__';
			foreach( $calendars as $calendar ){
				$calendarsView[] = $this->calendarsPresenter->presentTitle($calendar);
			}
		}
		else {
			$calendarsView[] = '__N/A__';
		}

		$roleView = join( ', ', $roleView );
		$return['role'] = $roleView;

		$calendarsView = $this->ui->makeListInline( $calendarsView );
		$return['calendar'] = $calendarsView;

		return $return;
	}

	public function listingCellMenu( $model )
	{
		$id = $model->getId();
		$return = array();

		$return['edit'] = array( get_edit_user_link($id), '__Edit WordPress User__' );

		return $return;
	}
}