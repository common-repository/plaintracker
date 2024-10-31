<?php
namespace Plainware\PlainTracker;

class PageApproverTimesheetDashboard
{
	public $self = __CLASS__;

	public $pageTimesheetDashboard = PageTimesheetDashboard::class;

	public $modelApproverWorker = ModelApproverWorker::class;
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

	// is approver?
		$q = [];
		$q[] = [ 'approverId', '=', $userId ];
		$listWorkerId = $this->modelApproverWorker->findProp( 'workerId', $q );
		if( ! $listWorkerId ){
			return $ret;
		}

		$q = [];
		$q[] = [ 'id', '=', $listWorkerId ];
		$q[] = [ 'limit', 1 ];
		$res = $this->modelWorker->findProp( 'id', $q );
		if( ! $res ){
			return $ret;
		}

		$ret = true;
		return $ret;
	}

	public function title( array $x )
	{
		$ret = '__My workers\' timesheet summary__';
		return $ret;
	}

	public function nav( array $x )
	{
		return $this->pageTimesheetDashboard->nav( $x );
	}

	public function post( array $x )
	{
		return $this->pageTimesheetDashboard->post( $x );
	}

	public function get( array $x )
	{
		$x = $this->pageTimesheetDashboard->get( $x );

	// adjust query to allow timesheets of the team
		$qWorker = $x[ '$qWorker' ];

		$userId = $this->auth->getCurrentUserId($x);
		$q2 = [];
		$q2[] = [ 'approverId', '=', $userId ];
		$listWorkerId = $this->modelApproverWorker->findProp( 'workerId', $q2 );

		$q2 = [];
		$q2[] = [ 'id', '=', $listWorkerId ];
		$listWorkerId = $this->modelWorker->findProp( 'id', $q2 );

		$qWorker[] = [ 'id', '=', $listWorkerId ];
		$x[ '$qWorker' ] = $qWorker;

		return $x;
	}

	public function render( array $x )
	{
		return $this->pageTimesheetDashboard->render( $x );
	}
}