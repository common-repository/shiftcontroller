<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
define( 'SH4_NEW_OPEN_SEPARATOR', '-' );

class SH4_New_Boot
{
	public function __construct(
		HC3_Router $router,
		HC3_Acl $acl
	)
	{
		$router
			->register( 'get:new', array('SH4_New_View_Dispatcher', 'render') )

			->register( 'get:new/shift', array('SH4_New_View_Calendar', 'renderShift') )
			->register( 'get:new/timeoff', array('SH4_New_View_Calendar', 'renderTimeoff') )
			->register( 'get:new/availability', array('SH4_New_View_Calendar', 'renderAvailability') )

			->register( 'get:new/calendar', array('SH4_New_View_Calendar', 'render') )

			->register( 'get:new/date', array('SH4_New_View_Date', 'render') )
			->register( 'get:ajax/new/date', array('SH4_New_View_Date', 'ajaxRender') )

			->register( 'get:new/employee', array('SH4_New_View_Employee', 'render') )
			->register( 'get:new/confirm', array('SH4_New_View_Confirm', 'render') )
			;

		$acl
			// ->register( 'get:new', array('SH4_New_Acl', 'checkNew') )

			// ->register( 'get:new/shift', array('SH4_New_Acl', 'checkNewShift') )
			// ->register( 'get:new/timeoff', array('SH4_New_Acl', 'checkNewTimeoff') )
			// ->register( 'get:new/availability', array('SH4_New_Acl', 'checkNewAvailability') )

			->register( 'get:new', array('SH4_New_Acl', 'quickCheckNew') )
			->register( 'get:new/shift', array('SH4_New_Acl', 'quickCheckNew') )
			->register( 'get:new/timeoff', array('SH4_New_Acl', 'quickCheckNew') )
			;

		$router
			->register( 'post:new/customtime/{calendar}', array('SH4_New_Controller_CustomTime', 'execute') )
			->register( 'post:new/employee', array('SH4_New_Controller_Employee', 'execute') )

			->register( 'post:new/{calendar}/{type}/{date}/{employee}/draft', array('SH4_New_Controller_Confirm', 'executeDraft') )
			->register( 'post:new/{calendar}/{type}/{date}/{employee}/publish', array('SH4_New_Controller_Confirm', 'executePublish') )
			;

		$acl
			->register( 'post:new/{calendar}/{type}/{employee}/{date}', array('SH4_New_Acl', 'checkCreate') )
			->register( 'post:new/{calendar}/{type}/{employee}/{date}/draft', array('SH4_New_Acl', 'checkDraft') )
			->register( 'post:new/{calendar}/{type}/{employee}/{date}/publish', array('SH4_New_Acl', 'checkPublish') )
			;
	}
}