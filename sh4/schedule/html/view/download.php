<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
#[AllowDynamicProperties]
class SH4_Schedule_Html_View_Download
{
	public function __construct( 
		HC3_Hooks $hooks,

		HC3_Ui $ui,
		HC3_Request $request,
		HC3_Time $t,
		HC3_Translate $translate,
		HC3_Settings $settings,

		SH4_Shifts_Presenter $shiftsPresenter,

		SH4_Calendars_Presenter $calendarsPresenter,
		SH4_Shifts_View_Widget $widget,
		SH4_Schedule_Html_View_Common $common
		)
	{
		$this->ui = $ui;
		$this->t = $t;
		$this->request = $request;
		$this->translate = $translate;

		$this->calendarsPresenter = $hooks->wrap( $calendarsPresenter );
		$this->shiftsPresenter = $hooks->wrap( $shiftsPresenter );

		$this->widget = $hooks->wrap( $widget );
		$this->common = $hooks->wrap( $common );
		$this->settings = $hooks->wrap($settings);

		$this->self = $hooks->wrap( $this );
	}

	public function render()
	{
		$params = $this->request->getParams();
		$startDate = $params['start'];

		$shifts = $this->common->getShifts();

		$iknow = array();
		$hori = TRUE;

		$allEmployees = $this->common->findAllEmployees();
		$allCalendars = $this->common->findAllCalendars();

		$session = HC3_Session::instance();
		$separator = $session->getUserdata( 'pref_csv_separator' );
		
		if( ! $separator ){
			$separator = $this->settings->get( 'pref_csv_separator' );
		}
		if( ! $separator ){
			$separator = ',';
		}

		$shiftsOut = array();
		$header = array();

		foreach( $shifts as $shift ){
			$thisOut = $this->shiftsPresenter->export( $shift );

			$thisHeader = array_keys( $thisOut );
			foreach( $thisHeader as $th ){
				if( ! isset($header[$th]) ){
					$header[$th] = $th;
				}
			}

			$shiftsOut[] = $thisOut;
		}

		$keys = array_keys( $header );
		$out = array();
		reset( $shiftsOut );
		foreach( $shiftsOut as $shiftOut ){
			$thisOut = array();

			reset( $keys );
			foreach( $keys as $k ){
				if( isset($shiftOut[$k]) ){
					$thisOut[$k] = $this->translate->translate( $shiftOut[$k] );
				}
				else {
					$thisOut[$k] = NULL;
				}
			}

			$thisOut = HC3_Functions::buildCsv( $thisOut, $separator );
			$out[] = $thisOut;
		}

		if( $out ){
			$header = HC3_Functions::buildCsv( $header, $separator );
			$header = array_unshift( $out, $header );
		}

		$out = join("\n", $out);

		$fileName = 'export';
		$fileName .= '-' . date('Y-m-d_H-i') . '.csv';

		HC3_Functions::pushDownload( $fileName, $out );
		exit;
	}
}