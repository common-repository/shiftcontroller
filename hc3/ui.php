<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
interface HC3_Ui_
{
	public function helperActionsFromArray( array $menu, $hidden = FALSE );

	public function makeBlock( $content = NULL );
	public function makeBlockInline( $content = NULL );
	public function makeLongText( $content, $length = 40 );
	public function makePager( $toSlug, $totalCount, $perPage, $currentPage = 1 );
	public function makeSpan( $content = NULL );
	public function makeList( array $items = array() );
	public function makeListInline( array $items = array() );
	public function makeGrid( array $items = array(), $itemWidth = NULL );
	public function makeH( $level, $content );
	public function makeTable( $header = NULL, array $rows = array(), $striped = TRUE );
	public function makeAhref( $to, $label = NULL );
	public function makeCollapse( $label, $content, $expand = FALSE );
	public function makeCollapseCheckbox( $name, $label, $content, $expand = FALSE );
	public function makeLabelled( $label, $content, $labelFor = NULL );
	public function makeLabelledInline( $label, $content, $labelFor = NULL );
	public function makeLabelledHori( $label, $content, $labelFor = NULL );

	public function makeHeadered( $header, $content );

	public function makeNumber( $number, $digits = 2 );
	public function makePercent( $number, $digits = 0 );

	public function makeForm( $action, $content = NULL );
	public function makeInputText( $name, $label = NULL, $value = NULL );
	public function makeInputPassword( $name, $label = NULL );
	public function makeInputTextarea( $name, $label = NULL, $value = NULL );
	public function makeInputRichTextarea( $name, $label = NULL, $value = NULL );
	public function makeInputCheckbox( $name, $label = NULL, $value = NULL, $checked = FALSE );
	public function makeInputRadio( $name, $label = NULL, $value = NULL, $checked = FALSE );
	public function makeInputHidden( $name, $value = NULL );
	public function makeInputColorpicker( $name, $label, $value = NULL );
	public function makeInputDatepicker( $name, $label = NULL, $value = NULL );
	public function makeInputTime( $name, $label = NULL, $value = NULL );
	public function makeInputTimerange( $name, $label, $value = array() );
	public function makeInputSelect( $name, $label, $options = array(), $value = NULL );
	public function makeInputRadioSet( $name, $label, $options = array(), $value = NULL );
	public function makeInputCheckboxSet( $name, $label = NULL, $options = array(), $value = array() );
	public function makeInputSubmit( $label, $alt = NULL );
	public function makeInputButton( $label, $alt = NULL );
}

class HC3_Ui implements HC3_Ui_
{
	public $t, $settings, $request, $enqueuer;

	public function __construct(
		HC3_Request $request,
		HC3_Enqueuer $enqueuer,
		HC3_Time $t,
		HC3_Settings $settings
	)
	{
		$this->t = $t;
		$this->settings = $settings;
		$this->request = $request;
		$this->enqueuer = $enqueuer;
	}

	public function helperActionsFromArray( array $menu, $hidden = FALSE )
	{
		$return = array();

		foreach( $menu as $k => $item ){
		// form
			if( count($item) >= 3 ){
				$href = $item[0];
				$formContent = $item[1];
				$btnLabel = $item[2];

				$confirm = ( isset($item[3]) && $item[3] ) ? true : false;

				if( ! $hidden ){
					$btn = $this->makeInputSubmit($btnLabel)
						->tag('nice-link')
						// ->tag('secondary')
						// ->tag('align', 'left')
						;

					if( $confirm ){
						$btn->addAttr( 'onclick', ' return confirm(\'' . $btnLabel . '?\')' );
					}

					$formContent = $formContent ? $this->makeListInline( array($formContent, $btn) ) : $btn;
				}

				$item = $this->makeForm( $href, $formContent );
				if( ! $item ){
					continue;
				}
			}
		// link
			else {
				// if( $hidden ){
					// continue;
				// }
				list( $href, $btnLabel ) = $item;

				if( is_array($href) ){
					$finalHref = str_replace( '--ID--', $href[1], $href[0] );
				}
				else {
					$finalHref = $href;
				}

				$item = $this->makeAhref( $finalHref, $btnLabel );
				if( ! $item ){
					continue;
				}

			// for bulk actions
				if( $hidden ){
					if( ! is_array($href) ){
						continue;
					}

					$item
						->addAttr('style', 'display:none;')
						->addAttr('data-href', $href[0])
						->addAttr('data-href-id', $href[1])
						;
				}
				else {
					$item
						->tag('nice-link')
						// ->tag('tab-link')
						->tag('align', 'left')
						;
				}
			}

			$item
				->addAttr( 'data-action-key', $k )
				->addAttr( 'data-action-label', $btnLabel )
				;

			$return[ $k ] = $item;
		}

		return $return;
	}

