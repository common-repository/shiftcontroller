<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
#[AllowDynamicProperties]
class SH4_Ical_Html_User_View_Profile_Ical
{
	public function __construct(
		HC3_Hooks $hooks,
		HC3_Ui $ui,
		HC3_Ui_Layout1 $layout,
		HC3_Post $post,

		HC3_Time $t,

		HC3_Auth $auth,
		HC3_IPermission $permission,
		HC3_UriAction $uriAction,
		HC3_Settings $settings,

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

		$this->post = $hooks->wrap($post);
		$this->auth = $hooks->wrap( $auth );
		$this->permission = $hooks->wrap($permission);
		$this->calendarsPresenter = $hooks->wrap( $calendarsPresenter );
		$this->employeesPresenter = $hooks->wrap( $employeesPresenter );

		$this->calendarsPermissions = $hooks->wrap( $calendarsPermissions );
		$this->calendarsQuery = $hooks->wrap( $calendarsQuery );

		$this->appQuery = $hooks->wrap( $appQuery );
		$this->scheduleViewCommon = $hooks->wrap( $scheduleViewCommon );

		$this->settings = $hooks->wrap($settings);
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

		if( $meEmployee ){
			$meEmployeeId = $meEmployee->getId();
			$links[] = array( 'ical/' . $token . '/x/' . $meEmployeeId, '__My Schedule__' );
		}

		if( $calendarsAsManager ){
			$links[] = array( 'ical/' . $token, '__All__' );
			$links[] = array( 'ical/' . $token . '/s', '__All__' . ': ' . '__Shifts__' );
			$links[] = array( 'ical/' . $token . '/t', '__All__' . ': ' . '__Time Off__' );

			foreach( $calendars as $calendar ){
				$calendarId = $calendar->getId();
				$calendarTitle = $this->calendarsPresenter->presentTitle( $calendar );
				$links[] = array( 'ical/' . $token . '/' . $calendarId, $calendarTitle );
			}

			$employees = $this->scheduleViewCommon->findAllEmployees();
			if( count($employees) > 1 ){
				foreach( $employees as $employee ){
					$employeeId = $employee->getId();
					$employeeTitle = $this->employeesPresenter->presentTitle( $employee );
					$links[] = array( 'ical/' . $token . '/x/' . $employeeId, $employeeTitle );
				}
			}
		}

		$note = '__You can automatically export your shifts to other 3rd party applications with the iCal feed option.__';
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

	// local
		$thisOut = array();
			$labelOut = array();
			$label = '__Local Applications__';
			$label = $this->ui->makeBlock($label)
				->tag('font-size', 5)
				;
			$labelOut[] = $label;
			$note = 'Apple Calendar, Microsoft Outlook, Lotus Organizer ...';
			$note = $this->ui->makeBlock( $note )
				->tag('muted')
				;
			$labelOut[] = $note;
			$labelOut = $this->ui->makeList( $labelOut )->gutter(0);
			$thisOut[] = $labelOut;

		$note = '__Click one of these links to set it up.__';
		$thisOut[] = $note;

		$linksView = array();
		reset( $links );
		foreach( $links as $linkArray ){
			list( $to, $linkLabel ) = $linkArray;
			// $to = $this->uriAction->makeUrl( $to, 'webcal://' );
			$to = site_url( '/?hcs=sh4&hca=' . $to );
			$pos = strpos( $to, '//' );
			$to = 'webcal://' . substr( $to, $pos + 2 );

			// $linkView = $link;
			$linkView = $this->ui->makeAhref( $to, $linkLabel )
				->tag('secondary')
				->tag('block')
				// ->addAttr('style', 'border: red 1px solid; display: block;')
				;
			$linkView = $this->ui->makeBlock( $linkView )
				// ->tag('secondary')
				;
			$linksView[] = $linkView;
		}
		$linksView = $this->ui->makeListInline( $linksView );
		$thisOut[] = $linksView;

		$thisOut = $this->ui->makeList( $thisOut );
		$out[] = $thisOut;

	// online
		$thisOut = array();

			$labelOut = array();
			$label = '__Online Services__';
			$label = $this->ui->makeBlock($label)
				->tag('font-size', 5)
				;
			$labelOut[] = $label;
			$note = 'Google Calendar';
			$note = $this->ui->makeBlock( $note )
				->tag('muted')
				;
			$labelOut[] = $note;
			$labelOut = $this->ui->makeList( $labelOut )->gutter(0);
			$thisOut[] = $labelOut;

		$thisOut[] = '__Inside your Google Calendar open the Other Calendars menu in the left sidebar and select the Add by URL option. Paste one of these links then click the Add Calendar button. This will automatically add your shifts to Google Calendar.__';
		$note = '__Please note that shifts do not sync immediately. It could take up to 12 hours for your shifts to show up (according to Google support website).__';
		$note = $this->ui->makeBlock( $note )
			->tag('padding', 2)
			->tag('border')
			->tag('border-color', 'orange')
			;
		$thisOut[] = $note;

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

		$form = $this->ui->makeForm(
			'user/profile/ical',
			$this->self->form()
		);
		$out[] = $form;


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
		$out = '__iCal Sync__';
		return $out;
	}

	public function breadcrumb()
	{
		$return = array();
		$return['profile'] = array( 'user/profile', '__Profile__' );
		return $return;
	}

	public function form()
	{
		$currentUser = $this->auth->getCurrentUser();
		$userId = $currentUser->getId();
		$pname = 'ical_max_advance' . '_' . $userId;

		$v = $this->settings->get( $pname );

		if( ! $v ) $v = '3 month';

		$inputs = array();

		$months = range( 1, 12 );
		foreach( $months as $m ){
			$option[ $m . ' month' ] = $m . ' __Month__';
		}

		$maxDate = $this->ui->makeInputSelect( 'ical_max_advance', '', $option, $v );
		$maxDate = $this->ui->makeListInline( array($maxDate, '__Month__') );
		$maxDate = $this->ui->makeLabelled( '__Latest Date In__', $maxDate );

		$inputs[] = $maxDate;

		$inputs = $this->ui->makeList( $inputs );

		$buttons = $this->ui->makeInputSubmit( '__Save__')
			->tag('primary')
			;

		$out = $this->ui->makeList()
			->add( $inputs )
			->add( $buttons )
			;

		return $out;
	}

	public function post()
	{
		$currentUser = $this->auth->getCurrentUser();
		$userId = $currentUser->getId();
		$pname = 'ical_max_advance' . '_' . $userId;

		$v = $this->post->get('ical_max_advance');
		$this->settings->set( $pname, $v );

		$ret = array( 'user/profile/ical', '__Settings Updated__' );
		return $ret;
	}
}