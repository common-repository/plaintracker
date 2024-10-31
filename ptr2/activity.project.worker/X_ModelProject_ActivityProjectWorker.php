<?php
namespace Plainware\PlainTracker;

class X_ModelProject_ActivityProjectWorker
{
	public $model = ModelActivityProjectWorker::class;
	public $modelWorker = ModelWorker::class;

	public function delete( _Project $project )
	{
		$q = [];
		$q[] = [ 'projectId', '=', $project->id ];
		$this->model->deleteMany( $q );
		return $project;
	}

	public function findError( array $ret, array $dict )
	{
		if( ! $dict ) return $ret;

	// if has no workers
		$q = [];
		$q[] = [ 'projectId', '=', array_keys($dict) ];
		$listActivityProjectWorker = $this->model->find( $q );

		$listWorkerId = [];
		foreach( $listActivityProjectWorker as $activityProjectWorker ){
			$listWorkerId[ $activityProjectWorker->workerId ] = $activityProjectWorker->workerId;
		}

		$q = [];
		$q[] = [ 'stateId', '=', 'active' ];
		$q[] = [ 'id', '=', $listWorkerId ];
		$dictWorkerId = $this->modelWorker->findProp( 'id', $q );

		$dictProjectWorker = [];
		foreach( $listActivityProjectWorker as $e ){
			if( isset($dictProjectWorker[$e->projectId]) ) continue;
			if( ! isset($dictWorkerId[$e->workerId]) ) continue;
			$dictProjectWorker[ $e->projectId ] = $e->workerId;
		}

		foreach( array_keys($dict) as $projectId ){
			if( ! isset($dictProjectWorker[$projectId]) ){
				$ret[ $projectId ][ 'activity.project.worker' ] = '__There are no workers in this project.__';
			}
		}

		return $ret;
	}
}