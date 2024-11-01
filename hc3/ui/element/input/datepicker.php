<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class HC3_Ui_Element_Input_Datepicker extends HC3_Ui_Abstract_Input
{
	protected $el = 'input';
	protected $uiType = 'input/datepicker';
	public $dateFormat, $valueFormatted, $weekStartsOn;

	public function __construct( $htmlFactory, $name, $label = NULL, $value = NULL, $valueFormatted = NULL, $dateFormat = NULL, $weekStartsOn = 0 )
	{
		parent::__construct( $htmlFactory, $name, $label, $value );

		$this->dateFormat = $dateFormat;
		$this->valueFormatted = $valueFormatted;
		$this->weekStartsOn = $weekStartsOn;
	}

	public function render()
	{
		static $jsShown = FALSE;

	/* hidden field to store our value */
		$hidden = $this->htmlFactory->makeInputHidden( $this->name(), $this->value );

	/* text field to display */
		$display_name = $this->name() . '_display';

		$text = $this->htmlFactory->makeInputText( $display_name, $this->label, $this->valueFormatted );

		$text
			->addAttr( 'class', 'hc-datepicker2' )
			->addAttr( 'readonly', 'true' )
			->addAttr( 'class', 'hc-xs-block' )
			->addAttr( 'data-date-format', $this->dateFormat )
			->addAttr( 'data-date-week-start', $this->weekStartsOn)
// ->addAttr( 'data-date-end-date', '25 Apr 2022' )
			;

		// $text = $this->app->make('/form/text')
			// ->addAttr('data-date-week-start', $t->weekStartsOn)
			// ;

		$js = NULL;

		if( ! $jsShown ){
			ob_start();
?>

<script language="JavaScript">
document.addEventListener('DOMContentLoaded', function()
{
	jQuery.fn.hc_datepicker2.dates['en'] = {
		days: ["__Sun__", "__Mon__", "__Tue__", "__Wed__", "__Thu__", "__Fri__", "__Sat__", "__Sun__"],
		daysShort: ["__Sun__", "__Mon__", "__Tue__", "__Wed__", "__Thu__", "__Fri__", "__Sat__", "__Sun__"],
		daysMin: ["__Sun__", "__Mon__", "__Tue__", "__Wed__", "__Thu__", "__Fri__", "__Sat__", "__Sun__"],
		months: ["__Jan__", "__Feb__", "__Mar__", "__Apr__", "__May__", "__Jun__", "__Jul__", "__Aug__", "__Sep__", "__Oct__", "__Nov__", "__Dec__"],
		monthsShort: ["__Jan__", "__Feb__", "__Mar__", "__Apr__", "__May__", "__Jun__", "__Jul__", "__Aug__", "__Sep__", "__Oct__", "__Nov__", "__Dec__"],
		today: "Today",
		clear: "Clear"
	};
});
</script>
<?php
			$js = ob_get_clean();
			$jsShown = TRUE;
		}

		$out = $this->htmlFactory->makeCollection( array($hidden, $text, $js) );
		return $out;
	}
}