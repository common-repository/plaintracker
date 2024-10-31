<?php
namespace Plainware\PlainTracker;

class X_ModelWorker_ActivityProjectWorker
{
	public $model = ModelActivityProjectWorker::class;
	public $modelProject = ModelProject::class;

	public function delete( _Worker $worker )
	{
		$q = [];
		$q[] = [ 'workerId', '=', $worker->id ];
		$this->model->deleteMany( $q );
		return $worker;
	}

	public function findError( array $ret, array $dict )
	{
		if( ! $dict ) return $ret;

	// if has no projects
		$q = [];
		$q[] = [ 'workerId', '=', array_keys($dict) ];
		$listActivityProjectWorker = $this->model->find( $q );

		$listProjectId = [];
		foreach( $listActivityProjectWorker as $activityProjectWorker ){
			$listProjectId[ $activityProjectWorker->projectId ] = $activityProjectWorker->projectId;
		}

		$q = [];
		$q[] = [ 'stateId', '=', 'active' ];
		$q[] = [ 'id', '=', $listProjectId ];
		$dictProjectId = $this->modelProject->findProp( 'id', $q );

		$dictWorkerProject = [];
		foreach( $listActivityProjectWorker as $e ){
			if( isset($dictWorkerProject[$e->workerId]) ) continue;
			if( ! isset($dictProjectId[$e->projectId]) ) continue;
			$dictWorkerProject[ $e->workerId ] = $e->projectId;
		}

		foreach( array_keys($dict) as $workerId ){
			if( ! isset($dictWorkerProject[$workerId]) ){
				$ret[ $workerId ][ 'activity.project.worker' ] = '__This worker is not associated with any projects.__' . ' ' . '__Add the worker to at least one project to let the worker report their time.__';
			}
		}

		return $ret;
	}
}