<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
interface SH4_Employees_Command_
{
	public function archive( SH4_Employees_Model $model );
	public function restore( SH4_Employees_Model $model );
	public function delete( SH4_Employees_Model $model );
	public function deleteAll();
	public function changeTitle( SH4_Employees_Model $model, $title, $description = NULL );
	public function create( $title, $description = NULL );
	public function changeSortOrder( SH4_Employees_Model $model, $showOrder );
}

class SH4_Employees_Command implements SH4_Employees_Command_
{
	public $self, $crud, $shiftsQuery, $shiftsCommand;

	public function __construct(
		HC3_Hooks $hooks,
		HC3_CrudFactory $crudFactory,
		SH4_Shifts_Query $shiftsQuery,
		SH4_Shifts_Command $shiftsCommand
		)
	{
		$crudFactory = $hooks->wrap( $crudFactory );

		$this->crud = $hooks->wrap( $crudFactory->make('employee') );
		$this->shiftsQuery = $hooks->wrap( $shiftsQuery );
		$this->shiftsCommand = $hooks->wrap( $shiftsCommand );
	}

	public function create( $title, $description = NULL )
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

		$array = array(
			'status'	=> SH4_Employees_Model::STATUS_ACTIVE,
			'title'		=> $title,
			'description'	=> $description
			);

		$return = $this->crud->create( $array );

		$return = $return['id'];
		return $return;
	}

	public function archive( SH4_Employees_Model $model )
	{
		$id = $model->getId();
		if( ! $id ){
			return;
		}

		$array = array(
			'status' => SH4_Employees_Model::STATUS_ARCHIVE
			);

		return $this->crud->update( $id, $array );
	}

	public function changeTitle( SH4_Employees_Model $model, $title, $description = NULL )
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
			'title'	=> $title,
			'description'	=> $description
			);

		return $this->crud->update( $id, $array );
	}

	public function changeSortOrder( SH4_Employees_Model $model, $showOrder )
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

	public function restore( SH4_Employees_Model $model )
	{
		$id = $model->getId();
		if( ! $id ){
			return;
		}

		$array = array(
			'status' => SH4_Employees_Model::STATUS_ACTIVE
			);

		return $this->crud->update( $id, $array );
	}

	public function delete( SH4_Employees_Model $model )
	{
		$id = $model->getId();
		if( ! $id ){
			return;
		}

	// shifts
		$shifts = $this->shiftsQuery->findAllByEmployee( $model );
		foreach( $shifts as $shift ){
			if( ! is_object($shift) ){
				continue;
			}
			$this->shiftsCommand->delete( $shift );
		}

		return $this->crud->delete( $id );
	}

	public function deleteAll()
	{
		return $this->crud->deleteAll();
	}
}