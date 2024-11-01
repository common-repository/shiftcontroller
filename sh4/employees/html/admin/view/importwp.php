<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
#[AllowDynamicProperties]
class SH4_Employees_Html_Admin_View_ImportWp
{
	public function __construct(
		HC3_Hooks $hooks,
		HC3_Ui $ui,
		HC3_Post $post,
		HC3_Ui_Layout1 $layout,

		HC3_Users_Query $usersQuery,

		SH4_Employees_Query $employeesQuery,

		SH4_Calendars_Query $calendarsQuery,
		SH4_Calendars_Presenter $calendarsPresenter,

		SH4_Employees_Command $command,
		SH4_App_Query $appQuery,
		SH4_App_Command $appCommand
	)
	{
		$this->ui = $ui;
		$this->layout = $layout;

		$this->post = $hooks->wrap($post);
		$this->command = $hooks->wrap($command);

		$this->employeesQuery = $hooks->wrap( $employeesQuery );

		$this->calendarsQuery = $hooks->wrap( $calendarsQuery );
		$this->calendarsPresenter = $hooks->wrap( $calendarsPresenter );
		$this->usersQuery = $hooks->wrap($usersQuery);

		$this->appQuery = $hooks->wrap( $appQuery );
		$this->appCommand = $hooks->wrap( $appCommand );

		$this->self = $hooks->wrap($this);
	}

	public function post()
	{
	// VALIDATE POST
		$errors = array();

		$wpRoleNames = $this->post->get('wp_role');
		if( ! $wpRoleNames ){
			$errors['wp_role'] = '__Required Field__';
		}

		if( $errors ){
			throw new HC3_ExceptionArray( $errors );
		}

		$calendarIds = $this->post->get('calendar');
		if( ! $calendarIds ){
			$calendarIds = array();
		}
		$calendars = $calendarIds ? $this->calendarsQuery->findManyActiveById( $calendarIds ) : array();

		$successCount = $errorCount = 0;
		$errors = array();

		foreach( $wpRoleNames as $wpRoleName ){
			$args = array(
				'role'	=> $wpRoleName,
				);
			$wpUsers = get_users( $args );

			foreach( $wpUsers as $wpUser ){
				$wpUserId = $wpUser->ID;
				$title = $wpUser->display_name;
				$description = NULL;

				$user = $this->usersQuery->findById( $wpUserId );

				try {
					$employee = $this->appQuery->findEmployeeByUser( $user );

				// already exists - update title if needed
					if( $employee && $employee->getId() ){
						if( $employee->getTitle() != $title ){
							$this->command->changeTitle( $employee, $title );
						}
						$errors[] = array( $title . ' ' . '__WordPress User Already Linked To Employee__' );
						$errorCount++;
						continue;
					}
					else {
						$employeeId = $this->command->create( $title, $description );
						if( $employeeId ){
							$employee = $this->employeesQuery->findById( $employeeId );

							if( $calendars ){
								reset( $calendars );
								foreach( $calendars as $calendar ){
									$this->appCommand->addEmployeeToCalendar( $employee, $calendar );
								}
							}

							$this->appCommand->linkEmployeeToUser( $employee, $user );
						}
					}

					$successCount++;
				}
				catch( HC3_ExceptionArray $e ){
					$errors[] = $e->getErrors();
					$errorCount++;
				}
			}
		}

// _print_r( $errors );
// exit;

		$to = 'admin/employees';

		$msg = NULL;
		if( $successCount ){
			$msg .= '__New Employee Added__';
			if( $successCount > 1 ){
				$msg .= ' [' . $successCount . ']';
			}
		}

		if( $errors ){
			$error = array();
			foreach( $errors as $err1 ){
				foreach( $err1 as $title => $err2 ){
					$error[] = '__Error__' . ': ' . $err2 . '<br/>';
				}
			}

			HC3_Session::instance()->setFlashdata( 'error', $error );
		}

		$return = array( $to, $msg );
		return $return;
	}

