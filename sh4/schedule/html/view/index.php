<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
#[AllowDynamicProperties]
class SH4_Schedule_Html_View_Index
{
	protected $linksInFooter = TRUE;

	public function __construct(
		HC3_Hooks $hooks,
		HC3_Time $t,
		HC3_Request $request,
		HC3_Settings $settings,
		HC3_Ui $ui,
		HC3_Auth $auth,
		HC3_Acl $acl,

		HC3_Uri $uri,

		HC3_Ui_Layout1 $layout,
		HC3_Enqueuer $enqueuer,

		SH4_App_Query $appQuery,

		SH4_Calendars_Permissions $cp,
		SH4_Schedule_Html_View_Common $common,

		SH4_Schedule_Html_View_List $viewList,
		SH4_Schedule_Html_View_Day $viewDay,
		SH4_Schedule_Html_View_Week $viewWeek,
		SH4_Schedule_Html_View_FourWeeks $viewFourWeeks,
		SH4_Schedule_Html_View_TwoWeeks $viewTwoWeeks,
		SH4_Schedule_Html_View_Month $viewMonth,
		SH4_Schedule_Html_View_Report $viewReport,
		SH4_Schedule_Html_View_Download $viewDownload,

		SH4_Schedule_Html_View_ControlOptions $viewControlOptions,
		SH4_Schedule_Html_View_MiscOptions $viewMiscOptions,
		SH4_Schedule_Html_View_NewOptions $viewNewOptions,
		SH4_Schedule_Html_View_ControlDates $viewControlDates
		)
	{
		$this->cp = $hooks->wrap( $cp );
		$this->common = $hooks->wrap( $common );

		$this->self = $hooks->wrap( $this );
		$this->layout = $layout;
		$this->auth = $auth;
		$this->acl = $acl;
		$this->enqueuer = $enqueuer;

		$this->appQuery = $hooks->wrap( $appQuery );
		$this->settings = $hooks->wrap( $settings );

		$this->ui = $ui;
		$this->uri = $uri;
		$this->t = $t;
		$this->request = $request;

		$this->viewList = $hooks->wrap( $viewList );
		$this->viewDay = $hooks->wrap( $viewDay );
		$this->viewWeek = $hooks->wrap( $viewWeek );
		$this->viewMonth = $hooks->wrap( $viewMonth );
		$this->viewFourWeeks = $hooks->wrap( $viewFourWeeks );
		$this->viewTwoWeeks = $hooks->wrap( $viewTwoWeeks );
		$this->viewReport = $hooks->wrap( $viewReport );
		$this->viewDownload = $hooks->wrap( $viewDownload );

		$this->viewControlOptions = $hooks->wrap( $viewControlOptions );
		$this->viewControlDates = $hooks->wrap( $viewControlDates );
		$this->viewMiscOptions = $hooks->wrap( $viewMiscOptions );
		$this->viewNewOptions = $hooks->wrap( $viewNewOptions );

		$this->linksInFooter = $this->settings->get( 'datetime_hide_bottom_date_navigation', false ) ? false : true;
	}

