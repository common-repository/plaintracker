<?php
namespace Plainware\PlainTracker;

class PageWorkerTimesheetIndex
{
	public $self = __CLASS__;

	public $pageParent = PageTimesheetIndex::class;

	public $modelWorker = ModelWorker::class;

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
		$ret = '__My timesheets__';
		return $ret;
	}

	public function nav( array $x )
	{
		$ret = $this->pageParent->nav( $x );

		$p = [];
		$ret[ '21-new' ] = [ ['.worker-timesheet-new', $p], '<i>&plus;</i><span>__Add new__</span>' ];

		return $ret;
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