	public function get()
	{
		$calendars = $this->calendarsQuery->findActive();

		$out = $this->render( $calendars );

		$this->layout
			->setContent( $out )
			->setBreadcrumb( $this->self->breadcrumb() )
			->setHeader( $this->self->header() )
			;

		$out = $this->layout->render();
		return $out;
	}

	public function header()
	{
		$out = '__Import WordPress Users As Employees__';
		return $out;
	}

	public function breadcrumb()
	{
		$return = array();
		$return['admin'] = array( 'admin', '__Administration__' );
		$return['employees'] = array( 'admin/employees', '__Employees__' );
		return $return;
	}

	public function render( array $calendars )
	{
		$out = array();

		$calendarsView = array();
		foreach( $calendars as $calendar ){
			$calendarId = $calendar->getId();
			$checked = FALSE;

			$title = $this->calendarsPresenter->presentTitle( $calendar );
			// $thisView = $this->ui->makeInputCheckbox( 'calendar[]', $thisView, $calendarId, $checked );
			$thisView = $this->ui->makeInputCheckbox( 'calendar[]', NULL, $calendarId, $checked );
			$thisView = $this->ui->makeListInline( array($thisView, $title) );

			$thisView = $this->ui->makeBlock( $thisView )
				->tag('border')
				->tag('padding', 1)
				;
			if( $checked ){
				$thisView
					->tag('border-color', 'olive')
					;
			}

			$calendarsView[] = $thisView;
		}

		$out = $this->ui->makeGrid();
		foreach( $calendarsView as $v ){
			$out->add( $v, 3, 12 );
		}

		$out = $this->ui->makeBlock( $out )
			->addAttr( 'id', 'sh4-calendars-employees' )
			;
		$toggleAll = $this->ui->makeInputCheckbox( 'toggle_all', '__Toggle All__' )
			->addAttr( 'class', 'sh4-calendars-employees-select-all' )
			;
		$out = $this->ui->makeList( array($toggleAll, $out) );
		$out = $this->ui->makeLabelled( '__Calendars__', $out );

	// add check all
		$js = $this->renderJs();
		$out = $this->ui->makeCollection( array($js, $out) );

		$buttons = $this->ui->makeInputSubmit( '__Import WordPress Users__' )
			->tag('primary')
			;

	// WP ROLES
		$wpRoleOptions = array();

		global $wp_roles;
		if( ! isset($wp_roles) ){
			$wp_roles = new WP_Roles();
		}

		$wpCountUsers = count_users();
		$wpCountUsers = $wpCountUsers['avail_roles'];
		$wpRoles = $wp_roles->get_names();

		foreach( $wpRoles as $k => $v ){
			$k = str_replace(' ', '_', $k);
			if( isset($wpCountUsers[$k]) && ($wpCountUsers[$k] > 0) ){
				$v .= ' (' . $wpCountUsers[$k] . ')';
				$wpRoleOptions[ $k ] = $v;
			}
		}

		$wpOut = $this->ui->makeInputCheckboxSet( 'wp_role', '__WordPress User Role__', $wpRoleOptions );

		$out = $this->ui->makeList( array($wpOut, $out) );
		$out = $this->ui->makeList( array($out, $buttons) );

		$out = $this->ui->makeForm(
			'admin/employees/importwp',
			$out
			);

		return $out;
	}

	public function renderJs()
	{
		ob_start();
?>

<script language="JavaScript">
( function(){

document.addEventListener('DOMContentLoaded', function()
{
	var self = this;
	var el = document.getElementById( 'sh4-calendars-employees' );

	this.toggleAll = function( e ){
		var checkers = el.getElementsByTagName( 'input' );
		for( ii = 0; ii < checkers.length; ii++ ){
			checkers[ii].checked = ! checkers[ii].checked;
		}
	};

	var togglers = document.getElementsByClassName( 'sh4-calendars-employees-select-all' );
	for( ii = 0; ii < togglers.length; ii++ ){
		togglers[ii].addEventListener( 'change', self.toggleAll );
	}
});

})();

</script>

<?php 
		return ob_get_clean();
	}
}
