<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class SH4_Calendars_Migration
{
	public $self, $dbForge, $migrationService, $crudFactory, $calendarPermissions, $calendarsQuery, $hooks;

	public function __construct(
		HC3_Hooks $hooks,
		HC3_CrudFactory $crudFactory,
		HC3_MigrationService $migrationService,
		SH4_Calendars_Permissions	$calendarPermissions,
		SH4_Calendars_Query $calendarsQuery,
		HC3_Database_DbForge $dbForge = NULL
		)
	{
		$this->dbForge = $dbForge;
		$this->migrationService = $migrationService;
		$this->crudFactory = $crudFactory;
		$this->calendarPermissions = $hooks->wrap( $calendarPermissions );
		$this->calendarsQuery = $hooks->wrap( $calendarsQuery );

		$this->hooks = $hooks;
	}

	public function up()
	{
		$currentVersion = $this->migrationService->getVersion( 'calendars' );

		if( $currentVersion < 1 ){
			$this->version1();
			$this->migrationService->saveVersion( 'calendars', 1 );
		}

		if( $currentVersion < 2 ){
			$this->version2();
			$this->migrationService->saveVersion( 'calendars', 2 );
		}

		if( $currentVersion < 3 ){
			$this->version3();
			$this->migrationService->saveVersion( 'calendars', 3 );
		}

		if( $currentVersion < 4 ){
			$this->version4();
			$this->migrationService->saveVersion( 'calendars', 4 );
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
					'color' => array(
						'type'		=> 'VARCHAR(8)',
						'null'		=> TRUE,
						),
					)
				);
			$this->dbForge->add_key('id', TRUE);
			$this->dbForge->create_table('calendars');
		}
	}

	public function version2()
	{
		if( $this->dbForge ){
			if( ! $this->dbForge->field_exists('is_timeoff', 'calendars') ){
				$this->dbForge->add_column(
					'calendars',
					array(
						'is_timeoff' => array(
							'type'		=> 'TINYINT',
							'default'	=> 0
							),
						)
					);
			}
		}
	}

	public function version3()
	{
		if( $this->dbForge ){
			if( ! $this->dbForge->field_exists('calendar_type', 'calendars') ){
				$this->dbForge->add_column(
					'calendars',
					array(
						'calendar_type' => array(
							'type' => 'VARCHAR(16)',
							'null' => FALSE,
							'default'	=> 'shift',
							),
						)
					);
			}
		}

		$crud = $this->hooks->wrap( $this->crudFactory->make('calendar') );

		$allCalendars = $crud->read();
		foreach( $allCalendars as $calendar ){
			if( array_key_exists('is_timeoff', $calendar) && $calendar['is_timeoff'] ){
				$array = array(
					'calendar_type'	=> SH4_Calendars_Model::TYPE_TIMEOFF
					);
				$id = $calendar['id'];
				$crud->update( $id, $array );
			}
		}

		if( $this->dbForge ){
			if( $this->dbForge->field_exists('is_timeoff', 'calendars') ){
				$this->dbForge->drop_column(
					'calendars',
					'is_timeoff'
					);
			}
		}
	}

	public function version4()
	{
		// new permissions:
		// 'employee_edit_own_publish', 'employee_edit_own_draft', 'employee_delete_own_publish', employee_delete_own_draft
		// init their settings as 'employee_create_own_publish' / 'employee_create_own_draft'

		$calendars = $this->calendarsQuery->findAll();
		foreach( $calendars as $calendar ){
			$v = $this->calendarPermissions->get( $calendar, 'employee_create_own_publish' );
			$this->calendarPermissions->set( $calendar, 'employee_edit_own_publish', $v );
			$this->calendarPermissions->set( $calendar, 'employee_delete_own_publish', $v );

			$v = $this->calendarPermissions->get( $calendar, 'employee_create_own_draft' );
			$this->calendarPermissions->set( $calendar, 'employee_edit_own_draft', $v );
			$this->calendarPermissions->set( $calendar, 'employee_delete_own_draft', $v );
		}
	}
}