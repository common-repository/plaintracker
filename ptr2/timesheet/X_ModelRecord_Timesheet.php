<?php
namespace Plainware\PlainTracker;

class X_ModelRecord_Timesheet
{
	public $self = __CLASS__;
	public $modelTimesheet = ModelTimesheet::class;

// add state
	public function find( array $ret )
	{
		if( ! $ret ) return $ret;

		$minDate = $maxDate = null;
		$listWorkerId = [];
		foreach( $ret as $record ){
			$listWorkerId[ $record->workerId ] = $record->workerId;
			if( (null === $minDate) OR ($record->startDate < $minDate) ){
				$minDate = $record->startDate;
			}
			if( (null === $maxDate) OR ($record->startDate > $maxDate) ){
				$maxDate = $record->startDate;
			}
		}

		$q = [];
		$q[] = [ 'workerId', '=', $listWorkerId ];
		$q[] = [ 'startDate', '<=', $maxDate ];
		$q[] = [ 'endDate', '>=', $minDate ];

		$listTimesheet = $this->modelTimesheet->find( $q );

		$repoWorkerTimesheet = [];
		foreach( $listTimesheet as $timesheet ){
			$key = $timesheet->workerId;
			$repoWorkerTimesheet[ $key ][ $timesheet->id ] = $timesheet;
		}

		$ids = array_keys( $ret );
		foreach( $ids as $id ){
			$record = $ret[ $id ];
			$key = $record->workerId;
			if( ! isset($repoWorkerTimesheet[$key]) ){
				unset( $ret[$id] );
				continue;
			}

			$timesheet = null;
			foreach( $repoWorkerTimesheet[$key] as $testTimesheet ){
				if( ($testTimesheet->startDate <= $record->startDate) && ($testTimesheet->endDate >= $record->startDate) ){
					$timesheet = $testTimesheet;
					break;
				}
			}

			if( ! $timesheet ){
				unset( $ret[$id] );
				continue;
			}

			$ret[ $id ]->stateId = $timesheet->stateId;
		}

		return $ret;
	}
}