	public function renderMy()
	{
		$this->enqueuer->addScript('schedule', 'sh4/schedule/assets/js/calendar.js?hcver=' . SH4_VERSION);

		$out = NULL;
		$slug = $this->request->getSlug();

		$currentUser = $this->auth->getCurrentUser();
		$currentUserId = $currentUser->getId();
		if( ! $currentUserId ){
			return $out;
		}

	// as employee
		$meEmployee = $this->appQuery->findEmployeeByUser( $currentUser );
		if( ! $meEmployee ){
			return $out;
		}

		$meEmployeeId = $meEmployee->getId();

		$start = $this->t->setNow()->formatDateDb();
		$end = $this->t->modify('+6 days')->formatDateDb();

		$type = NULL;
		$type = HC3_Session::instance()->getUserdata('scheduleViewType'); // week/list/report
		if( ! $type ){
			$type = $this->settings->get( 'default_schedule_view' );
		}
		if( ! $type ){
			$type = 'week';
		}

		$groupby = NULL;
		$groupby = HC3_Session::instance()->getUserdata('scheduleViewGroupby');
		if( ! $groupby ){
			$groupby = $this->settings->get( 'default_schedule_groupby' );
		}
		if( (! $groupby) OR ($groupby == 'employee') ){
			$groupby = 'none';
		}

		$allowed = array();

		$employee = array( $meEmployeeId );

		$calendar = HC3_Session::instance()->getUserdata('scheduleViewCalendar');
		if( ! $calendar ){
			$calendar = array();
		}

		$hideui = array( 
			'filter-employee',
			// 'filter-calendar',
			// 'groupby',
			// 'type-month', 'type-day', 'type-week', 
			);
// type
// type-month
// type-week
// type-day
// type-list
// type-report
// groupby
// print
// download
// filter-calendar
// filter-employee

		$defaultParams = array(
			'conflict'	=> 0,
			'pickup'		=> 0,
			'type'		=> $type,
			'groupby'	=> $groupby,
			'start'		=> $start,
			'end'		=> $end,
			'employee'	=> $employee,
			'calendar'	=> $calendar,
			'hideui'	=> $hideui
			);

		$params = $this->request->getParams();
		$params = array_merge( $defaultParams, $params );

		foreach( $params as $k => $v ){
			$this->request
				->initParam( $k, $v )
				;
		}

		// $params['employee'] = HC3_Functions::removeFromArray( $params['employee'], 'x' );
		$params['calendar'] = HC3_Functions::removeFromArray( $params['calendar'], 'x' );

		$this->request
			// ->setParam('employee', $params['employee'])
			->setParam('calendar', $params['calendar'])
			;

		HC3_Session::instance()
			->setUserdata( 'scheduleViewType', $params['type'] )
			->setUserdata( 'scheduleViewGroupby', $params['groupby'] )
			// ->setUserdata( 'scheduleViewEmployee', $params['employee'] )
			->setUserdata( 'scheduleViewCalendar', $params['calendar'] )
			;

		$toParams = $this->request->getParams('withoutdefault');
		$scheduleLink = array( $slug, $toParams );

		HC3_Session::instance()
			->setUserdata( 'scheduleLink', $scheduleLink )
			;

// $scheduleLink2 = HC3_Session::instance()
// 	->getUserdata( 'scheduleLink' )
// 	;
// $to = $this->uri->makeUrl( $scheduleLink2 );
// echo "TO = '$to'<br>";
// exit;

		$type = $params['type'];
		$groupby = $params['groupby'];

		$view = NULL;

		if( array_key_exists('download', $params) && $params['download'] ){
			$view = $this->viewDownload;
			$groupby = NULL;
		}
		else {
			switch( $type ){
				case '4weeks':
					$view = $this->viewFourWeeks;
					break;
				case '2weeks':
					$view = $this->viewTwoWeeks;
					break;
				case 'month':
					$view = $this->viewMonth;
					break;
				case 'week':
					$view = $this->viewWeek;
					break;
				case 'day':
					$view = $this->viewDay;
					break;
				case 'list':
					$view = $this->viewList;
					break;
				case 'report':
					$view = $this->viewReport;
					break;
			}
		}

		switch( $groupby ){
			// case 'employee':
			// 	$out = $view->renderByEmployee();
			// 	break;
			case 'calendar':
				$out = $view->renderByCalendar();
				break;
			default:
				$out = $view->render();
				break;
		}

		if( ! in_array($type, array('report')) ){
			$miscOptions = $this->viewMiscOptions->options();
			if( $this->request->isPrintView() ){
				$miscOptions[2] = NULL;
				$miscOptions[3] = NULL;
			}

			if( $miscOptions ){
				$misc = $this->ui->makeGrid();
				$misc->add( isset($miscOptions[1]) ? $miscOptions[1] : NULL, 3, 12 );
				$misc->add( isset($miscOptions[2]) ? $miscOptions[2] : NULL, 7, 12 );
				$misc->add( isset($miscOptions[3]) ? $miscOptions[3] : NULL, 2, 12 );

				$out = $this->ui->makeList( array($misc, $out) )
					->gutter(3)
					;
			}
		}

		$src = $this->uri->currentUrl();
		$this->uri->fromUrl( $src );

		$uriParams = $this->uri->getParams();
		$src = $this->uri
			->makeUrl( array('myschedule', $uriParams) )
			;

		$out = $this->ui->makeBlock( $out )
			->addAttr( 'id', 'sh4-shifts-calendar' )
			->addAttr( 'data-src', $src )
			;

		if( $this->request->isAjax() ){
			return $out;
		}

		if( ! $this->request->isPrintView() ){
			$headerControl = $this->viewControlOptions->render();
			if( $headerControl ){
				$out = $this->ui->makeList( array($headerControl, $out) );
			}
		}

		if( ! in_array($type, array('report')) ){
			if( ! $this->request->isPrintView() ){
				$new = $this->viewNewOptions->render();
				$out = $this->ui->makeList( array($new, $out) )
					->gutter(3)
					;
			}
		}

		$headerDate = $this->viewControlDates->render();
		if( $headerDate ){
			$out = $this->ui->makeList( array($headerDate, $out) );
		}

		$modal = $this->_renderModal();
		$out = $this->ui->makeCollection( array($out, $modal) );

		$header = '__My Schedule__';
		$header = $this->ui->makeBlock( $header )
			->tag('font-size', 5)
			;

		$toCheck = 'schedule';
		$everyoneScheduleDisabled = $this->checkEveryoneScheduleDisabled();

		$out = $this->ui->makeList( array($header, $out) )
			->gutter(3)
			;

	// is wp?
		if( defined('WPINC') && (! is_admin()) ){
			$tos = $this->self->toplinks();
			// $tos = array(
				// array( 'admin', '__Administration__' ),
				// array( 'user/profile', '__Profile__' ),
				// );

			$headerLinks = array();
			foreach( $tos as $to ){
				$checkTo = is_array( $to[0] ) ? $to[0][0] : $to[0];
				if( ! $this->acl->check('get:' . $checkTo) ){
					continue;
				}

				$headerLinks[] = $this->ui->makeAhref( $to[0], $to[1] )
					->tag('secondary')
					;
			}

			if( $headerLinks ){
				$headerLinks = $this->ui->makeListInline( $headerLinks )
					->gutter(2)
					;
				$out = $this->ui->makeList( array($headerLinks, $out) )
					->gutter(3)
					;
			}
		}

	// add links to footer
		if( $this->linksInFooter && $headerDate ){
		// rerender to change id for the form
			$footerDate = $this->viewControlDates->render();
			$out = $this->ui->makeList( array($out, $footerDate) )
				->gutter(3)
				;
		}

		$this->layout
			->setContent( $out )
			// ->setHeader( $header )
			;

		$out = $this->layout->render();

		return $out;
	}

