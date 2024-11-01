<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
interface SH4_Shifts_Command_
{
	public function draft( SH4_Shifts_Model $model );
	public function publish( SH4_Shifts_Model $model );
	public function unpublish( SH4_Shifts_Model $model );
	public function delete( SH4_Shifts_Model $model );
	public function deleteAll();
	public function create( 
		SH4_Calendars_Model $calendar,
		$start,
		$end,
		SH4_Employees_Model $employee,
		$breakStart = null,
		$breakEnd = null,
		$status = SH4_Shifts_Model::STATUS_DRAFT
		);
	public function copy( 
		SH4_Shifts_Model $model,
		SH4_Calendars_Model $calendar,
		$start,
		$end,
		SH4_Employees_Model $employee,
		$breakStart = null,
		$breakEnd = null,
		$status = SH4_Shifts_Model::STATUS_DRAFT
		);

	public function changeEmployee( SH4_Shifts_Model $model, SH4_Employees_Model $employee );
	public function changeCalendar( SH4_Shifts_Model $model, SH4_Calendars_Model $calendar );
	public function reschedule( SH4_Shifts_Model $model, $newStart, $newEnd, $newStartBreak = NULL, $newEndBreak = NULL );
}

class SH4_Shifts_Command implements SH4_Shifts_Command_
{
	public $self, $shiftsQuery, $crud;

	public function __construct(
		SH4_Shifts_Query $shiftsQuery,
		HC3_Hooks $hooks,
		HC3_CrudFactory $crudFactory
		)
	{
		$this->shiftsQuery = $hooks->wrap( $shiftsQuery );
		$this->crud = $hooks->wrap( $crudFactory->make('shift') );
		$this->self = $hooks->wrap( $this );
	}

	public function create(
		SH4_Calendars_Model $calendar,
		$start,
		$end,
		SH4_Employees_Model $employee,
		$breakStart = null,
		$breakEnd = null,
		$status = SH4_Shifts_Model::STATUS_DRAFT
		)
	{
		$calendarId = $calendar->getId();
		$employeeId = $employee ? $employee->getId() : null;

		$array = array(
			'calendar_id'	=> $calendarId,
			'employee_id'	=> $employeeId,
			'starts_at'		=> $start,
			'ends_at'		=> $end,
			'break_starts_at'	=> $breakStart,
			'break_ends_at'		=> $breakEnd,
			'status'		=> $status,
			);

		$return = $this->crud->create( $array );
		$return = $return['id'];
		return $return;
	}

	public function copy(
		SH4_Shifts_Model $model,
		SH4_Calendars_Model $calendar,
		$start,
		$end,
		SH4_Employees_Model $employee,
		$breakStart = null,
		$breakEnd = null,
		$status = SH4_Shifts_Model::STATUS_DRAFT
		)
	{
		// $newId = $this->self->create( $calendar, $start, $end, $employee, $breakStart, $breakEnd, $status );
		$newId = $this->self->create( $calendar, $start, $end, $employee, $breakStart, $breakEnd );

		$wasPublished = $model->isPublished();

		$m2 = $this->shiftsQuery->findById( $newId );
		if( $wasPublished ){
			$this->self->publish( $m2 );
		}
		else {
			$this->self->draft( $m2 );
		}

		return $newId;
	}

	public function createNew( SH4_Shifts_Model $model )
	{
		$id = $model->getId();
		if( ! $id ){
			$id = $this->self->create( 
				$model->getCalendar(),
				$model->getStart(),
				$model->getEnd(),
				$model->getEmployee(),
				$model->getBreakStart(),
				$model->getBreakEnd()
				);
			$model->setId( $id );
		}

		$this->shiftsQuery->keep( $model );
		return $id;
	}

	public function publish( SH4_Shifts_Model $model )
	{
		$id = $model->getId();
		if( ! $id ){
			$id = $this->self->create( 
				$model->getCalendar(),
				$model->getStart(),
				$model->getEnd(),
				$model->getEmployee(),
				$model->getBreakStart(),
				$model->getBreakEnd()
				);
			$model->setId( $id );
		}

		$array = array(
			'status' => SH4_Shifts_Model::STATUS_PUBLISH
			);

		$this->crud->update( $id, $array );
		return $id;
	}

