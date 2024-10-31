<?php
namespace Plainware\PlainTracker;

class X_PageTimesheetId_ActivityProjectWorker
{
	public $self = __CLASS__;

	public $modelTimesheet = ModelTimesheet::class;
	public $modelActivityProjectWorker = ModelActivityProjectWorker::class;
	public $modelWorker = ModelWorker::class;

	public $auth = Auth::class;
	public $acl = Acl::class;

// allow worker
	public function can( $ret, array $x )
	{
		if( $ret ) return $ret;

		$userId = $this->auth->getCurrentUserId($x);
		if( ! $userId ){
			return $ret;
		}

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

		$id = $x['id'];
		$timesheet = $this->modelTimesheet->findById( $id );

		if( $workerId != $timesheet->workerId ){
			return $ret;
		}

		$a = $x['a-'] ?? null;

		if( (! $a) OR in_array($a, ['submit']) ){
			$ret = true;
			return $ret;
		}

		return $ret;
	}
}