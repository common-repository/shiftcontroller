<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
interface HC3_Relations_
{
	public function findFromIds( $relationName, $toModel );
	public function findToIds( $fromModel, $relationName );

	public function delete( $fromModel, $relationName, $toModel = NULL );
	public function create( $fromModel, $relationName, $toModel );

	public function updateTo( $fromModel, $relationName, $toModels );
	public function updateFrom( $fromModels, $relationName, $toModel );
}

class HC3_Relations implements HC3_Relations_
{
	public function __construct(
		HC3_CrudFactory $crudFactory
		)
	{
		$this->crud = $crudFactory->make('relation');
	}

	public function findFromIds( $relationName, $toModel )
	{
		$return = array();

		$toModelId = is_object($toModel) ? $toModel->id() : $toModel;

		$args = array();
		$args[] = array('relation_name', '=', $relationName);
		$args[] = array('to_id', '=', $toModelId);

		$results = $this->crud->read( $args );

		foreach( $results as $r ){
			if( $r['from_id'] > 0 ){
				$return[] = $r['from_id'];
			}
		}

		return $return;
	}

	public function findToIds( $fromModel, $relationName )
	{
		$return = array();

		$fromModelId = is_object($fromModel) ? $fromModel->id() : $fromModel;

		$args = array();
		$args[] = array('relation_name', '=', $relationName);
		$args[] = array('from_id', '=', $fromModelId);

		$results = $this->crud->read( $args );

		foreach( $results as $r ){
			if( $r['to_id'] > 0 ){
				$return[] = $r['to_id'];
			}
		}

		return $return;
	}

	public function updateTo( $relationName, $fromModel, $toModels )
	{
		$fromId = is_object($fromModel) ? $fromModel->id() : $fromModel;
		$toIds = array();
		foreach( $toModels as $e ){
			$toIds[] = is_object($e) ? $e->id() : $e;
		}

	// get current
		$currentToIds = $this->findToIds( $relationName, $fromId );

		$toAdd = array_diff( $toIds, $currentToIds );
		$toDelete = array_diff( $currentToIds, $toIds );

		foreach( $toAdd as $toId ){
			$this->create( $relationName, $fromId, $toId );
		}

		foreach( $toDelete as $toId ){
			$this->delete( $relationName, $fromId, $toId );
		}
	}

	public function updateFrom( $relationName, $toModel, $fromModels )
	{
		$toId = is_object($toModel) ? $toModel->id() : $toModel;
		$fromIds = array();
		foreach( $fromModels as $e ){
			$fromIds[] = is_object($e) ? $e->id() : $e;
		}

	// get current
		$currentFromIds = $this->findFromIds( $relationName, $toId );

		$toAdd = array_diff( $fromIds, $currentFromIds );
		$toDelete = array_diff( $currentFromIds, $fromIds );

		foreach( $toAdd as $fromId ){
			$this->create( $relationName, $fromId, $toId );
		}

		foreach( $toDelete as $fromId ){
			$this->delete( $relationName, $fromId, $toId );
		}
	}

	public function create( $relationName, $fromModel, $toModel )
	{
		$toId = is_object($toModel) ? $toModel->id() : $toModel;
		$fromId = is_object($fromModel) ? $fromModel->id() : $fromModel;

		$values = array(
			'relation_name'	=> $relationName,
			'to_id'			=> $toId,
			'from_id' 		=> $fromId,
			);
		$this->crud->create( $values );
	}

	public function delete( $relationName, $fromModel = NULL, $toModel = NULL )
	{
		$toId = is_object($toModel) ? $toModel->id() : $toModel;
		$fromId = is_object($fromModel) ? $fromModel->id() : $fromModel;

	// find existing
		$args = array();
		$args[] = array('relation_name', '=', $relationName);
		if( $fromId ){
			$args[] = array('from_id', '=', $fromId);
		}
		if( $toId ){
			$args[] = array('to_id', '=', $toId);
		}

		$results = $this->crud->read( $args );
		$ids = array_keys( $results );

		foreach( $ids as $id ){
			$this->crud->delete( $id );
		}
	}
}