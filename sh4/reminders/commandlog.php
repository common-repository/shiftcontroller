<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
interface SH4_Reminders_CommandLog_
{
	public function create( SH4_Reminders_ModelLog $model );
}

class SH4_Reminders_CommandLog implements SH4_Reminders_CommandLog_
{
	public $t, $crud;

	public function __construct(
		HC3_Time $t,
		HC3_Hooks $hooks,
		HC3_CrudFactory $crudFactory
		)
	{
		$this->t = $t;
		$crudFactory = $hooks->wrap( $crudFactory );
		$this->crud = $hooks->wrap( $crudFactory->make('reminderlog') );
	}

	public function create( SH4_Reminders_ModelLog $model )
	{
		$errors = array();

		if( $errors ){
			throw new HC3_ExceptionArray( $errors );
		}

		$sentOn = isset( $model->sent_on ) ? $model->sent_on : $this->t->setNow()->formatDateTimeDb();
		$array = array(
			'range'			=> $model->range,
			'date'			=> $model->date,
			'employee_id'	=> $model->employee_id,
			'sent_on'		=> $sentOn,
			);

		$ret = $this->crud->create( $array );

		$ret = $ret['id'];
		return $ret;
	}

	public function delete( SH4_Reminders_ModelLog $model )
	{
		$id = $model->id;
		return $this->crud->delete( $id );
	}

	public function deleteAll()
	{
		return $this->crud->deleteAll();
	}
}