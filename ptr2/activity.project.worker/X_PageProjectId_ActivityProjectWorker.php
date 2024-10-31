<?php
namespace Plainware\PlainTracker;

class X_PageProjectId_ActivityProjectWorker
{
	public $self = __CLASS__;
	public $modelActivityProjectWorker = ModelActivityProjectWorker::class;

	public $modelWorker = ModelWorker::class;
	public $pageWorkerId = PageWorkerId::class;

	public function nav( array $ret, array $x )
	{
		$projectId = $x['id'] ?? null;
		if( ! $projectId ) return;

		$q = [];
		$q[] = [ 'projectId', '=', $projectId ];
		$listWorkerId = $this->modelActivityProjectWorker->findProp( 'workerId', $q );

		$count = 0;
		if( $listWorkerId ){
			$q = [];
			$q[] = [ 'stateId', '=', 'active' ];
			$q[] = [ 'id', '=', $listWorkerId ];
			$count = $this->modelWorker->count( $q );
		}

		$label = '<span>__Workers__</span><i>(' . $count . ')</i>';
		if( ! $count ) $label = '<strong>' . $label . '</strong>';
		$ret[ '51-worker' ] = [ ['.project-worker', ['project' => $projectId]] , $label ];

		return $ret;
	}
}