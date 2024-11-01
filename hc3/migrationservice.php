<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class HC3_MigrationService
{
	public $settings;
	public $dbForge;

	public function __construct(
		HC3_Settings $settings,
		HC3_Database_DbForge $dbForge = NULL
		)
	{
		$this->settings = $settings;
		$this->dbForge = $dbForge;
	}

	public function getVersion( $moduleName )
	{
		$confName = 'migration_' . $moduleName;
		$return = $this->settings->get( $confName );
		if( ! $return ){
			$return = 0;
		}
		return $return;
	}

	public function saveVersion( $moduleName, $version )
	{
		$return = TRUE;
		$confName = 'migration_' . $moduleName;
		$this->settings->set( $confName, $version );
		return $return;
	}


	protected function _up( $versions, $moduleName )
	{
		$confName = 'migration_' . $moduleName;

		$installedVersion = $this->settings->get( $confName );
		if( ! $installedVersion ){
			$installedVersion = 0;
		}

		foreach( $versions as $v => $callable ){
			if( $v <= $installedVersion ){
				continue;
			}
			call_user_func( $callable );
			$this->settings->set( $confName, $v );
		}
	}
}