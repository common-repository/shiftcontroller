<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
#[AllowDynamicProperties]
class SH4_Users_Html_User_View_Profile_Roles
{
	public function __construct(
		HC3_Hooks $hooks,
		HC3_Ui $ui,
		HC3_Ui_Layout1 $layout,

		HC3_Auth $auth,
		HC3_IPermission $permission,

		SH4_Calendars_Presenter $calendarsPresenter,
		SH4_Employees_Presenter $employeesPresenter,
		SH4_App_Query $appQuery
	)
	{
		$this->self = $hooks->wrap($this);

		$this->ui = $ui;
		$this->layout = $layout;

		$this->auth = $hooks->wrap( $auth );
		$this->permission = $hooks->wrap($permission);
		$this->calendarsPresenter = $hooks->wrap( $calendarsPresenter );
		$this->employeesPresenter = $hooks->wrap( $employeesPresenter );

		$this->appQuery = $hooks->wrap( $appQuery );
	}

	public function render()
	{
		$user = $this->auth->getCurrentUser();

		$out = array();

		$roleAdmin = $this->self->renderAdmin();
		if( $roleAdmin )
			$out[] = $roleAdmin;

		$roleManager = $this->self->renderManager();
		if( $roleManager ){
			$out[] = $roleManager;
		}

		$roleViewer = $this->self->renderViewer();
		if( $roleViewer ){
			$out[] = $roleViewer;
		}

		$roleEmployee = $this->self->renderEmployee();
		if( $roleEmployee ){
			$out[] = $roleEmployee;
		}

		if( ! $out ){
			$out[] = '__You have no roles at the moment__';
		}

		$out = $this->ui->makeList( $out );

		$this->layout
			->setContent( $out )
			->setBreadcrumb( $this->self->breadcrumb() )
			->setHeader( $this->self->header() )
			;

		$out = $this->layout->render();
		return $out;
	}

	public function renderAdmin()
	{
		$out = NULL;

		$user = $this->auth->getCurrentUser();
		if( ! $this->permission->isAdmin( $user ) ){
			return $out;
		}

		$out = array();

		$label = '__Admin__';
		$label = $this->ui->makeBlock( $label )
			->tag('font-size', 4)
			;
		$out[] = $label;

		$out = $this->ui->makeList( $out )->gutter(1);
		$out = $this->ui->makeBlock( $out )
			->tag('border')
			->tag('padding', 2)
			;

		return $out;
	}

	public function renderManager()
	{
		$out = NULL;

		$user = $this->auth->getCurrentUser();
		$calendars = $this->appQuery->findCalendarsManagedByUser( $user );
		if( ! $calendars ){
			return $out;
		}

		$out = array();

		$label = '__Manager__';
		$label = $this->ui->makeBlock( $label )
			->tag('font-size', 4)
			;
		$out[] = $label;

		$calendarsView = array();
		foreach( $calendars as $calendar ){
			$calendarsView[] = $this->calendarsPresenter->presentTitle( $calendar );
		}
		$calendarsView = $this->ui->makeListInline( $calendarsView );
		$out[] = $calendarsView;

		$out = $this->ui->makeList( $out )->gutter(1);
		$out = $this->ui->makeBlock( $out )
			->tag('border')
			->tag('padding', 2)
			;

		return $out;
	}

	public function renderViewer()
	{
		$out = NULL;

		$user = $this->auth->getCurrentUser();
		$calendars = $this->appQuery->findCalendarsViewedByUser( $user );
		if( ! $calendars ){
			return $out;
		}

		$out = array();

		$label = '__Viewer__';
		$label = $this->ui->makeBlock( $label )
			->tag('font-size', 4)
			;
		$out[] = $label;

		$calendarsView = array();
		foreach( $calendars as $calendar ){
			$calendarsView[] = $this->calendarsPresenter->presentTitle( $calendar );
		}
		$calendarsView = $this->ui->makeListInline( $calendarsView );
		$out[] = $calendarsView;

		$out = $this->ui->makeList( $out )->gutter(1);
		$out = $this->ui->makeBlock( $out )
			->tag('border')
			->tag('padding', 2)
			;

		return $out;
	}

	public function renderEmployee()
	{
		$out = NULL;

		$user = $this->auth->getCurrentUser();
		$employee = $this->appQuery->findEmployeeByUser( $user );
		if( ! $employee ){
			return $out;
		}

		$label = '__Employee__';
		$label = $this->ui->makeBlock( $label )
			->tag('font-size', 4)
			;
		$out[] = $label;

		$employeeView = $this->employeesPresenter->presentTitle( $employee );
		$out[] = $employeeView;

		$calendars = $this->appQuery->findCalendarsForEmployee( $employee );
		$calendarsView = array();
		foreach( $calendars as $calendar ){
			$calendarsView[] = $this->calendarsPresenter->presentTitle($calendar);
		}
		$calendarsView = $this->ui->makeListInline( $calendarsView );
		$out[] = $calendarsView;

		$out = $this->ui->makeList( $out )->gutter(1);
		$out = $this->ui->makeBlock( $out )
			->tag('border')
			->tag('padding', 2)
			;

		return $out;
	}

	public function header()
	{
		$out = '__My Roles__';
		return $out;
	}

	public function breadcrumb()
	{
		$return = array();
		$return['profile'] = array( 'user/profile', '__Profile__' );
		return $return;
	}
}