	public function checkEmployee()
	{
		$return = NULL;

	// CHECK IF EVERYTHING IS DISABLED FOR EMPLOYEE THEN REDIRECT TO MYSCHEDULE
		$somethingAllowed = FALSE;

		$perms = array(
			'employee_view_others_publish',
			'employee_view_others_draft',
			'employee_view_open_publish',
			'employee_view_open_draft',
			'employee_pickup_others'
			);

		$allCalendars = $this->common->findAllCalendars();
		foreach( $allCalendars as $calendar ){
			reset( $perms );
			foreach( $perms as $perm ){
				if( $this->cp->get($calendar, $perm) ){
					$somethingAllowed = TRUE;
					break;
				}
			}

			if( $somethingAllowed ){
				break;
			}
		}

		if( ! $somethingAllowed ){
			$return = array( 'myschedule', NULL );
		}

		return $return;
	}

	public function checkAnon()
	{
		$return = NULL;

		if( $this->settings->get('users_login_required') ){
			$return = array( 'login', NULL );
			return $return;
		}

		// CHECK IF EVERYTHING IS DISABLED FOR VISITOR THEN REDIRECT TO LOGIN
		$somethingAllowed = FALSE;

		$perms = array(
			'visitor_view_others_publish',
			'visitor_view_others_draft',
			'visitor_view_open_publish',
			'visitor_view_open_draft'
			);

		$allCalendars = $this->common->findAllCalendars();
		foreach( $allCalendars as $calendar ){
			reset( $perms );
			foreach( $perms as $perm ){
				if( $this->cp->get($calendar, $perm) ){
					$somethingAllowed = TRUE;
					break;
				}
			}

			if( $somethingAllowed ){
				break;
			}
		}

		if( ! $somethingAllowed ){
			$return = array( 'login', NULL );
		}

		return $return;
	}

