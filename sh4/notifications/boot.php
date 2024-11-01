<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class SH4_Notifications_Boot
{
	public function __construct(
		HC3_Router $router,
		HC3_Ui $ui,
		HC3_Acl $acl,
		HC3_Notificator $notificator,
		SH4_Notifications_Service $notificationsService,
		HC3_Hooks $hooks
		)
	{
		$hooks
			->add( 'sh4/users/html/user/view/profile::menu::after', function( $ret ){
				$ret['notifications'] = array( 'user/profile/notifications', '__Notifications__' );
				return $ret;
			})

			->add( 'sh4/schedule/html/view/miscoptions::options::after', array('SH4_Notifications_Html_Admin_View_TurnOnoff', 'render') )

			->add( 'sh4/users/html/admin/view/index::listingcell::after', function( $return, $args ) use ( $notificator, $ui ){
				$notes = array();
				if( array_key_exists('notes', $return) ){
					$notes[] = $return['notes'];
				}

				$thisView = array();
				$user = $args[0];
				$userId = $user->getId();

				$actions = array();
				if( ! $notificator->isOnForUser($user) ){
					$thisNote = '__Notifications Turned Off__';
					$thisView[] = $thisNote;
					$actions['user'] = array( 'admin/users/' . $userId . '/notifications/on', NULL, '__Turn On__' );
				}
				else {
					$actions['user'] = array( 'admin/users/' . $userId . '/notifications/off', NULL, '__Turn Off Notifications__' );
				}

				$actions = $ui->helperActionsFromArray( $actions );
				if( $actions ){
					$actions = $ui->makeListInline( $actions )->gutter(1)->separated();
					$thisView[] = $actions;
				}

				if( $thisView ){
					$thisView = $ui->makeList($thisView)->gutter(0);
					$notes[] = $thisView;
				}

				$notes = $ui->makeList( $notes );

				$return['notes'] = $notes;
				return $return;
				})
			;

		$router
			->register( 'get:admin/notifications/{id}', array('SH4_Notifications_Html_Admin_View_Index', 'render') )

			->register( 'post:admin/notifications/{calendar}/{notification}/enable', array('SH4_Notifications_Html_Admin_Controller_Enable', 'execute') )
			->register( 'post:admin/notifications/{calendar}/{notification}/disable', array('SH4_Notifications_Html_Admin_Controller_Disable', 'execute') )

			->register( 'get:admin/notifications/{calendar}/{notification}', array('SH4_Notifications_Html_Admin_View_Edit', 'render') )
			->register( 'post:admin/notifications/{calendar}/{notification}/reset', array('SH4_Notifications_Html_Admin_Controller_Edit', 'executeReset') )
			->register( 'post:admin/notifications/{calendar}/{notification}', array('SH4_Notifications_Html_Admin_Controller_Edit', 'execute') )

			->register( 'get:user/profile/notifications', array('SH4_Notifications_Html_User_View_Profile_Notifications', 'render') )
			->register( 'post:user/profile/notifications', array('SH4_Notifications_Html_User_Controller_Profile_Notifications', 'execute') )

			->register( 'post:admin/users/{user}/notifications/on', array('SH4_Notifications_Html_Admin_Users_Controller_Notifications', 'executeOn') )
			->register( 'post:admin/users/{user}/notifications/off', array('SH4_Notifications_Html_Admin_Users_Controller_Notifications', 'executeOff') )

			->register( 'post:admin/notifications/turnonoff', array('SH4_Notifications_Html_Admin_Controller_Turnonoff', 'execute') )
			;

		$notificationsService
			->register( 'sh4/shifts/command::publish', 'email_manager_publish', 'SH4_Notifications_Email_Manager_Publish', TRUE )
			->register( 'sh4/shifts/command::publish', 'email_employee_publish', 'SH4_Notifications_Email_Employee_Publish', TRUE )

			->register( 'sh4/shifts/command::draft', 'email_manager_draft', 'SH4_Notifications_Email_Manager_Draft', FALSE )
			->register( 'sh4/shifts/command::draft', 'email_employee_draft', 'SH4_Notifications_Email_Employee_Draft', FALSE )

			->register( 'sh4/shifts/command::unpublish', 'email_manager_unpublish', 'SH4_Notifications_Email_Manager_Unpublish', FALSE )
			->register( 'sh4/shifts/command::unpublish', 'email_employee_unpublish', 'SH4_Notifications_Email_Employee_Unpublish', TRUE )

			->register( 'sh4/shifts/command::reschedule', 'email_manager_reschedule', 'SH4_Notifications_Email_Manager_Reschedule', TRUE )
			->register( 'sh4/shifts/command::reschedule', 'email_employee_reschedule', 'SH4_Notifications_Email_Employee_Reschedule', TRUE )

			->register( 'sh4/shifts/command::delete', 'email_manager_delete', 'SH4_Notifications_Email_Manager_Delete', TRUE )
			->register( 'sh4/shifts/command::delete', 'email_employee_delete', 'SH4_Notifications_Email_Employee_Delete', TRUE )
			;

		$acl
			->register( 'get:user/profile/notifications', array('SH4_App_Acl', 'checkUserProfile') )
			;
	}
}