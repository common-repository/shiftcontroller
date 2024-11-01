<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
#[AllowDynamicProperties]
class SH4_Employees_Html_Admin_View_User
{
	public function __construct(
		HC3_Hooks $hooks,
		HC3_Ui $ui,
		HC3_Ui_Layout1 $layout,

		SH4_App_Query $appQuery,

		SH4_Employees_Query $query,
		HC3_Users_Presenter $usersPresenter,
		HC3_Users_Query $users
	)
	{
		$this->self = $hooks->wrap($this);
		$this->ui = $ui;
		$this->layout = $layout;

		$this->appQuery = $hooks->wrap($appQuery);
		$this->query = $hooks->wrap($query);

		$this->usersPresenter = $hooks->wrap($usersPresenter);
		$this->users = $hooks->wrap($users);
	}

	public function render( $id )
	{
		$model = $this->query->findById($id);

		$users = $this->users->findAll();

		$takenUsers = $this->appQuery->findAllUsersWithEmployee();
		$takenUsersIds = array_keys($takenUsers);

		$header = array( 'title' => '__User__', 'action' => NULL );
		$rows = array();

		foreach( $users as $e ){
			$userId = $e->getId();
			$row = array();

			$userView = $this->usersPresenter->presentTitle( $e );
			$row['title'] = $userView;

			if( in_array($userId, $takenUsersIds) ){
				$row['action'] = '__Already Linked To Another Employee__';
			}
			else {
				$form = $this->ui->makeForm( 
					'admin/employees/' . $id . '/user/' . $userId,
					$this->ui->makeInputSubmit( '__Link To This User Account__' )
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
		$return['employees'] = array( 'admin/employees', '__Employees__' );
		$return['employees/edit'] = array( 'admin/employees/' . $model->getId(), $model->getTitle() );
		return $return;
	}
}