	public function checkEveryoneSchedule()
	{
		$ret = true;

		if( ! is_admin() ){
			return $ret;
		}

		$currentUserId = $this->auth->getCurrentUserId();
		if( ! $currentUserId ){
			return $ret;
		}

		$currentUser = $this->auth->getCurrentUser();
		$managedCalendars = $this->appQuery->findCalendarsManagedByUser( $currentUser );
		if( $managedCalendars ){
			return $ret;
		}

		$viewedCalendars = $this->appQuery->findCalendarsViewedByUser( $currentUser );
		if( $viewedCalendars ){
			return $ret;
		}

	// CHECK IF EVERYTHING IS DISABLED FOR EMPLOYEE THEN REDIRECT TO MYSCHEDULE
		$somethingAllowed = false;

		$perms = array(
			'employee_view_others_publish',
			'employee_view_others_draft',
			'employee_view_open_publish',
			'employee_view_open_draft',
			'employee_pickup_others'
			);

		$allCalendars = $this->common->findAllCalendars();
		foreach( $allCalendars as $calendar ){
			reset( $perms );
			foreach( $perms as $perm ){
				if( $this->cp->get($calendar, $perm) ){
					$somethingAllowed = true;
					break;
				}
			}
			if( $somethingAllowed ){
				break;
			}
		}

		if( $somethingAllowed ){
			return $ret;
		}

		$ret = false;
		return $ret;
	}

	public function checkEveryoneScheduleDisabled()
	{
		$return = NULL;

		$currentUserId = $this->auth->getCurrentUserId();
		if( $currentUserId ){
			$currentUser = $this->auth->getCurrentUser();
			$managedCalendars = $this->appQuery->findCalendarsManagedByUser( $currentUser );
			if( $managedCalendars ){
				return $return;
			}
			$viewedCalendars = $this->appQuery->findCalendarsViewedByUser( $currentUser );
			if( $viewedCalendars ){
				return $return;
			}

			$return = $this->checkEmployee();
			if( $return ){
				return $return;
			}
		}

		if( ! $currentUserId ){
			$return = $this->checkAnon();
			if( $return ){
				return $return;
			}
		}

		return $return;
	}

