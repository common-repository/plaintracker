<?php
namespace Plainware\PlainTracker;

class X_PageRecordEdit_ActivityProjectWorker
{
	public $self = __CLASS__;

	public $modelApp = ModelApp::class;
	public $modelTimesheet = ModelTimesheet::class;

	public $modelRecord = ModelRecord::class;
	public $modelWorker = ModelWorker::class;

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

		$m = $this->modelRecord->findById( $recordId );

	// is worker
		$userId = $this->auth->getCurrentUserId($x);

		$q2 = [];
		$q2[] = [ 'userId', '=', $userId ];
		$q2[] = [ 'limit', 1 ];
		$res = $this->modelWorker->findProp( 'id', $q2 );
		if( ! $res ){
			return $ret;
		}

		$workerId = current( $res );
		if( $workerId == $m->workerId ){
			$ret = true;
			return $ret;
		}

		return $ret;
	}
}