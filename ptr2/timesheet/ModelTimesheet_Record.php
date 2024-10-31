<?php
namespace Plainware\PlainTracker;

class ModelTimesheet_Record
{
	public $self = __CLASS__;
	public $modelRecord = ModelRecord::class;

	public function delete( _Timesheet $timesheet )
	{
		$q = [];
		$q[] = [ 'workerId', '=', $timesheet->workerId ];
		$q[] = [ 'startDate', '>=', $timesheet->startDate ];
		$q[] = [ 'startDate', '<=', $timesheet->endDate ];

		$listRecord = $this->modelRecord->find( $q );
		foreach( $listRecord as $record ){
			$this->modelRecord->delete( $record );
		}

		return $timesheet;
	}
}