	public function render()
	{
		$this->enqueuer->addScript('schedule', 'sh4/schedule/assets/js/calendar.js?hcver=' . SH4_VERSION);

		$return = $this->checkEveryoneScheduleDisabled();
		if( $return ){
			return $return;
		}

		$slug = $this->request->getSlug();

		$start = $this->t->setNow()->formatDateDb();
		$end = $this->t->modify('+6 days')->formatDateDb();

		$type = NULL;
		$type = HC3_Session::instance()->getUserdata('scheduleViewType'); // week/list/report
		if( ! $type ){
			$type = $this->settings->get( 'default_schedule_view' );
		}
		if( ! $type ){
			$type = 'week';
		}

		$groupby = NULL;
		$groupby = HC3_Session::instance()->getUserdata('scheduleViewGroupby');
		if( ! $groupby ){
			$groupby = $this->settings->get( 'default_schedule_groupby' );
		}
		if( ! $groupby ){
			$groupby = 'employee';
		}

		// $employee = HC3_Session::instance()->getUserdata('scheduleViewEmployee');
		// if( ! $employee ){
			$employee = array();
		// }

		// $calendar = HC3_Session::instance()->getUserdata('scheduleViewCalendar');
		// if( ! $calendar ){
			$calendar = array();
		// }

		$hideui = array();

		$defaultParams = array(
			'conflict'	=> 0,
			'pickup'		=> 0,
			'type'		=> $type,
			'groupby'	=> $groupby,
			'start'		=> $start,
			'end'		=> $end,
			'employee'	=> $employee,
			'calendar'	=> $calendar,
			'hideui'	=> $hideui
			);

		$params = $this->request->getParams();
		$params = array_merge( $defaultParams, $params );

		foreach( $params as $k => $v ){
			$this->request
				->initParam( $k, $v )
				;
		}

		$params['employee'] = HC3_Functions::removeFromArray( $params['employee'], 'x' );
		$params['calendar'] = HC3_Functions::removeFromArray( $params['calendar'], 'x' );

		$this->request
			->setParam('employee', $params['employee'])
			->setParam('calendar', $params['calendar'])
			;

		HC3_Session::instance()
			->setUserdata( 'scheduleViewType', $params['type'] )
			->setUserdata( 'scheduleViewGroupby', $params['groupby'] )
			->setUserdata( 'scheduleViewEmployee', $params['employee'] )
			->setUserdata( 'scheduleViewCalendar', $params['calendar'] )
			;

		$toParams = $this->request->getParams('withoutdefault');
		$scheduleLink = array( $slug, $toParams );

		HC3_Session::instance()
			->setUserdata( 'scheduleLink', $scheduleLink )
			;

		$type = $params['type'];
		$groupby = $params['groupby'];

		$hideui = $params['hideui'];
		if( ('employee' == $groupby) && in_array('groupby-employee', $hideui) ){
			$groupby = NULL;
		}
		if( ('calendar' == $groupby) && in_array('groupby-calendar', $hideui) ){
			$groupby = NULL;
		}

		$view = NULL;

		if( array_key_exists('download', $params) && $params['download'] ){
			$view = $this->viewDownload;
			$groupby = NULL;
		}
		else {
			switch( $type ){
				case '4weeks':
					$view = $this->viewFourWeeks;
					break;
				case '2weeks':
					$view = $this->viewTwoWeeks;
					break;
				case 'month':
					$view = $this->viewMonth;
					break;
				case 'day':
					$view = $this->viewDay;
					break;
				case 'list':
					$view = $this->viewList;
					break;
				case 'report':
					$view = $this->viewReport;
					break;
				default:
					$view = $this->viewWeek;
					break;
			}
		}

		switch( $groupby ){
			case 'employee':
				$out = $view->renderByEmployee();
				break;
			case 'calendar':
				$out = $view->renderByCalendar();
				break;
			default:
				$out = $view->render();
				break;
		}

		if( ! in_array($type, array('report')) ){
			$miscOptions = $this->viewMiscOptions->options();
			if( $this->request->isPrintView() ){
				$miscOptions[2] = NULL;
				$miscOptions[3] = NULL;
			}

			if( $miscOptions ){
				$misc = $this->ui->makeGrid();
				$misc->add( isset($miscOptions[1]) ? $miscOptions[1] : NULL, 3, 12 );
				$misc->add( isset($miscOptions[2]) ? $miscOptions[2] : NULL, 7, 12 );
				$misc->add( isset($miscOptions[3]) ? $miscOptions[3] : NULL, 2, 12 );

				$out = $this->ui->makeList( array($misc, $out) )
					->gutter(3)
					;
			}
		}

		$src = $this->uri->currentUrl();
		$out = $this->ui->makeBlock( $out )
			->addAttr( 'id', 'sh4-shifts-calendar' )
			->addAttr( 'data-src', $src )
			;

		if( $this->request->isAjax() ){
			return $out;
		}

		if( ! $this->request->isPrintView() ){
			$headerControl = $this->viewControlOptions->render();
			if( $headerControl ){
				$out = $this->ui->makeList( array($headerControl, $out) );
			}
		}
		else {
			$header = $this->viewControlOptions->render();
			if( $header ){
				$out = $this->ui->makeList( array($header, $out) );
			}
		}

		if( ! in_array($type, array('report')) ){
			if( ! $this->request->isPrintView() ){
				$new = $this->viewNewOptions->render();
				$out = $this->ui->makeList( array($new, $out) )
					->gutter(3)
					;
			}
		}

		$headerDate = $this->viewControlDates->render();
		if( $headerDate ){
			$out = $this->ui->makeList( array($headerDate, $out) );
		}

		$modal = $this->_renderModal();
		$out = $this->ui->makeCollection( array($out, $modal) );

		$toCheck = 'myschedule';
		if( $this->acl->check('get:' . $toCheck) ){
			$hideui = isset( $params['hideui'] ) ? $params['hideui'] : array();
			if( ! array_intersect(array('pagetitle', 'all'), $hideui) ){
				$header = "__Everyone's Schedule__";
				$header = $this->ui->makeBlock( $header )
					->tag('font-size', 5)
					;

				$out = $this->ui->makeList( array($header, $out) )
					->gutter(3)
					;
			}
		}

	// is wp?
		if( defined('WPINC') && (! is_admin()) ){
			$tos = $this->self->toplinks();

			$headerLinks = array();
			foreach( $tos as $to ){
				$checkTo = is_array( $to[0] ) ? $to[0][0] : $to[0];
				if( ! $this->acl->check('get:' . $checkTo) ){
					continue;
				}

				$headerLinks[] = $this->ui->makeAhref( $to[0], $to[1] )
					->tag('secondary')
					;
			}

			if( $headerLinks ){
				$headerLinks = $this->ui->makeListInline( $headerLinks )
					->gutter(2)
					;
				$out = $this->ui->makeList( array($headerLinks, $out) )
					->gutter(3)
					;
			}
		}

	// add links to footer
		if( $this->linksInFooter && $headerDate ){
		// rerender to change id for the form
			$footerDate = $this->viewControlDates->render();
			$out = $this->ui->makeList( array($out, $footerDate) )
				->gutter(3)
				;
		}

		$this->layout
			->setContent( $out )
			// ->setHeader( $this->self->header() )
			;

		$out = $this->layout->render();
		return $out;
	}

