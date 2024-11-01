<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
interface SH4_Notifications_ITemplate
{
	public function parse( $template, SH4_Shifts_Model $shift );
	public function getTags( SH4_Calendars_Model $calendar );
}

class SH4_Notifications_Template implements SH4_Notifications_ITemplate
{
	public $self, $shiftsPresenter;

	public function __construct(
		HC3_Hooks $hooks,
		SH4_Shifts_Presenter $shiftsPresenter
		)
	{
		$this->self = $hooks->wrap( $this );
		$this->shiftsPresenter = $hooks->wrap( $shiftsPresenter );
	}

	public function parse( $template, SH4_Shifts_Model $shift )
	{
		$calendar = $shift->getCalendar();

		$return = $template;

		$tags = $this->self->getTags( $calendar );
		$values = array();
		foreach( $tags as $tag ){
			$v = $this->self->getTagValue( $tag, $shift );
			$values[ $tag ] = $v;
		}

		reset( $values );
		foreach( $values as $k => $v ){
			$k = '{' . strtoupper($k) . '}';
			$return = str_replace( $k, $v, $return );
		}

		return $return;
	}

	public function getTagValue( $tag, SH4_Shifts_Model $shift )
	{
		$return = NULL;

		$calendar = $shift->getCalendar();
		$employee = $shift->getEmployee();

		switch( $tag ){
			case 'id':
				$return = $shift->getId();
				break;
			case 'datetime':
				$return = $this->shiftsPresenter->presentFullTime($shift);
				$breakView = $this->shiftsPresenter->presentBreak( $shift );
				if( $breakView ){
					$return  .= ' (' . '__Break__' . ' ' . $breakView . ')';
				}
				break;
			case 'calendar':
				$return = $calendar->getTitle();
				break;
			case 'employee':
				$return = $employee->getTitle();
				break;
		}

		return $return;
	}

	public function getTags( SH4_Calendars_Model $calendar )
	{
		$return = array();
		$return[] = 'calendar';
		$return[] = 'datetime';
		$return[] = 'employee';
		$return[] = 'id';

		return $return;
	}
}