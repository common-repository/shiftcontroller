<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
#[AllowDynamicProperties]
class SH4_Calendars_Html_Admin_Controller_Index
{
	public function __construct(
		HC3_Hooks $hooks,
		SH4_Calendars_Query $query,
		SH4_Calendars_Command $command,
		HC3_Post $post
		)
	{
		$this->query = $hooks->wrap($query);
		$this->command = $hooks->wrap($command);
		$this->post = $hooks->wrap($post);
		$this->self = $hooks->wrap($this);
	}

	public function execute()
	{
		$action = $this->post->get('action');
		$ids = $this->post->get('id');

		if( ! is_array($ids) ){
			$ids = explode( ':', $ids );
		}

		$filter = $this->post->get('filter');
		if( $filter ){
			$calendarId = $this->post->get('calendar');
			return $this->self->executeFilter( $calendarId );
		}

		if( ('archive' == $action) && $ids ){
			$this->self->executeArchive( $ids );

			$msg = '__Calendar Archived__';
			if( count($ids) > 1 ){
				$msg .= ' (' . count($ids) . ')';
			}
		}

		if( ('restore' == $action) && $ids ){
			$this->self->executeRestore( $ids );

			$msg = '__Calendar Restored__';
			if( count($ids) > 1 ){
				$msg .= ' (' . count($ids) . ')';
			}
		}

		if( ('delete' == $action) && $ids ){
			$this->self->executeDelete( $ids );

			$msg = '__Calendar Deleted__';
			if( count($ids) > 1 ){
				$msg .= ' (' . count($ids) . ')';
			}
		}

		$return = array( 'admin/calendars', $msg );
		return $return;
	}

	public function executeFilter( $calendarId )
	{
		$params = array();
		$params['admin/calendar'] = $calendarId ? $calendarId : NULL;

		$return = array( array('admin/calendars', $params), NULL );
		return $return;
	}

	public function executeDelete( $ids )
	{
		if( ! is_array($ids) ){
			$ids = array($ids);
		}

		foreach( $ids as $id ){
			$model = $this->query->findById( $id );
			$this->command->delete( $model );
		}
	}

	public function executeArchive( $ids )
	{
		if( ! is_array($ids) ){
			$ids = array($ids);
		}

		foreach( $ids as $id ){
			$model = $this->query->findById( $id );
			$this->command->archive( $model );
		}
	}

	public function executeRestore( $ids )
	{
		if( ! is_array($ids) ){
			$ids = array($ids);
		}

		foreach( $ids as $id ){
			$model = $this->query->findById( $id );
			$this->command->restore( $model );
		}
	}
}