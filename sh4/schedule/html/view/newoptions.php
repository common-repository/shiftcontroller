<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
#[AllowDynamicProperties]
class SH4_Schedule_Html_View_NewOptions
{
	public function __construct( 
		SH4_New_Acl $newAcl,
		HC3_Hooks $hooks,
		HC3_Ui $ui,
		HC3_Request $request
		)
	{
		$this->self = $hooks->wrap( $this );
		$this->ui = $ui;
		$this->request = $request;
		$this->newAcl = $hooks->wrap( $newAcl );

		$this->allCombosShift = $this->newAcl->findAllCombosShift();
		$this->allCombosTimeoff = $this->newAcl->findAllCombosTimeoff();
	}

	public function render()
	{
		$out = NULL;

		$options = $this->self->options();
		if( ! $options ){
			return $out;
		}

		$out = $this->ui->makeListInline( $options )
			->gutter(2)
			;

		return $out;
	}

	public function options()
	{
		$out = array();

		$params = $this->request->getParams();

		$toParams = array();
		if( array_key_exists('employee', $params) && (count($params['employee']) == 1) ){
			$toParams['employee'] = $params['employee'][0];
		}

		if( $this->allCombosShift ){
			$label = '+' . ' ' . '__Shift__';
			$to = 'new/shift';
			$to = array( $to, $toParams );
			$newShiftLink = $this->ui->makeAhref( $to, $label )
				->tag('secondary')
				;
			$out[] = $newShiftLink;
		}

		if( $this->allCombosTimeoff ){
			$label = '+' . ' ' . '__Time Off__';
			$to = 'new/timeoff';
			$to = array( $to, $toParams );
			$newTimeoffLink = $this->ui->makeAhref( $to, $label )
				->tag('secondary')
				;
			$out[] = $newTimeoffLink;
		}

		// $label = '+' . ' ' . '__Availability__';
		// $to = 'new/availability';
		// $to = array( $to, $toParams );
		// $newAvailabilityLink = $this->ui->makeAhref( $to, $label )
			// ->tag('secondary')
			// ;
		// $out[] = $newAvailabilityLink;

		return $out;
	}
}