	public function makePercent( $number, $digits = 2, $arrowChange = FALSE )
	{
		$return = $number * 100;
		if( $arrowChange && ($number < 0) ){
			$return = - $return;
		}

		$return = number_format( $return, $digits );

		if( $arrowChange ){
			if( $number > 0 ){
				$return = '&uarr;' . $return;
			}
			else {
				$return = '&darr;' . $return;
			}
		}
		else {
			if( $number > 0 ){
				$return = '+' . $return;
			}
		}

		$return .= '%';

		$return = $this->makeSpan( $return );
		if( $number > 0 ){
			$return->tag('color', 'olive');
		}
		elseif( $number < 0 ) {
			$return->tag('color', 'maroon');
		}

		return $return;
	}

	public function makeNumber( $number, $digits = 2, $arrowChange = FALSE )
	{
		$return = $number;
		if( $arrowChange && ($number < 0) ){
			$return = - $return;
		}

		$return = number_format( $return, $digits );

		if( $arrowChange ){
			if( $number > 0 ){
				$return = '&uarr;' . $return;
			}
			else {
				$return = '&darr;' . $return;
			}
		}
		else {
			if( $number > 0 ){
				$return = '+' . $return;
			}
		}

		$return = $this->makeSpan( $return );
		if( $number > 0 ){
			$return->tag('color', 'olive');
		}
		elseif( $number < 0 ) {
			$return->tag('color', 'maroon');
		}

		return $return;
	}

	public function makePager( $toSlug, $totalCount, $perPage, $currentPage = 1 )
	{
		$return = new HC3_Ui_Element_Pager( $this, $toSlug, $totalCount, $perPage, $currentPage );
		return $return;
	}

	public function makeElement( $el, $content = NULL )
	{
		$return = new HC3_Ui_Element_Element( $el, $content );
		return $return;
	}

	public function makeLongText( $content = '', $length = 40 )
	{
		if( null === $content ) $content = '';
		$contentView = $content;

		$shortContentView = $contentView;
		if( strlen($contentView) > $length ){
			$shortContentView = substr($contentView, 0, $length) . '...';
		}

		if( strlen($contentView) > $length ){
			$contentView = $this->makeCollapse( $shortContentView, $contentView )
				->arrow( NULL )
				->hideToggle()
				;
		}

		$contentView = $this->makeBlock( $contentView )
			->tag('font-style', 'italic')
			->tag('font-size', 2)
			;

		return $contentView;
	}

	public function makeBlock( $content = NULL )
	{
		$return = $this->makeElement('div', $content);
		return $return;
	}

	public function makeSpan( $content = NULL )
	{
		$return = $this->makeElement('span', $content);
		return $return;
	}

	public function makeCollapse( $label, $content, $expand = FALSE )
	{
		$return = new HC3_Ui_Element_Collapse( $this, $label, $content, $expand );
		return $return;
	}

	public function makeCollapseCheckbox( $name, $label, $content, $expand = FALSE )
	{
		$return = new HC3_Ui_Element_CollapseCheckbox( $this, $name, $label, $content, $expand );
		return $return;
	}

	public function makeLabelled( $label, $content, $labelFor = NULL )
	{
		$return = new HC3_Ui_Element_Labelled( $this, $label, $content, $labelFor );
		return $return;
	}

	public function makeHeadered( $header, $content )
	{
		$header = $this->makeBlock( $header )
			->tag('font-size', 4)
			->tag('padding', 'b1')
			->tag('border', 'bottom')
			->tag('border-color', 'lightgray')
			;

		$out = $this->makeList( array($header, $content) )
			->gutter(2)
			;

		return $out;
	}

