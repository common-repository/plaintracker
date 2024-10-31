<?php
namespace Plainware\PlainTracker;

class X_ModelActivity_ActivityProjectWorker
{
	public $model = ModelActivityProjectWorker::class;
	public $modelWorker = ModelWorker::class;

	public function delete( _Activity $activity )
	{
		$q = [];
		$q[] = [ 'activityId', '=', $activity->id ];
		$this->model->deleteMany( $q );
		return $activity;
	}

	public function findError( array $ret, array $dict )
	{
		if( ! $dict ) return $ret;

	// if has no workers
		$q = [];
		$q[] = [ 'activityId', '=', array_keys($dict) ];
		$listActivityProjectWorker = $this->model->find( $q );

		$listWorkerId = [];
		foreach( $listActivityProjectWorker as $activityProjectWorker ){
			$listWorkerId[ $activityProjectWorker->workerId ] = $activityProjectWorker->workerId;
		}

		$q = [];
		$q[] = [ 'stateId', '=', 'active' ];
		$q[] = [ 'id', '=', $listWorkerId ];
		$dictWorkerId = $this->modelWorker->findProp( 'id', $q );

		$dictActivityWorker = [];
		foreach( $listActivityProjectWorker as $e ){
			if( isset($dictActivityWorker[$e->activityId]) ) continue;
			if( ! isset($dictWorkerId[$e->workerId]) ) continue;
			$dictActivityWorker[ $e->activityId ] = $e->workerId;
		}

		foreach( array_keys($dict) as $activityId ){
			if( ! isset($dictActivityWorker[$activityId]) ){
				$ret[ $activityId ][ 'activity.project.worker' ] = '__This activity is not associated with any worker.__';
			}
		}

		return $ret;
	}
}