<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
interface SH4_Calendars_Command_
{
	public function archive( SH4_Calendars_Model $model );
	public function restore( SH4_Calendars_Model $model );
	public function delete( SH4_Calendars_Model $model );
	public function deleteAll();
	public function changeTitle( SH4_Calendars_Model $model, $title );
	public function changeDescription( SH4_Calendars_Model $model, $description );
	public function changeColor( SH4_Calendars_Model $model, $color );
	public function create( $title, $color, $description = NULL, $isTimeoff = 0 );
	public function changeType( SH4_Calendars_Model $model, $type );
	public function changeSortOrder( SH4_Calendars_Model $model, $showOrder );
}

class SH4_Calendars_Command implements SH4_Calendars_Command_
{
	public $lastCreatedId = null;
	public $self, $crud, $shiftsQuery, $shiftsCommand;

	public function __construct(
		HC3_Hooks $hooks,
		HC3_CrudFactory $crudFactory,
		SH4_Shifts_Command $shiftsCommand,
		SH4_Shifts_Query $shiftsQuery
		)
	{
		$this->crud = $hooks->wrap( $crudFactory->make('calendar') );
		$this->shiftsQuery = $hooks->wrap( $shiftsQuery );
		$this->shiftsCommand = $hooks->wrap( $shiftsCommand );
	}

	public function getLastCreatedId()
	{
		return $this->lastCreatedId;
	}

	public function create( $title, $color, $description = NULL, $type = NULL )
	{
		$errors = array();

	// title is set
		if( ! strlen($title) ){
			$errors['title'] = '__Required Field__';
		}

	// duplicated titles
		if( ! isset($errors['title']) ){
			$args = array();
			$args[] = array( 'title', '=', $title );
			$already = $this->crud->count( $args );
			if( $already ){
				$msg = '__This value is already used__';
				$msg .= ': ' . strip_tags( $title );
				$errors['title'] = $msg;
			}
		}

		if( $errors ){
			throw new HC3_ExceptionArray( $errors );
		}

		if( ! $color ){
			$color = '#cbe86b';
		}

		$type = $type ? $type : SH4_Calendars_Model::TYPE_SHIFT;

		$array = array(
			'status'	=> SH4_Calendars_Model::STATUS_ACTIVE,
			'title'		=> $title,
			'color'		=> $color,
			'description'	=> $description,
			'calendar_type'	=> $type
			);

		$return = $this->crud->create( $array );
		$return = $return['id'];

		$this->lastCreatedId = $return;

		return $return;
	}

	public function changeSortOrder( SH4_Calendars_Model $model, $showOrder )
	{
		$id = $model->getId();
		if( ! $id ){
			return;
		}

		$errors = array();

		if( $errors ){
			throw new HC3_ExceptionArray( $errors );
		}

		if( ! strlen($showOrder) ){
			$showOrder = 1;
		}

		$array = array(
			'show_order'	=> $showOrder,
			);

		return $this->crud->update( $id, $array );
	}

	public function archive( SH4_Calendars_Model $model )
	{
		$id = $model->getId();
		if( ! $id ){
			return;
		}

		$array = array(
			'status'	=> SH4_Calendars_Model::STATUS_ARCHIVE
			);

		return $this->crud->update( $id, $array );
	}

	public function changeTitle( SH4_Calendars_Model $model, $title )
	{
		$id = $model->getId();
		if( ! $id ){
			return;
		}

		$errors = array();

	// title is set
		if( ! strlen($title) ){
			$errors['title'] = '__Required Field__';
		}

	// duplicated titles
		if( ! isset($errors['title']) ){
			$args = array();
			$args[] = array( 'title', '=', $title );
			$args[] = array( 'id', '<>', $id );
			$already = $this->crud->count( $args );
			if( $already ){
				$msg = '__This value is already used__';
				$msg .= ': ' . strip_tags( $title );
				$errors['title'] = $msg;
			}
		}

		if( $errors ){
			throw new HC3_ExceptionArray( $errors );
		}

		$array = array(
			'title'	=> $title
			);

		return $this->crud->update( $id, $array );
	}

	public function changeDescription( SH4_Calendars_Model $model, $description )
	{
		$id = $model->getId();
		if( ! $id ){
			return;
		}

		$array = array(
			'description'	=> $description
			);

		return $this->crud->update( $id, $array );
	}

	public function changeType( SH4_Calendars_Model $model, $type )
	{
		$id = $model->getId();
		if( ! $id ){
			return;
		}

		if( ! $type ){
			return;
		}

		$array = array(
			'calendar_type'	=> $type
			);

		return $this->crud->update( $id, $array );
	}

	public function changeColor( SH4_Calendars_Model $model, $color )
	{
		$id = $model->getId();
		if( ! $id ){
			return;
		}

		$array = array(
			'color'	=> $color
			);

		return $this->crud->update( $id, $array );
	}

	public function restore( SH4_Calendars_Model $model )
	{
		$id = $model->getId();
		if( ! $id ){
			return;
		}

		$array = array(
			'status'	=> SH4_Calendars_Model::STATUS_ACTIVE
			);

		return $this->crud->update( $id, $array );
	}

	public function delete( SH4_Calendars_Model $model )
	{
		$id = $model->getId();
		if( ! $id ){
			return;
		}

	// shifts
		$shifts = $this->shiftsQuery->findAllByCalendar( $model );
		foreach( $shifts as $shift ){
			$this->shiftsCommand->delete( $shift );
		}

		return $this->crud->delete( $id );
	}

	public function deleteAll()
	{
		return $this->crud->deleteAll();
	}
}