	public function makeLabelledInline( $label, $content, $labelFor = NULL )
	{
		$return = new HC3_Ui_Element_LabelledInline( $this, $label, $content, $labelFor );
		return $return;
	}

	public function makeLabelledHori( $label, $content, $labelFor = NULL )
	{
		$return = new HC3_Ui_Element_LabelledHori( $this, $label, $content, $labelFor );
		return $return;
	}

	public function makeBlockInline( $content = NULL )
	{
		$return = $this->makeElement('div', $content)
			->addAttr('class', 'hc-inline-block')
			;
		return $return;
	}

	public function makeList( array $items = array() )
	{
		$return = new HC3_Ui_Element_List( $this, $items );
		return $return;
	}

	public function makeCollection( array $items = array() )
	{
		$return = new HC3_Ui_Element_Collection( $items );
		return $return;
	}

	public function makeListInline( array $items = array() )
	{
		$return = new HC3_Ui_Element_ListInline( $this, $items );
		return $return;
	}

	public function makeGrid( array $items = array(), $itemWidth = NULL )
	{
		$return = new HC3_Ui_Element_Grid( $this, $items, $itemWidth );
		return $return;
	}

	public function makeH( $level, $content )
	{
		$return = new HC3_Ui_Element_H( $level, $content );
		return $return;
	}

	public function makeTable( $header = NULL, array $rows = array(), $striped = TRUE )
	{
		$return = new HC3_Ui_Element_Table( $this, $header, $rows );
		if( ! $striped ){
			$return->setStriped( FALSE );
		}
		return $return;
	}

	public function makeForm( $action, $content = NULL )
	{
		$return = new HC3_Ui_Element_Form( $this, $action, $content );
		return $return;
	}

	public function makeInputText( $name, $label = NULL, $value = NULL )
	{
		$return = new HC3_Ui_Element_Input_Text( $this, $name, $label, $value );
		return $return;
	}

	public function makeInputPassword( $name, $label = NULL )
	{
		$return = new HC3_Ui_Element_Input_Password( $this, $name, $label );
		return $return;
	}

	public function makeInputRichtextarea( $name, $label = NULL, $value = NULL )
	{
		$return = new HC3_Ui_Element_Input_RichTextarea( $this, $name, $label, $value );
		return $return;
	}

	public function makeInputTextarea( $name, $label = NULL, $value = NULL )
	{
		$return = new HC3_Ui_Element_Input_Textarea( $this, $name, $label, $value );
		return $return;
	}

	public function makeInputCheckbox( $name, $label = NULL, $value = NULL, $checked = FALSE )
	{
		$return = new HC3_Ui_Element_Input_Checkbox( $this, $name, $label, $value, $checked );
		return $return;
	}

	public function makeInputRadio( $name, $label = NULL, $value = NULL, $checked = FALSE )
	{
		$return = new HC3_Ui_Element_Input_Radio( $this, $name, $label, $value, $checked );
		return $return;
	}

	public function makeInputHidden( $name, $value = NULL )
	{
		$return = new HC3_Ui_Element_Input_Hidden( $this, $name, $value );
		return $return;
	}

	public function makeInputColorpicker( $name, $label, $value = NULL )
	{
		if( ! $this->request->isPrintView() ){
			$this->enqueuer
				->addScript('colorpicker',	'hc3/ui/element/input/colorpicker/assets/input.js?hcver=' . SH4_VERSION);
				;
		}
		$return = new HC3_Ui_Element_Input_Colorpicker( $this, $name, $label, $value );
		return $return;
	}

	public function makeInputDatepicker( $name, $label = NULL, $value = NULL )
	{
		if( ! $this->request->isPrintView() ){
			$this->enqueuer
				->addScript('datepicker',	'hc3/ui/element/input/datepicker/assets/input.js?hcver=' . SH4_VERSION)
				->addStyle('datepicker',	'hc3/ui/element/input/datepicker/assets/input.css?hcver=' . SH4_VERSION)
				;
		}

		if( $value === NULL ){
			$this->t->setNow();
			$value = $this->t->formatDateDb();
		}
		else {
			$this->t->setDateDb( $value );
		}

		$date_format = $this->t->formatToDatepicker();
		$value_formatted = $this->t->formatDate();
		$week_starts_on = $this->t->weekStartsOn;

		$return = new HC3_Ui_Element_Input_Datepicker(
			$this,
			$name,
			$label,
			$value,
			$value_formatted,
			$date_format,
			$week_starts_on
			);
		return $return;
	}

