<?php
namespace Plainware\PlainTracker;

class X_ModelProject_Timesheet
{
	public $self = __CLASS__;
	public $modelTimesheet = ModelTimesheet::class;

	public function delete( _Project $project )
	{
		$q = [];
		$q[] = [ 'projectId', '=', $project->id ];

		$listTimesheet = $this->modelTimesheet->find( $q );
		foreach( $listTimesheet as $timesheet ){
			$this->modelTimesheet->delete( $timesheet );
		}

		return $project;
	}
}