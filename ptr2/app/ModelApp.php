<?php
namespace Plainware\PlainTracker;

class ModelApp
{
	public $self = __CLASS__;
	public $modelTimesheet = ModelTimesheet::class;
	public $modelRecord = ModelRecord::class;
	public $modelActivityWorker = ModelActivityWorker::class;
	public $modelProjectWorker = ModelProjectWorker::class;
	public $modelProjectManager = ModelProjectManager::class;

	public function queryWorkerByUser( $userId )
	{
		$q = [];
		$q[] = [ 'userId', '=', $userId ];
		return $q;
	}

	public function queryTimesheetByRecord( $recordId )
	{
		$q = [];

		$record = $this->modelRecord->findById( $recordId );
		if( ! $record ){
			$q[] = [ 1, '=', 0 ];
			return $q;
		}

		$q[] = [ 'workerId', '=', $record->workerId ];
		// $q[] = [ 'projectId', '=', $record->projectId ];
		$q[] = [ 'startDate', '<=', $record->startDate ];
		$q[] = [ 'endDate', '>=', $record->startDate ];

		return $q;

		// $res = $this->modelTimesheet->find( $q );
		// $ret = current( $res );
		// return $ret;
	}
}