<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class SH4_Employees_Migration
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
		$currentVersion = $this->migrationService->getVersion( 'employees' );

		if( $currentVersion < 1 ){
			$this->version1();
			$this->migrationService->saveVersion( 'employees', 1 );
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
					'description' => array(
						'type'		=> 'TEXT',
						'null'		=> TRUE,
						),
					'show_order' => array(
						'type' => 'INT',
						'null' => FALSE,
						'default' => 0,
						),
					'status' => array(
						'type' => 'VARCHAR(16)',
						'null' => FALSE,
						'default'	=> 'active',
						),
					'user_id' => array(
						'type' => 'INT',
						'null' => TRUE,
						),
					)
				);
			$this->dbForge->add_key('id', TRUE);
			$this->dbForge->create_table('employees');
		}
	}
}