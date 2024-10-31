<?php
namespace Plainware\PlainTracker;

class PageWorkerRecordIndex
{
	public $self = __CLASS__;

	public $pageParent = PageRecordIndex::class;

	public $modelWorker = ModelWorker::class;
	public $modelActivityProjectWorker = ModelActivityProjectWorker::class;

	public $auth = Auth::class;
	public $acl = Acl::class;

	public function can( array $x )
	{
		$ret = false;

		$userId = $this->auth->getCurrentUserId($x);
		if( ! $userId ){
			return $ret;
		}

	// is worker?
		$q2 = [];
		$q2[] = [ 'userId', '=', $userId ];
		$q2[] = [ 'limit', 1 ];
		$res = $this->modelWorker->findProp( 'id', $q2 );
		if( $res ){
			$ret = true;
		}

		return $ret;
	}

	public function title( array $x )
	{
		$ret = '__My time records__';
		return $ret;
	}

	public function nav( array $x )
	{
		return $this->pageParent->nav( $x );
	}

	public function post( array $x )
	{
		return $this->pageParent->post( $x );
	}

	public function get( array $x )
	{
		$x = $this->pageParent->get( $x );

	// iknow worker
		$iknow = $x['iknow'] ?? [];
		$iknow[] = 'worker';
		$x['iknow'] = $iknow;

	// adjust query to allow records of the worker only
		$q = $x[ '$q' ];
		$userId = $this->auth->getCurrentUserId($x);

		$q2 = [];
		$q2[] = [ 'userId', '=', $userId ];
		$q2[] = [ 'limit', 1 ];
		$res = $this->modelWorker->findProp( 'id', $q2 );
		$workerId = current( $res );

		$q[] = [ 'workerId', '=', $workerId ];
		$x[ '$q' ] = $q;

		return $x;
	}

	public function render( array $x )
	{
		return $this->pageParent->render( $x );
	}
}