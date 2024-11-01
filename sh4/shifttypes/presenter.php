<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class SH4_ShiftTypes_Presenter
{
	public $settings, $ui, $t;

	public function __construct(
		HC3_Hooks $hooks,
		HC3_Settings $settings,
		HC3_Time $t,
		HC3_Ui $ui
		)
	{
		$this->settings = $hooks->wrap($settings);
		$this->ui = $ui;
		$this->t = $t;
	}

	public function presentTitleTime( SH4_ShiftTypes_Model $model )
	{
		if( $model->getId() ){
			$titleView = $this->presentTitle( $model );
			$timeView = $this->presentTime( $model );
			$timeView = $this->ui->makeSpan($timeView)
				->tag('font-size', 2)
				;
			$return = $this->ui->makeList( array($titleView, $timeView) )
				->gutter(0)
				;
		}
		else {
			$return = $this->presentTime( $model );
		}
		return $return;
	}

	public function presentTitle( SH4_ShiftTypes_Model $model )
	{
		$ret = $model->getTitle();
		$ret = esc_html( $ret );
		return $ret;
	}

	public function presentDuration( $duration )
	{
		$return = $duration;
		$return = HC3_Time::formatPeriodInHours( $return );
		return $return;
	}

	public function presentTime( SH4_ShiftTypes_Model $e, $start = NULL, $end = NULL )
	{
		$return = NULL;

		$noBreak = $this->settings->get( 'shifttypes_nobreak' );

		$start = (NULL === $start) ? $e->getStart() : $start;
		$end = (NULL === $end) ? $e->getEnd() : $end;
		$breakStart = $e->getBreakStart();
		$breakEnd = $e->getBreakEnd();

		$range = $e->getRange();

		switch( $range ){
			case SH4_ShiftTypes_Model::RANGE_DAYS:
				if( $start != $end ){
					$return = $start . ' - ' . $end . ' (' . '__Days__' . ')';
				}
				else {
					$return = $start . ' (' . '__Days__' . ')';
				}

				break;

			case SH4_ShiftTypes_Model::RANGE_HOURS:
				if( (NULL === $start) && (NULL === $end) ){
					$return = NULL;
					// $return = '__Custom Time__';
				}
				elseif( (0 == $start) && (24*60*60 == $end) ){
					$return = '__All Day__';
				}
				else {
					$this->t->setDateDb( 20180102 );
					$this->t->modify('+' . $start . ' seconds');
					$startView = $this->t->formatTime();

					$this->t->setDateDb( 20180102 );
					$this->t->modify('+' . $end . ' seconds');
					$endView = $this->t->formatTime();
					if( $end > 24 * 60 * 60 ){
						$endView = '&gt;' . $endView;
					}

					$return = $this->ui->makeListInline( array($startView, '-', $endView) )->gutter(1);

					if( ! $noBreak ){
						if( ! ((NULL === $breakStart) && (NULL === $breakEnd)) ){
							if( $breakStart OR $breakEnd ){
								$this->t->setDateDb( 20180102 );
								if( $breakStart ){
									$this->t->modify('+' . $breakStart . ' seconds');
								}
								$breakStartView = $this->t->formatTime();

								$this->t->setDateDb( 20180102 );
								if( $breakEnd ){
									$this->t->modify('+' . $breakEnd . ' seconds');
								}
								$breakEndView = $this->t->formatTime();

								$breakView = $this->ui->makeListInline( array('__Break__', $breakStartView, '-', $breakEndView) )
									->gutter(1)
									;
								$breakView = $this->ui->makeSpan( $breakView )
									->tag('font-size', 2)
									->tag('muted', 2)
									;
								$return = $this->ui->makeList( array($return, $breakView) )->gutter(0);
							}
						}
					}

					// $return = $startView . ' - ' . $endView;
				}
				break;
		}

		return $return;
	}
}