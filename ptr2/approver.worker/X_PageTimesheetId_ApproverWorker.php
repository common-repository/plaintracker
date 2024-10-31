<?php
namespace Plainware\PlainTracker;

class X_PageTimesheetId_ApproverWorker
{
	public $self = __CLASS__;

	public $modelTimesheet = ModelTimesheet::class;
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

		$id = $x['id'];
		$timesheet = $this->modelTimesheet->findById( $id );

	// is approver?
		$q = [];
		$q[] = [ 'approverId', '=', $userId ];
		$q[] = [ 'workerId', '=', $timesheet->workerId ];
		$res = $this->modelApproverWorker->find( $q );
		if( $res ){
			$ret = true;
		}

		return $ret;
	}
}