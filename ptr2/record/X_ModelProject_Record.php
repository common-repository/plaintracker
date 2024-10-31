<?php
namespace Plainware\PlainTracker;

class X_ModelProject_Record
{
	public $self = __CLASS__;
	public $t = \Plainware\Time::class;
	public $modelRecord = ModelRecord::class;

	public function delete( _Project $project )
	{
		$q = [];
		$q[] = [ 'projectId', '=', $project->id ];
		$this->modelRecord->deleteMany( $q );

		return $project;
	}
}