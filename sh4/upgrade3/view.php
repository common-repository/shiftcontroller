<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class SH4_Upgrade3_View
{
	public $self, $ui, $layout;

	public function __construct(
		HC3_Hooks $hooks,
		HC3_Ui $ui,
		HC3_Ui_Layout1 $layout
		)
	{
		$this->ui = $ui;
		$this->layout = $layout;

		$this->self = $hooks->wrap($this);
	}

	public function render()
	{
		global $wpdb;
		$prfx = $wpdb->prefix . 'shiftcontroller_v3_';

		$out = array();

	// find locations
		// $sql = "SELECT * FROM {$prfx}locations";
		// $results = $wpdb->get_results( $sql, ARRAY_A );

	// locations
		$sql = "SELECT COUNT(*) FROM {$prfx}locations";
		$count = $wpdb->get_var( $sql );
		$count = $this->ui->makeSpan($count)->tag('font-size', 4)->tag('font-style', 'bold');
		$out[] = $this->ui->makeListInline( array('__Calendars__', $count) );

	// employees
		$level = 1;
		$sql = "SELECT COUNT(*) FROM {$prfx}users WHERE level & $level";
		$count = $wpdb->get_var( $sql );
		$count = $this->ui->makeSpan($count)->tag('font-size', 4)->tag('font-style', 'bold');
		$out[] = $this->ui->makeListInline( array('__Employees__', $count) );

	// shift types
		// $sql = "SELECT DISTINCT CONCAT(start, '-', end) AS time_range FROM {$prfx}shifts";
		// $results = $wpdb->get_results( $sql, ARRAY_A );
		// $sql = "SELECT COUNT(DISTINCT CONCAT(start, '-', end)) FROM {$prfx}shifts";
		// $count = $wpdb->get_var( $sql );
		// $count = $this->ui->makeSpan($count)->tag('font-size', 4)->tag('font-style', 'bold');
		// $out[] = $this->ui->makeListInline( array('__Shift Types__', $count) );

	// shifts
		$sql = "SELECT COUNT(*) FROM {$prfx}shifts";
		$count = $wpdb->get_var( $sql );
		$count = $this->ui->makeSpan($count)->tag('font-size', 4)->tag('font-style', 'bold');
		$out[] = $this->ui->makeListInline( array('__Shifts__', $count) );

	// notice
		$out[] = '__Please note that your current data will be overwritten.__';

	// confirm
		$out[] = $this->ui->makeForm(
			'upgrade3',
			$this->ui->makeInputSubmit('__Proceed To Upgrade__')->tag('primary')
			);

		$out = $this->ui->makeList($out);

		$this->layout
			->setContent( $out )
			// ->setBreadcrumb( $this->self->breadcrumb() )
			->setHeader( $this->self->header() )
			// ->setMenu( $this->self->menu() )
			;

		$out = $this->layout->render();
		return $out;
	}

	public function breadcrumb()
	{
		$return = array();
		return $return;
	}

	public function header()
	{
		$out = '__Upgrade From 3.x__';
		return $out;
	}
}