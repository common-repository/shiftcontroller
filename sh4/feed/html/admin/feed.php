<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
#[AllowDynamicProperties]
class SH4_Feed_Html_Admin_Feed
{
	public function __construct(
		HC3_Hooks $hooks,
		HC3_Ui $ui,
		HC3_Ui_Layout1 $layout,

		HC3_Time $t,

		HC3_Auth $auth,
		HC3_IPermission $permission,
		HC3_UriAction $uriAction,

		SH4_Calendars_Permissions $calendarsPermissions,
		SH4_Calendars_Query $calendarsQuery,
		SH4_Calendars_Presenter $calendarsPresenter,
		SH4_Employees_Presenter $employeesPresenter,
		SH4_App_Query $appQuery,

		SH4_Schedule_Html_View_Common $scheduleViewCommon
	)
	{
		$this->self = $hooks->wrap($this);

		$this->ui = $ui;
		$this->layout = $layout;
		$this->uriAction = $uriAction;
		$this->t = $t;

		$this->auth = $hooks->wrap( $auth );
		$this->permission = $hooks->wrap($permission);
		$this->calendarsPresenter = $hooks->wrap( $calendarsPresenter );
		$this->employeesPresenter = $hooks->wrap( $employeesPresenter );

		$this->calendarsPermissions = $hooks->wrap( $calendarsPermissions );
		$this->calendarsQuery = $hooks->wrap( $calendarsQuery );

		$this->appQuery = $hooks->wrap( $appQuery );
		$this->scheduleViewCommon = $hooks->wrap( $scheduleViewCommon );
	}

	public function render()
	{
		$currentUser = $this->auth->getCurrentUser();
		$token = $this->auth->getTokenByUser( $currentUser );

		$out = array();

		$calendarsAsManager = $this->appQuery->findCalendarsManagedByUser( $currentUser );
		$calendarsAsViewer = $this->appQuery->findCalendarsViewedByUser( $currentUser );

		$calendarsAsEmployee = array();
		$meEmployee = $this->appQuery->findEmployeeByUser( $currentUser );
		if( $meEmployee ){
			$employeeCalendars = $this->appQuery->findCalendarsForEmployee( $meEmployee );
			foreach( $employeeCalendars as $thisCalendar ){
				$thisCalendarId = $thisCalendar->getId();
				$perms = array(
					'employee_view_own_publish',
					'employee_view_own_draft',
					'employee_view_others_publish',
					'employee_view_others_draft',
					'employee_view_open_publish',
					'employee_view_open_draft'
					);

				reset( $perms );
				foreach( $perms as $perm ){
					$thisPerm = $this->calendarsPermissions->get($thisCalendar, $perm);
					if( $thisPerm ){
						$calendarsAsEmployee[ $thisCalendarId ] = $thisCalendar;
						break;
					}
				}
			}
		}

	// links
		$links = array();

		$allCalendars = $calendarsAsManager + $calendarsAsViewer + $calendarsAsEmployee;
		$allCalendarsIds = array_keys( $allCalendars );
		$calendars = $this->calendarsQuery->findManyActiveById( $allCalendarsIds );

		// if( $meEmployee ){
			// $meEmployeeId = $meEmployee->getId();
			// $links[] = array( 'feed/' . $token . '/x/' . $meEmployeeId, '__My Schedule__' );
		// }

		if( $calendarsAsManager ){
			$links[] = array( 'feed/' . $token, '__All From Today__' );
			$links[] = array( 'json/' . $token, '__All From Today__' );

			$links[] = array( 'feed/' . $token . '/s', '__All From Today__' . ': ' . '__Shifts__' );
			$links[] = array( 'json/' . $token . '/t', '__All From Today__' . ': ' . '__Time Off__' );

			$links[] = array( 'feed/' . $token . '/x/x/20181123/20190101', '__From Date__' . ' / ' . '__To Date__' );
			$links[] = array( 'json/' . $token . '/x/x/20181123/20190101', '__From Date__' . ' / ' . '__To Date__' );

			foreach( $calendars as $calendar ){
				$calendarId = $calendar->getId();
				$calendarTitle = $this->calendarsPresenter->presentTitle( $calendar );
				$links[] = array( 'feed/' . $token . '/' . $calendarId, $calendarTitle );
				$links[] = array( 'json/' . $token . '/' . $calendarId, $calendarTitle );
			}

			$employees = $this->scheduleViewCommon->findAllEmployees();
			if( count($employees) > 1 ){
				foreach( $employees as $employee ){
					$employeeId = $employee->getId();
					$employeeTitle = $this->employeesPresenter->presentTitle( $employee );
					$links[] = array( 'feed/' . $token . '/x/' . $employeeId, $employeeTitle );
					$links[] = array( 'json/' . $token . '/x/' . $employeeId, $employeeTitle );
				}
			}
		}

		$note = '__Here is the automatic CSV/JSON feed of the shifts that can be used with other 3rd party applications.__';
		$out[] = $note;

		$timezone = $this->t->getTimezone();
		$timezoneView = $timezone->getName();

		$timezoneLabel = '__Timezone__';
		$timezoneLabel = $this->ui->makeBlock( $timezoneLabel )
			->tag('muted')
			->tag('font-size', 2)
			;
		$timezoneView = $this->ui->makeListInline( array($timezoneLabel, $timezoneView) );
		$timezoneView = $this->ui->makeBlock( $timezoneView )
			->tag('border')
			->tag('padding', 2)
			;

		$out[] = $timezoneView;

	// online
		$thisOut = array();

		$linksView = array();
		reset( $links );
		foreach( $links as $linkArray ){
			list( $to, $linkLabel ) = $linkArray;
			// $link = $this->uriAction->makeUrl( $to );
			$link = site_url( '/?hcs=sh4&hca=' . $to );
			$linkView = $this->ui->makeInputText( NULL, $linkLabel, $link )
				->addAttr( 'onclick', 'this.focus(); this.select();' )
				;
			$linksView[] = $linkView;
		}
		$linksView = $this->ui->makeList( $linksView );
		$thisOut[] = $linksView;


		$thisOut = $this->ui->makeList( $thisOut );
		$out[] = $thisOut;

		if( class_exists('SH4_CFields_Model') ){
			$f = ShiftController4::$instance->root();
			$cfieldsQuery = $f->make('SH4_CFields_Query');
			$cfields = $cfieldsQuery->read();
			if( $cfields ){
				$moreOut = '<p><strong>__Optional Arguments__</strong></p>';
				$moreOut .= '<p>';
				foreach( $cfields as $cfield ){
					$moreOut .= '<em>' . $cfield->getName() . '</em> (' . $cfield->getLabel() . ')<br>';
				}
				$moreOut .= '</p>';
				$out[] = $moreOut;
			}
		}

		$out = $this->ui->makeList( $out )->gutter(3);

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
		$out = '__Shifts Feed__';
		return $out;
	}

	public function breadcrumb()
	{
		$return = array();
		$return['admin'] = array( 'admin', '__Administration__' );
		return $return;
	}
}