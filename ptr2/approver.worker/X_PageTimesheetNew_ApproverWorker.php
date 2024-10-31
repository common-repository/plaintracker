<?php
namespace Plainware\PlainTracker;

class X_PageTimesheetNew_ApproverWorker
{
	public $self = __CLASS__;

	public $modelApproverWorker = ModelApproverWorker::class;

	public $auth = Auth::class;
	public $acl = Acl::class;

// allow approvers
	public function can( $ret, array $x )
	{
		if( $ret ) return $ret;

		$userId = $this->auth->getCurrentUserId($x);
		if( ! $userId ){
			return $ret;
		}

		$workerId = $x['worker'] ?? null;
		if( ! $workerId ){
			return $ret;
		}

	// is approver?
		$q = [];
		$q[] = [ 'approverId', '=', $userId ];
		$q[] = [ 'workerId', '=', $workerId ];
		$res = $this->modelApproverWorker->find( $q );
		if( $res ){
			$ret = true;
		}

		return $ret;
	}
}