	public function toplinks()
	{
		$ret = array();

		$params = $this->request->getParams();
		$hideui = isset( $params['hideui'] ) ? $params['hideui'] : array();
		if( array_intersect(array('pagemenu', 'all'), $hideui) ){
			return $ret;
		}

		$ret['admin'] = array( 'admin', '__Administration__' );
		$ret['user/profile'] = array( 'user/profile', '__Profile__' );

		return $ret;
	}

	protected function _renderModal()
	{
		$modalContent = $this->ui->makeBlock( '' )
			->addAttr( 'id', 'sh4-shifts-details-content' )
			;
		$modalCloser = $this->ui->makeAhref( '', '&times;' . ' ' . '__Close__' )
			->tag('padding', 2)
			->addAttr( 'id', 'sh4-shifts-details-closer' )
			->tag('border')
			->tag('color', 'red')
			->tag('font-size', 5)
			->addAttr('class', 'hc-closer')
			->addAttr('style', 'float: none; display: block; text-align: center; text-align: center;')
			;

		$modal = $this->ui->makeBlock( $this->ui->makeCollection(array($modalCloser, $modalContent)) )
			->addAttr( 'style', 'position: relative; display: none;' )
			->addAttr( 'id', 'sh4-shifts-details' )
			->tag('border')
			->tag('border-color', 'gray')
			->tag('padding', 3)
			;

		return $modal;
	}
}