<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class SH4_Conf_Migration
{
	public $dbForge;
	public $migrationService;

	public function __construct(
		HC3_Hooks $hooks,
		HC3_MigrationService $migrationService,
		HC3_Database_DbForge $dbForge = null
		)
	{
		$this->dbForge = $dbForge;
		$this->migrationService = $migrationService;
	}

	public function up()
	{
		$currentVersion = $this->migrationService->getVersion( 'conf' );

		if( $currentVersion < 1 ){
			$this->version1();
			$this->migrationService->saveVersion( 'conf', 1 );
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
					'name' => array(
						'type' => 'VARCHAR(255)',
						'null' => FALSE,
						),
					'value' => array(
						'type'		=> 'TEXT',
						'null'		=> TRUE,
						),
					)
				);
			$this->dbForge->add_key('id', TRUE);
			$this->dbForge->create_table('conf');
		}
	}
}