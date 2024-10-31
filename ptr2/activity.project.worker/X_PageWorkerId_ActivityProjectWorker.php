<?php
namespace Plainware\PlainTracker;

class X_PageWorkerId_ActivityProjectWorker
{
	public $self = __CLASS__;
	public $modelActivityProjectWorker = ModelActivityProjectWorker::class;

	public $modelProject = ModelProject::class;
	public $pageProjectId = PageProjectId::class;

	public function nav( array $ret, array $x )
	{
		$workerId = $x['id'] ?? null;
		if( ! $workerId ) return;

		$q = [];
		$q[] = [ 'workerId', '=', $workerId ];
		$listProjectId = $this->modelActivityProjectWorker->findProp( 'projectId', $q );

		$count = 0;
		if( $listProjectId ){
			$q = [];
			$q[] = [ 'stateId', '=', 'active' ];
			$q[] = [ 'id', '=', $listProjectId ];
			$count = $this->modelProject->count( $q );
		}

		$label = '<span>__Projects__</span><i>(' . $count . ')</i>';
		if( ! $count ) $label = '<strong>' . $label . '</strong>';
		$ret[ '51-project' ] = [ ['.worker-project', ['worker' => $workerId]] , $label ];

		return $ret;
	}
}