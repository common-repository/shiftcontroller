<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
interface SH4_Shifts_View_ICommon
{
	public function breadcrumb( SH4_Shifts_Model $model );
	public function menu( SH4_Shifts_Model $model );
	public function icons( SH4_Shifts_Model $model, $iknow = array() );
}

#[AllowDynamicProperties]
class SH4_Shifts_View_Common implements SH4_Shifts_View_ICommon
{
	public function __construct(
		HC3_Hooks $hooks,
		HC3_Settings $settings,
		HC3_Auth $auth,
		HC3_Ui $ui,
		SH4_Shifts_Presenter $presenter,
		SH4_Shifts_Conflicts $conflicts
		)
	{
		$this->self = $hooks->wrap($this);
		$this->ui = $ui;
		$this->presenter = $hooks->wrap($presenter);
		$this->auth = $hooks->wrap( $auth );
		$this->conflicts = $hooks->wrap( $conflicts );
		$this->settings = $hooks->wrap( $settings );
	}

	public function breadcrumb( SH4_Shifts_Model $model )
	{
		$id = $model->getId();

		$return = array();

	// schedule link
		$scheduleLink = HC3_Session::instance()->getUserdata( 'scheduleLink' );
		if( ! $scheduleLink ){
			$scheduleLink = array( 'schedule', array() );
		}
		// $return['schedule'] = array( $scheduleLink, '__Schedule__' );

		$label = $this->presenter->presentTitle($model);
		$return['schedule/' . $id ] = array( 'shifts/' . $id , $label );
		// $return['schedule/' . $id ] = $label;

		return $return;
	}

	public function bulkMenu( array $models )
	{
		$ret = null;

		$menu = array();
		$skipMulti = array( 
			'employee_pickup',
			'employee_assign',
			// 'employee_pickup/reset',
			// 'employee_pickup/request'
		);

		foreach( $models as $m ){
			$fullMenu = $this->self->menu( $m );
			foreach( $fullMenu as $k0 => $thisMenu ){
				foreach( $thisMenu as $k1 => $actionArray ){
					$k = $k0 . '_' . $k1;
					if( (count($models) > 1) && (! in_array($k, $skipMulti)) ){
						$thisK = $k . ':' . $m->getId();
					}
					else {
						$thisK = $k;
					}
					$menu[$thisK] = $actionArray;
				}
			}
		}

		if( ! $menu ) return $ret;

		$id = $m->getId();

		$menu = $this->ui->helperActionsFromArray( $menu, true );
		$menu = $this->ui->makeCollection( $menu );

		$checkbox = $this->ui->makeInputCheckbox( 'id[]', null, $id, true );
		$checkbox = $this->ui->makeBlock( $checkbox )
			->addAttr('class', 'sh4-shift-checker')
			->addAttr('style', 'display: none;')
			;
		$ret = $this->ui->makeCollection( array($checkbox, $menu) );

		return $ret;
	}

	public function menu( SH4_Shifts_Model $model )
	{
		$id = $model->getId();

		$return = array();

		$conflicts = $this->conflicts->get( $model );
		if( $conflicts ){
			$label = '__View Conflicts__';
			$return['datetime']['conflicts'] = array( 'shifts/' . $id . '/conflicts', $label );
		}
		$return['datetime']['time'] = array( 'shifts/' . $id . '/time', '__Change Time__' );
		$return['datetime']['date'] = array( 'shifts/' . $id . '/date', '__Change Date__' );

		if( $model->isOpen() ){
			$return['employee']['assign'] = array( 
				array( 'shifts/--ID--/employee', $id ),
				'__Assign__'
			);
		}
		else {
			$return['employee']['change'] = array(
				array( 'shifts/--ID--/employee', $id ),
				'__Change Employee__'
			);
			$return['employee']['change2'] = array(
				array( 'shifts/--ID--/employeecopy', $id ),
				'__Copy To Another Employee__'
			);
			$return['employee']['unassign'] = array(
				'shifts/' . $id . '/employee/0',
				null,
				'__Unassign__'
			);
		}

		if( $model->isDraft() ){
			$confirm = $this->settings->get('shifts_confirm_publish') ? true : false;
			$return['status']['publish'] = array( 'shifts/' . $id . '/publish', NULL, '__Publish__', $confirm );
		}
		else {
			$noDraft = $this->settings->get('shifts_no_draft') ? TRUE : FALSE;
			if( ! $noDraft ){
				$return['status']['unpublish'] = array( 'shifts/' . $id . '/unpublish', NULL, '__Unpublish__' );
				// $return['status']['unpublish2'] = array( 'shifts/' . $id . '/unpublish','<input type="text"><button type="submit">unbub</button', '' );
			}
		}
		$return['status']['delete'] = array( 'shifts/' . $id . '/delete', NULL, '__Delete__' );

		return $return;
	}

	public function icons( SH4_Shifts_Model $model, $iknow = array() )
	{
		$return = array();
		$calendar = $model->getCalendar();

		if( $model->isDraft() ){
			if( ! $calendar->isAvailability() ){
				$sign = $this->ui->makeBlock('?')
					->tag('align', 'center')
					->addAttr('style', 'width: 1em;')
					->tag('border')
					// ->tag('border-color', 'gray' )
					// ->tag('bgcolor', 'silver')
					->tag('bgcolor', 'gray')
					->tag('color', 'white')
					->tag('muted', 1)
					->addAttr('title', '__Draft__')
					;
				if( ! isset($return['status']) ){
					$return['status'] = array();
				}
				$return['status'][] = $sign;
			}
		}

		if( ! in_array('conflicts', $iknow) ){
			$conflicts = array();
			$currentUser = $this->auth->getCurrentUser();
			$currentUserId = $currentUser->getId();
			if( $currentUserId ){
				$conflicts = $this->conflicts->get( $model );
			}

			if( $conflicts ){
				$sign = $this->ui->makeBlock('!')
					->tag('align', 'center')
					->addAttr('style', 'width: 1em;')
					->tag('border')
					->tag('border-color', 'red' )
					->tag('bgcolor', 'lightred')
					->tag('muted', 1)
					->addAttr('title', '__Conflicts__')
					;
				if( ! isset($return['datetime']) ){
					$return['datetime'] = array();
				}
				$return['datetime'][] = $sign;
			}
		}

		if( $model->isOpen() ){
			$sign = $this->ui->makeBlock('!')
				->tag('align', 'center')
				->addAttr('style', 'width: 1em;')
				->tag('border')
				// ->tag('border-color', 'orange' )
				// ->tag('bgcolor', 'yellow')
				->tag('bgcolor', 'orange')
				->tag('color', 'white')
				->tag('muted', 1)
				->addAttr('title', '__Open Shift__')
				;
			if( ! isset($return['employee']) ){
				$return['employee'] = array();
			}
			$return['employee'][] = $sign;
		}

		return $return;
	}
}