	public function draft( SH4_Shifts_Model $model )
	{
		$id = $model->getId();

		if( ! $id ){
			$id = $this->self->create( 
				$model->getCalendar(),
				$model->getStart(),
				$model->getEnd(),
				$model->getEmployee(),
				$model->getBreakStart(),
				$model->getBreakEnd()
				);
			$model->setId( $id );
		}

		$array = array(
			'status' => SH4_Shifts_Model::STATUS_DRAFT
			);

		$this->crud->update( $id, $array );
		return $id;
	}

	public function unpublish( SH4_Shifts_Model $model )
	{
		$id = $model->getId();
		if( ! $id ){
			return;
		}

		$array = array(
			'status' => SH4_Shifts_Model::STATUS_DRAFT
			);

		$this->crud->update( $id, $array );
		return $id;
	}

	public function delete( SH4_Shifts_Model $model )
	{
		$id = $model->getId();
		if( ! $id ){
			return;
		}
		return $this->crud->delete( $id );
	}

	public function deleteAll()
	{
		return $this->crud->deleteAll();
	}

	public function changeEmployee( SH4_Shifts_Model $model, SH4_Employees_Model $employee )
	{
		$id = $model->getId();
		if( ! $id ){
			return;
		}

	// unpublish to notify the current employee
		$wasPublished = $model->isPublished();

		$oldEmployee = $model->getEmployee();
		if( $oldEmployee && $oldEmployee->getId() ){
			$this->self->unpublish( $model );
		}

		$employeeId = $employee->getId();
		$array = array(
			'employee_id' => $employeeId,
			);

	/* quick tweak, later change it to something better */
		$refObject = new ReflectionObject( $model );

		$refProperty = $refObject->getProperty( 'employee' );
		$refProperty->setAccessible( TRUE );
		$refProperty->setValue( $model, $employee );

		$ret = $this->crud->update( $id, $array );

		// if( $employeeId ){
			if( $wasPublished ){
				$this->self->publish( $model );
			}
			else {
				$this->self->draft( $model );
			}
		// }

		return $ret;
	}

	public function changeCalendar( SH4_Shifts_Model $model, SH4_Calendars_Model $calendar )
	{
		$id = $model->getId();
		if( ! $id ){
			return;
		}

		$calendarId = $calendar->getId();
		$array = array(
			'calendar_id' => $calendarId,
			);

	/* quick tweak, later change it to something better */
		$refObject = new ReflectionObject( $model );

		$refProperty = $refObject->getProperty( 'calendar' );
		$refProperty->setAccessible( TRUE );
		$refProperty->setValue( $model, $calendar );

		return $this->crud->update( $id, $array );
	}

	public function reschedule( SH4_Shifts_Model $model, $newStart, $newEnd, $newStartBreak = NULL, $newEndBreak = NULL )
	{
		$id = $model->getId();
		if( ! $id ){
			return;
		}

		$array = array(
			'starts_at' => $newStart,
			'ends_at' => $newEnd,
			'break_starts_at' => NULL,
			'break_ends_at' => NULL
			);

		if( NULL !== $newStartBreak ){
			$array['break_starts_at'] = $newStartBreak;
		}
		if( NULL !== $newEndBreak ){
			$array['break_ends_at'] = $newEndBreak;
		}

	/* quick tweak, later change it to something better */
		$refObject = new ReflectionObject( $model );

		$refProperty = $refObject->getProperty( 'start' );
		$refProperty->setAccessible( TRUE );
		$refProperty->setValue( $model, $array['starts_at'] );

		$refProperty = $refObject->getProperty( 'end' );
		$refProperty->setAccessible( TRUE );
		$refProperty->setValue( $model, $array['ends_at'] );

		$refProperty = $refObject->getProperty( 'breakStart' );
		$refProperty->setAccessible( TRUE );
		$refProperty->setValue( $model, $array['break_starts_at'] );

		$refProperty = $refObject->getProperty( 'breakEnd' );
		$refProperty->setAccessible( TRUE );
		$refProperty->setValue( $model, $array['break_ends_at'] );

		return $this->crud->update( $id, $array );
	}
}