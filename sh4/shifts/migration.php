<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class SH4_Shifts_Migration
{
	public $dbForge, $migrationService;

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
		$currentVersion = $this->migrationService->getVersion( 'shifts' );

		if( $currentVersion < 1 ){
			$this->version1();
			$this->migrationService->saveVersion( 'shifts', 1 );
		}

		if( $currentVersion < 2 ){
			$this->version2();
			$this->migrationService->saveVersion( 'shifts', 2 );
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

					'starts_at' => array(
						'type' => 'BIGINT',
						'null' => FALSE,
						),
					'ends_at' => array(
						'type' => 'BIGINT',
						'null' => FALSE,
						),

					'break_starts_at' => array(
						'type' => 'BIGINT',
						'null' => TRUE,
						),
					'break_ends_at' => array(
						'type' => 'BIGINT',
						'null' => TRUE,
						),

					'status' => array(
						'type' => 'VARCHAR(16)',
						'null' => FALSE,
						'default'	=> 'active',
						),
					'calendar_id' => array(
						'type' => 'INT',
						'null' => FALSE,
						),
					'employee_id' => array(
						'type' => 'INT',
						'null' => FALSE,
						),
					)
				);
			$this->dbForge->add_key('id', TRUE);
			$this->dbForge->create_table('shifts');
		}
	}

	public function version2()
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
					'parent_id' => array(
						'type' => 'INT',
						'null' => FALSE,
						),
					'meta_key' => array(
						'type' => 'VARCHAR(255)',
						'null' => FALSE,
						'default' => 'text'
						),
					'meta_value' => array(
						'type' => 'TEXT',
						'null' => TRUE,
						),
					)
				);
			$this->dbForge->add_key('id', TRUE);
			$this->dbForge->create_table('shifts_meta');
		}
	}
}