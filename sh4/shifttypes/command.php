<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
interface SH4_ShiftTypes_Command_
{
	public function changeTitle( SH4_ShiftTypes_Model $model, $title );
	public function changeTime( SH4_ShiftTypes_Model $model, $start, $end, $breakStart = NULL, $breakEnd = NULL );
	public function delete( SH4_ShiftTypes_Model $model );
	public function deleteAll();

	public function createHours( $title, $start, $end, $breakStart = NULL, $breakEnd = NULL );
	public function createDays( $title, $minDays, $maxDays );
}

class SH4_ShiftTypes_Command implements SH4_ShiftTypes_Command_
{
	public $crud, $query;

	public function __construct(
		HC3_Hooks $hooks,
		SH4_ShiftTypes_Query	$query,
		HC3_CrudFactory $crudFactory
		)
	{
		$this->crud = $hooks->wrap( $crudFactory->make('shifttype') );
		$this->query = $hooks->wrap( $query );
	}

	public function createHours( $title, $start, $end, $breakStart = NULL, $breakEnd = NULL )
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

	// time already exists
		if( ! isset($errors['time']) ){
			$args = array();
			$args[] = array( 'starts_at', '=', $start );
			$args[] = array( 'ends_at', '=', $end );
			$args[] = array( 'range', '=', 'hours' );

		// also check break start/end
			$matches = $this->query->read( $args );
			foreach( $matches as $e ){
				if( ( $breakStart === $e->getBreakStart() ) && ( $breakEnd === $e->getBreakEnd() ) ){
					$msg = '__This value is already used__';
					$errors['time'] = $msg;
					break;
				}
			}
		}

	// break
		if( ! isset($errors['break']) ){
			if( (NULL !== $breakStart) && (NULL !== $breakEnd) ){
				if( ($end > 24*60*60) && ($breakStart < $start) ){
					$breakStart = 24*60*60 + $breakStart;
					$breakEnd = 24*60*60 + $breakEnd;
				}

				if( ($breakStart >= $end) OR ($breakStart < $start) ){
					$msg = '__Lunch break should be within shift hours.__';
					$errors['break'] = $msg;
				}
				if( ($breakEnd > $end) OR ($breakEnd <= $start) ){
					$msg = '__Lunch break should be within shift hours.__';
					$errors['break'] = $msg;
				}
			}
		}

		if( $errors ){
			throw new HC3_ExceptionArray( $errors );
		}

		$array = array(
			'title'		=> $title,
			'starts_at'	=> $start,
			'ends_at'	=> $end,
			'break_starts_at'	=> $breakStart,
			'break_ends_at'		=> $breakEnd,
			'range'		=> SH4_ShiftTypes_Model::RANGE_HOURS,
			);

		$return = $this->crud->create( $array );
		$return = $return['id'];
		return $return;
	}

	public function createDays( $title, $minDays, $maxDays )
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

		if( ! isset($errors['end']) ){
			if( $maxDays < $minDays ){
				$msg = '__Max value should not be less than min value__';
				$errors['end'] = $msg;
			}
		}

		if( $errors ){
			throw new HC3_ExceptionArray( $errors );
		}

		$array = array(
			'title'		=> $title,
			'starts_at'	=> $minDays,
			'ends_at'	=> $maxDays,
			'range'		=> SH4_ShiftTypes_Model::RANGE_DAYS,
			);

		$return = $this->crud->create( $array );
		$return = $return['id'];
		return $return;
	}

	public function changeTitle( SH4_ShiftTypes_Model $model, $title )
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

	public function changeTime( SH4_ShiftTypes_Model $model, $start, $end, $breakStart = NULL, $breakEnd = NULL )
	{
		$id = $model->getId();
		if( ! $id ){
			return;
		}

		$errors = array();

	// time already exists
		if( ! isset($errors['time']) ){
			$args = array();
			$args[] = array( 'starts_at', '=', $start );
			$args[] = array( 'ends_at', '=', $end );
			$args[] = array( 'range', '=', 'hours' );
			$args[] = array( 'id', '<>', $id );

		// also check break start/end
			$matches = $this->query->read( $args );
			foreach( $matches as $e ){
				if( ( $breakStart === $e->getBreakStart() ) && ( $breakEnd === $e->getBreakEnd() ) ){
					$msg = '__This value is already used__';
					$errors['time'] = $msg;
					break;
				}
			}
		}

	// break
		if( ! isset($errors['break']) ){
			if( (NULL !== $breakStart) && (NULL !== $breakEnd) ){
				if( ($end > 24*60*60) && ($breakStart < $start) ){
					$breakStart = 24*60*60 + $breakStart;
					$breakEnd = 24*60*60 + $breakEnd;
				}
				if( ($breakStart >= $end) OR ($breakStart < $start) ){
					$msg = '__Lunch break should be within shift hours.__';
					$errors['break'] = $msg;
				}
				if( ($breakEnd > $end) OR ($breakEnd <= $start) ){
					$msg = '__Lunch break should be within shift hours.__';
					$errors['break'] = $msg;
				}
			}
		}

		if( $errors ){
			throw new HC3_ExceptionArray( $errors );
		}

		$array = array(
			'starts_at'	=> $start,
			'ends_at'	=> $end,
			'break_starts_at'	=> $breakStart,
			'break_ends_at'	=> $breakEnd,
			);

		return $this->crud->update( $id, $array );
	}

	public function delete( SH4_ShiftTypes_Model $model )
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

}