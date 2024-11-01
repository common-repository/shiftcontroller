<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
#[AllowDynamicProperties]
class SH4_Notifications_Html_Admin_View_Edit
{
	public function __construct(
		HC3_Hooks $hooks,
		HC3_Ui $ui,
		HC3_Ui_Layout1 $layout,

		SH4_Calendars_Query $calendarsQuery,
		SH4_Calendars_Presenter $calendarsPresenter,

		SH4_Notifications_Template $notificationsTemplate,
		SH4_Notifications_Service $notificationsService
		)
	{
		$this->self = $hooks->wrap( $this );
		$this->ui = $ui;
		$this->layout = $layout;

		$this->calendarsQuery = $hooks->wrap( $calendarsQuery );
		$this->calendarsPresenter = $hooks->wrap( $calendarsPresenter );

		$this->notificationsTemplate = $hooks->wrap( $notificationsTemplate );
		$this->notificationsService = $hooks->wrap( $notificationsService );
	}

	public function render( $calendarId, $notificationId )
	{
		$calendar = $this->calendarsQuery->findById( $calendarId );
		$notification = $this->notificationsService->findById( $notificationId );

		$template = $this->notificationsService->getTemplate( $calendar, $notificationId );

		$template = explode( "\n", $template );
		$templateSubject = array_shift( $template );
		$templateBody = join( "\n", $template );

// echo $model->getTemplateSubject( $calendar );
// exit;

		$inputs = $this->ui->makeList()
			->add( $this->ui->makeInputText( 'subject', '__Subject__', $templateSubject ) )
			->add( $this->ui->makeInputRichTextarea( 'body', '__Body__', $templateBody )->setRows(10) )
			;

		$tags = $this->notificationsTemplate->getTags( $calendar );

		$tagsView = $tags;
		$count = count($tagsView);
		for( $ii = 0; $ii < $count; $ii++ ){
			$tagsView[ $ii ] = '{' . strtoupper($tagsView[$ii]) . '}';
		}
		$tagsView = $this->ui->makeList( $tagsView )->gutter(1);
		$tagsView = $this->ui->makeLabelled( '__Template Tags__', $tagsView );

		$inputs = $this->ui->makeGrid()
			->add( $inputs, 9, 12 )
			->add( $tagsView, 3, 12 )
			;

		$buttons = array();

		$to = array( 'admin/notifications', $calendarId, $notificationId );
		$to = join( '/', $to );
		$buttons[] = $this->ui->makeInputSubmit( '__Save__')
			->setFormAction( $to )
			->tag('primary')
			;

		$to = array( 'admin/notifications', $calendarId, $notificationId, 'reset' );
		$to = join( '/', $to );
		$buttons[] = $this->ui->makeInputSubmit( '__Reset To Defaults__')
			->setFormAction( $to )
			->tag('secondary')
			->tag('font-size', 2)
			->tag('confirm')
			;
		$buttons = $this->ui->makeListInline( $buttons )->gutter(3);

		$form = $this->ui->makeList( array($inputs, $buttons) );

		$to = array( 'admin/notifications', $calendarId, $notificationId );
		$to = join( '/', $to );
		$form = $this->ui->makeForm( $to, $form );

		$this->layout
			->setContent( $form )
			->setBreadcrumb( $this->self->breadcrumb($calendar) )
			->setHeader( $this->self->header($calendar, $notification) )
			->setMenu( $this->self->menu($calendar, $notification) )
			;

		$out = $this->layout->render();
		return $out;
	}

	public function menu( SH4_Calendars_Model $calendar, $notification )
	{
		$return = array();
		return $return;
	}

	public function header( SH4_Calendars_Model $calendar, $notification )
	{
		$out = $notification->getTitle();
		return $out;
	}

	public function breadcrumb( SH4_Calendars_Model $calendar )
	{
		$calendarId = $calendar->getId();
		$calendarTitle = $this->calendarsPresenter->presentTitle( $calendar );

		$return = array();
		$return['admin'] = array( 'admin', '__Administration__' );
		$return['calendars'] = array( 'admin/calendars', '__Calendars__' );
		$return['calendars/edit'] = array( 'admin/calendars/' . $calendarId, $calendarTitle );
		$return['calendars/notifications'] = array( 'admin/notifications/' . $calendarId, '__Notifications__' );

		return $return;
	}
}