	public function makeInputSubmit( $label, $name = NULL, $alt = NULL )
	{
		$return = new HC3_Ui_Element_Input_Submit( $this, $label, $name, $alt );
		return $return;
	}

	public function makeInputButton( $label, $name = NULL, $alt = NULL )
	{
		$return = new HC3_Ui_Element_Input_Button( $label, $name, $alt );
		return $return;
	}

	public function makeAhref( $to, $label = NULL )
	{
		$return = new HC3_Ui_Element_Ahref( $to, $label );
		return $return;
	}

	public function makeInputSelect( $name, $label, $options = array(), $value = NULL )
	{
		$return = new HC3_Ui_Element_Input_Select( $this, $name, $label, $options, $value );
		return $return;
	}

	public function makeInputRadioSet( $name, $label, $options = array(), $value = NULL )
	{
		$return = new HC3_Ui_Element_Input_RadioSet( $this, $name, $label, $options, $value );
		return $return;
	}

	public function makeInputCheckboxSet( $name, $label = NULL, $options = array(), $value = array() )
	{
		$return = new HC3_Ui_Element_Input_CheckboxSet( $this, $name, $label, $options, $value );
		return $return;
	}

	public function makeInputTimeRange( $name, $label, $value = array(), $noLimit = FALSE )
	{
		$timeFormatOptions = array();

		$startWith = 0;
		$endWith = 24 * 60 * 60;

// $noLimit = TRUE;
		if( ! $noLimit ){
			$minTime = $this->settings->get('datetime_min_time');
			if( $minTime ){
				$startWith = $minTime;
			}
		}

		if( ! $noLimit ){
			$maxTime = $this->settings->get('datetime_max_time');
			if( $maxTime ){
				$endWith = $maxTime;
			}
		}

		if( $endWith < $startWith ){
			$endWith = $startWith;
		}

		$this->t->setDateDb( 20180102 );
		if( $startWith ){
			$this->t->modify( '+' . $startWith . ' seconds' );
		}

		$step = $this->settings->get('datetime_step');
		if( ! $step ){
			$step = 5 * 60;
		}

		$noOfSteps = ( $endWith - $startWith) / $step;
		for( $ii = 0; $ii <= $noOfSteps; $ii++ ){
			$sec = $startWith + $ii * $step;
			$timeFormatOptions[ $sec ] = $this->t->formatTime();
			$this->t->modify( '+' . $step . ' seconds' );
		}

		$return = new HC3_Ui_Element_Input_TimeRange( $this, $name, $label, $timeFormatOptions, $value );
		return $return;
	}

	public function makeInputTime( $name, $label = NULL, $value = 0, $noLimit = FALSE )
	{
		$timeFormatOptions = array();

		$startWith = 0;
		$endWith = 24 * 60 * 60;

		if( ! $noLimit ){
			$minTime = $this->settings->get('datetime_min_time');
			if( $minTime ){
				$startWith = $minTime;
			}
		}

		if( ! $noLimit ){
			$maxTime = $this->settings->get('datetime_max_time');
			if( $maxTime ){
				$endWith = $maxTime;
			}
		}

		if( $endWith < $startWith ){
			$endWith = $startWith;
		}

		$this->t->setDateDb( 20180102 );
		if( $startWith ){
			$this->t->modify( '+' . $startWith . ' seconds' );
		}

		$step = $this->settings->get('datetime_step');
		if( ! $step ){
			$step = 5 * 60;
		}

	// value to nearest step
		$value = ceil( $value / $step ) * $step;

		$noOfSteps = ( $endWith - $startWith) / $step;
		for( $ii = 0; $ii <= $noOfSteps; $ii++ ){
			$sec = $startWith + $ii * $step;
			$timeFormatOptions[ $sec ] = $this->t->formatTime();
			$this->t->modify( '+' . $step . ' seconds' );
		}

		$return = new HC3_Ui_Element_Input_Select( $this, $name, $label, $timeFormatOptions, $value );
		return $return;
	}
}
