<?php
namespace Plainware\PlainTracker;

class PageWorkerTimesheetNew
{
	public $self = __CLASS__;

	public $pageParent = PageTimesheetNew::class;

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
		$ret = $this->pageParent->title( $x );
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
		$userId = $this->auth->getCurrentUserId($x);

		$q2 = [];
		$q2[] = [ 'userId', '=', $userId ];
		$q2[] = [ 'limit', 1 ];
		$res = $this->modelWorker->findProp( 'id', $q2 );
		$workerId = current( $res );

		$x['worker'] = $workerId;

		$x = $this->pageParent->get( $x );

		return $x;
	}

	public function render( array $x )
	{
		return $this->pageParent->render( $x );
	}
}