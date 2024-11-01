<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
#[AllowDynamicProperties]
class SH4_Calendars_Html_Admin_Controller_ShiftTypes
{
	public function __construct(
		HC3_Hooks $hooks,
		HC3_Post $post,

		SH4_ShiftTypes_Query $shiftTypesQuery,
		SH4_Calendars_Query $calendarsQuery,

		SH4_App_Query $appQuery,
		SH4_App_Command $appCommand
		)
	{
		$this->post = $hooks->wrap( $post );

		$this->calendarsQuery = $hooks->wrap( $calendarsQuery );
		$this->shiftTypesQuery = $hooks->wrap( $shiftTypesQuery );

		$this->appQuery = $hooks->wrap( $appQuery );
		$this->appCommand = $hooks->wrap( $appCommand );
	}

	public function execute( $calendarId, $new = FALSE )
	{
		$calendar = $this->calendarsQuery->findById( $calendarId );

		$currentShiftTypes = $this->appQuery->findShiftTypesForCalendar( $calendar );
		$currentShiftTypesIds = array_keys( $currentShiftTypes );

		$shiftTypesIds = $this->post->get('shifttype');
		if( ! $shiftTypesIds ){
			$shiftTypesIds = array();
		}

		$toAddIds = array_diff( $shiftTypesIds, $currentShiftTypesIds );
		$toRemoveIds = array_diff( $currentShiftTypesIds, $shiftTypesIds );

		if( $toAddIds ){
			$toAdd = $this->shiftTypesQuery->findManyById( $toAddIds );
			foreach( $toAdd as $shiftType ){
				$this->appCommand->addShiftTypeToCalendar( $shiftType, $calendar );
			}
		}

		if( $toRemoveIds ){
			$toRemove = $this->shiftTypesQuery->findManyById( $toRemoveIds );
			foreach( $toRemove as $shiftType ){
				$this->appCommand->removeShiftTypeFromCalendar( $shiftType, $calendar );
			}
		}

		if( $new ){
			$to = 'admin/calendars/' . $calendarId . '/prm';
		}
		else {
			$to = 'admin/calendars';
		}

		$return = array( $to, array('__Calendar Updated__') );

		return $return;
	}
}