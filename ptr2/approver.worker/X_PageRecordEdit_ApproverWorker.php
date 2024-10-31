<?php
namespace Plainware\PlainTracker;

class X_PageRecordEdit_ApproverWorker
{
	public $self = __CLASS__;

	public $modelApp = ModelApp::class;
	public $modelTimesheet = ModelTimesheet::class;

	public $modelApproverWorker = ModelApproverWorker::class;

	public $auth = Auth::class;
	public $acl = Acl::class;

	public function can( $ret, array $x )
	{
		if( $ret ) return $ret;

		$recordId = $x['id'];
		$q = $this->modelApp->queryTimesheetByRecord( $recordId );
		$res = $this->modelTimesheet->find( $q );
		$timesheet = current( $res );
		if( ! $timesheet ){
			return $ret;
		}

	// can edit for draft and submit only
		if( ! in_array($timesheet->stateId, ['draft', 'submit']) ){
			return $ret;
		}

	// can approve this user?
		$userId = $this->auth->getCurrentUserId($x);
		$q2 = [];
		$q2[] = [ 'approverId', '=', $userId ];
		$q2[] = [ 'workerId', '=', $timesheet->workerId ];
		$res = $this->modelApproverWorker->findProp( 'workerId', $q2 );

		if( $res ){
			$ret = true;
			return $ret;
		}

		return $ret;
	}
}