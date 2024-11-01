<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
#[AllowDynamicProperties]
class SH4_Users_Html_Admin_View_Employee
{
	public function __construct(
		HC3_Hooks $hooks,
		HC3_Ui $ui,
		HC3_Ui_Layout1 $layout,

		SH4_App_Query $appQuery,

		SH4_Employees_Query $employeesQuery,
		HC3_Users_Query $usersQuery,

		SH4_Employees_Presenter $employeesPresenter
	)
	{
		$this->self = $hooks->wrap($this);
		$this->ui = $ui;
		$this->layout = $layout;

		$this->appQuery = $hooks->wrap($appQuery);

		$this->employeesQuery = $hooks->wrap($employeesQuery);
		$this->usersQuery = $hooks->wrap($usersQuery);

		$this->employeesPresenter = $hooks->wrap($employeesPresenter);
	}

	public function render( $id )
	{
		$model = $this->usersQuery->findById($id);

		$employees = $this->employeesQuery->findActive();
	// remove open shift
		unset( $employees[0] );

		$header = array( 'title' => '__Employee__', 'action' => NULL );
		$rows = array();

		foreach( $employees as $e ){
			$employeeId = $e->getId();
			$row = array();

			$employeeView = $this->employeesPresenter->presentTitle( $e );
			$row['title'] = $employeeView;

			$thisUser = $this->appQuery->findUserByEmployee( $e );
			if( $thisUser ){
				$row['action'] = '__Already Linked To Another User Account__';
			}
			else {
				$form = $this->ui->makeForm( 
					'admin/users/' . $id . '/employee/' . $employeeId,
					$this->ui->makeInputSubmit( '__Link To This Employee__' )
						->tag('primary')
						// ->tag('block')
					);
				$row['action'] = $form;
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
		$out = '__Link Employee To User Account__';
		return $out;
	}

	public function breadcrumb( $model )
	{
		$return = array();
		$return['admin'] = array( 'admin', '__Administration__' );
		$return['users'] = array( 'admin/users', '__Users__' );
		$return['users/edit'] = $model->getDisplayName();
		return $return;
	}
}
