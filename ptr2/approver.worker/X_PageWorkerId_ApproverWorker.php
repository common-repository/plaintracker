<?php
namespace Plainware\PlainTracker;

class X_PageWorkerId_ApproverWorker
{
	public $self = __CLASS__;

	public $modelUser = ModelUser::class;
	public $pageUserId = PageUserId::class;

	public $modelApproverWorker = ModelApproverWorker::class;

	public function nav( array $ret, array $x )
	{
		$workerId = $x['id'] ?? null;
		if( ! $workerId ) return;

		$q = [];
		$q[] = [ 'workerId', '=', $workerId ];
		$listApproverId = $this->modelApproverWorker->findProp( 'approverId', $q );

		if( $listApproverId ){
			$q = [];
			$q[] = [ 'stateId', '=', 'active' ];
			$q[] = [ 'id', '=', $listApproverId ];
			$count = $this->modelUser->count( $q );
		}
		else {
			$count = 0;
		}

		$label = '<span>__Approvers__</span><i>(' . $count . ')</i>';
		if( ! $count ) $label = '<strong>' . $label . '</strong>';
		$ret[ '52-approver' ] = [ ['.worker-approver', ['worker' => $workerId]] , $label ];

		return $ret;
	}
}