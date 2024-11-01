<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
#[AllowDynamicProperties]
class SH4_Schedule_Html_View_MiscOptions
{
	public function __construct( 
		HC3_Hooks $hooks,
		HC3_Ui $ui,
		HC3_Request $request,

		SH4_Shifts_Duration $shiftsDuration,
		SH4_Schedule_Html_View_Common $common
		)
	{
		$this->self = $hooks->wrap( $this );
		$this->ui = $ui;
		$this->request = $request;

		$this->shiftsDuration = $hooks->wrap( $shiftsDuration );
		$this->common = $hooks->wrap( $common );
	}

	public function render()
	{
		$options = $this->self->options();

		if( ! array_key_exists(1, $options) ){
			$options[1] = NULL;
		}
		if( ! array_key_exists(2, $options) ){
			$options[2] = NULL;
		}
		if( ! array_key_exists(3, $options) ){
			$options[3] = NULL;
		}

		$out = $this->ui->makeGrid()
			->add( $options[1], 3, 12 )
			->add( $options[2], 7, 12 )
			->add( $options[3], 2, 12 )
			;

		return $out;
	}

	public function options()
	{
		$out = array();

		$shifts = $this->common->getShifts();
		$this->shiftsDuration->reset();

		$counted = 0;

		foreach( $shifts as $shift ){
			$shiftCalendar = $shift->getCalendar();
			// if( ! $shiftCalendar->isShift() ){
				// continue;
			// }
			$this->shiftsDuration->add( $shift );
			$counted++;
		}

		if( $counted ){
			$out[] = $this->common->renderReport( $this->shiftsDuration, FALSE );
		}

		$out = $this->ui->makeListInline( $out )
			->gutter(1)
			;

		$return = array();
		$return[1] = $out;

		return $return;
	}
}