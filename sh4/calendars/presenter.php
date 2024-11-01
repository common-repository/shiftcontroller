<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class SH4_Calendars_Presenter
{
	public $ui;

	public function __construct( HC3_Ui $ui )
	{
		$this->ui = $ui;
	}

	public function export( SH4_Calendars_Model $model )
	{
		$ret = array();

		$ret['id'] = $model->getId();
		$ret['title'] = $model->getTitle();
		$ret['type'] = $model->isTimeoff() ? 'timeoff' : 'shift';
		$ret['active'] = $model->isActive() ? 1 : 0;

		return $ret;
	}

	public function presentDescription( SH4_Calendars_Model $calendar )
	{
		$return = $calendar->getDescription();

		if( defined('WPINC') ){
			$return = do_shortcode( $return );
		}

		return $return;
	}

	public function presentTitle( SH4_Calendars_Model $calendar )
	{
		$ret = $calendar->getTitle();
		$ret = esc_html( $ret );

		if( $calendar->isTimeoff() ){
			$label = '(' . '__Time Off__' . ')';
			$ret = $this->ui->makeListInline( array($ret, $label) );
		}

		if( $calendar->isAvailability() ){
			$label = '(' . '__Availability__' . ')';
			$ret = $this->ui->makeListInline( array($ret, $label) );
		}

		$ret = $this->ui->makeBlockInline( $ret )
			->paddingX(1)
			->tag('bgcolor', $calendar->getColor() )
			->tag('color', 'white')
			;
		if( $calendar->isArchived() ){
			$ret
				->tag('font-style', 'line-through')
				;
		}

		return $ret;
	}
}