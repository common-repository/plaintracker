<?php
namespace Plainware\PlainTracker;

class X_ModelWorker_ApproverWorker
{
	public $model = ModelApproverWorker::class;
	public $modelUser = ModelUser::class;

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

	// if has no approvers
		$q = [];
		$q[] = [ 'workerId', '=', array_keys($dict) ];
		$listApproverWorker = $this->model->find( $q );

		$listApproverId = [];
		foreach( $listApproverWorker as $approverWorker ){
			$listApproverId[ $approverWorker->approverId ] = $approverWorker->approverId;
		}

		$q = [];
		$q[] = [ 'stateId', '=', 'approve' ];
		$q[] = [ 'id', '=', $listApproverId ];
		$dictApproverId = $this->modelUser->findProp( 'id', $q );

		$dictWorkerApprover = [];
		foreach( $listApproverWorker as $approverWorker ){
			if( isset($dictWorkerApprover[$approverWorker->workerId]) ) continue;
			if( ! isset($dictApproverId[$approverWorker->approverId]) ) continue;
			$dictWorkerApprover[ $approverWorker->workerId ] = $approverWorker->approverId;
		}

		foreach( array_keys($dict) as $workerId ){
			if( ! isset($dictWorkerApprover[$workerId]) ){
				$ret[ $workerId ][ 'approver.worker' ] = '__This worker has no approvers. Add at least one approver to let the worker report their time.__';
			}
		}

		return $ret;
	}
}