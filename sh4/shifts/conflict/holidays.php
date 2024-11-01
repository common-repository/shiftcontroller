<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class SH4_Shifts_Conflict_Holidays implements SH4_Shifts_Conflict_IConflict
{
	protected $parent = null;
	public $ui;

	public function __construct(
		HC3_Ui $ui
		)
	{
		$this->ui = $ui;
	}

	public function check( SH4_Shifts_Model $shift )
	{
		$return = TRUE;
		return $return;

		$return = $this->parent ? $this->parent->check($shift) : array();

		// $return[] = $this;
		return $return;
	}

	public function render()
	{
		$label = '__Holidays__';

		$out = NULL;
		$out = $this->ui->makeLabelled( $label, $out );

		return $out;
	}
}