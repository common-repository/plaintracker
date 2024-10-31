<?php
namespace Plainware\PlainTracker;

class X_PageIndex_Worker
{
	public $self = __CLASS__;
	public $modelWorker = ModelWorker::class;

	public function navAdmin( array $ret, array $x )
	{
		$q = [];
		$q[] = [ 'stateId', '=', 'active' ];
		$count = $this->modelWorker->count( $q );
		$ret[ '54-worker' ] = [ '.worker-index', '<span>__Workers__</span><i>(' . $count . ')</i>' ];

		return $ret;
	}
}