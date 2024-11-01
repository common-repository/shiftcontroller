<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class SH4_Upgrade3_Query
{
	protected $prfxStandalone = 'shf2_v3_';
	protected $prfxWp = 'shiftcontroller_v3_';
	public $dic;

	public function __construct(
		HC3_Dic $dic
	)
	{
		$this->dic = $dic;
	}

	public function hasVersion3()
	{
		$return = FALSE;

		if( defined('WPINC') ){
			global $wpdb;

			$prfx = $wpdb->prefix . $this->prfxWp;
			$testTable = $prfx . 'locations';

			if( $wpdb->get_var("SHOW TABLES LIKE '$testTable'") != $testTable ){
				$return = FALSE;
			}
			else {
				$return = TRUE;
			}
		}
		else {
			$db = $this->dic->make('HC3_Database');
			$prfx = $this->prfxStandalone;

			$sql = "SELECT * FROM {$prfx}locations";
			$oldLocations = $db->query( $sql );
			if( $oldLocations ){
				$return = TRUE;
			}
			else {
				$return = FALSE;
			}
		}

		return $return;
	}

	public function findOldData()
	{
		$oldLocations = $oldEmployees = $oldShifts = $oldUsers = array();

		if( defined('WPINC') ){
			global $wpdb;
			$prfx = $wpdb->prefix . $this->prfxWp;

			$sql = "SELECT * FROM {$prfx}locations";
			$oldLocations = $wpdb->get_results( $sql, ARRAY_A );

			$level = 1;
			$sql = "SELECT * FROM {$prfx}users WHERE level & $level";
			$oldEmployees = $wpdb->get_results( $sql, ARRAY_A );

			$sql = "SELECT * FROM {$prfx}shifts";
			$oldShifts = $wpdb->get_results( $sql, ARRAY_A );

			$importUsers = FALSE;
		}
		else {
			$db = $this->dic->make('HC3_Database');
			$prfx = $this->prfxStandalone;

			$sql = "SELECT * FROM {$prfx}locations";
			$oldLocations = $db->query( $sql );

			$level = 1;
			$sql = "SELECT * FROM {$prfx}users WHERE level & $level";
			$oldEmployees = $db->query( $sql );

			$sql = "SELECT * FROM {$prfx}shifts";
			$oldShifts = $db->query( $sql );

			$importUsers = TRUE;
		}

		$return = array( $oldLocations, $oldEmployees, $oldShifts, $importUsers );
		return $return;
	}
}