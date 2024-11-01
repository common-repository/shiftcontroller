<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
#[AllowDynamicProperties]
class SH4_Calendars_Html_Admin_Controller_New
{
	public function __construct(
		HC3_Post $post,

		SH4_App_Query $appQuery,
		SH4_App_Command $appCommand,

		SH4_Calendars_Command $command,
		SH4_Calendars_Query $query,
		SH4_Calendars_Permissions $calendarPermissions,
		SH4_Notifications_Service $notificationService,

		HC3_Hooks $hooks
		)
	{
		$this->post = $hooks->wrap($post);
		$this->command = $hooks->wrap($command);
		$this->query = $hooks->wrap($query);

		$this->appQuery = $hooks->wrap($appQuery);
		$this->appCommand = $hooks->wrap($appCommand);
		$this->calendarPermissions = $hooks->wrap( $calendarPermissions );
		$this->notificationService = $hooks->wrap( $notificationService );
	}

	public function execute()
	{
		$title = $this->post->get('title');
		$color = $this->post->get('color');
		$description = $this->post->get('description');
		$type = $this->post->get('calendar_type');

		$copyFromId = $this->post->get('copy');

		$newId = $this->command->create( $title, $color, $description, $type );

		if( ! $copyFromId ){
			$to = 'admin/calendars/' . $newId . '/shifttypes/new';
		}
		else {
			$to = 'admin/calendars';

			$copyFrom = $this->query->findById( $copyFromId );
			$model = $this->query->findById( $newId );

		// employees
			$employees = $this->appQuery->findEmployeesForCalendar( $copyFrom );
			foreach( $employees as $e ){
				$this->appCommand->addEmployeeToCalendar( $e, $model );
			}

		// managers
			$managers = $this->appQuery->findManagersForCalendar( $copyFrom );
			foreach( $managers as $e ){
				$this->appCommand->addManagerToCalendar( $e, $model );
			}

		// viewers
			$viewers = $this->appQuery->findViewersForCalendar( $copyFrom );
			foreach( $viewers as $e ){
				$this->appCommand->addViewerToCalendar( $e, $model );
			}

		// shifttypes
			$shifTypes = $this->appQuery->findShiftTypesForCalendar( $copyFrom );
			foreach( $shifTypes as $e ){
				$this->appCommand->addShiftTypeToCalendar( $e, $model );
			}

		// permissions
			$permissions = $this->calendarPermissions->getAll( $copyFrom );
			foreach( $permissions as $k => $v ){
				$this->calendarPermissions->set( $model, $k, $v );
			}

		// notifications
			$notifications = $this->notificationService->findAll();
			foreach( $notifications as $notificationId => $notification ){
				if( $this->notificationService->isOn($copyFrom, $notificationId) ){
					$this->notificationService->setOn( $model, $notificationId );
				}
				else {
					$this->notificationService->setOff( $model, $notificationId );
				}

				$template = $this->notificationService->getTemplate( $copyFrom, $notificationId );
				$template2 = $this->notificationService->getTemplate( $model, $notificationId );
				if( $template2 != $template ){
					$this->notificationService->setTemplate( $model, $notificationId, $template );
				}
			}
		}

		$ret = array( $to, array('__New Calendar Added__') );
		return $ret;
	}
}