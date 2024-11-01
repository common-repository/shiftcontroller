<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class SH4_ShiftTypes_Migration
{
	public $dbForge;
	public $migrationService;

	public function __construct(
		HC3_Hooks $hooks,
		HC3_MigrationService $migrationService,

		HC3_Database_DbForge $dbForge = NULL
		)
	{
		$this->dbForge = $dbForge;
		$this->migrationService = $migrationService;
	}

	public function up()
	{
		$currentVersion = $this->migrationService->getVersion( 'shifttypes' );

		if( $currentVersion < 1 ){
			$this->version1();
			$this->migrationService->saveVersion( 'shifttypes', 1 );
		}
	}

	public function version1()
	{
		if( $this->dbForge ){
			$this->dbForge->add_field(
				array(
					'id' => array(
						'type' => 'INT',
						'null' => FALSE,
						'unsigned' => TRUE,
						'auto_increment' => TRUE
						),
					'title' => array(
						'type' => 'VARCHAR(255)',
						'null' => FALSE,
						),
					'starts_at' => array(
						'type' => 'INT',
						'null' => FALSE,
						),
					'ends_at' => array(
						'type' => 'INT',
						'null' => FALSE,
						),
					'break_starts_at' => array(
						'type' => 'INT',
						'null' => TRUE,
						),
					'break_ends_at' => array(
						'type' => 'INT',
						'null' => TRUE,
						),
					'range' => array(
						'type' => 'VARCHAR(16)',
						'null' => FALSE,
						'default' => SH4_ShiftTypes_Model::RANGE_HOURS,
						),
					)
				);
			$this->dbForge->add_key('id', TRUE);
			$this->dbForge->create_table('shifttypes');
		}
	}
}