<?php
namespace Plainware\PlainTracker;

class X_PageIndex_ApproverWorker
{
	public $self = __CLASS__;

	public $modelTimesheet = ModelTimesheet::class;
	public $modelApproverWorker = ModelApproverWorker::class;
	public $modelWorker = ModelWorker::class;

	public $htmlMenu = \Plainware\HtmlMenu::class;

	public $auth = Auth::class;
	public $acl = Acl::class;

	public function render( array $ret, array $x )
	{
		$ret[ '45-approver' ] = [ $this->self, 'renderApprover' ];
		return $ret;
	}

	public function navAdmin( array $ret, array $x )
	{
		$q = [];
		$listApproverId = $this->modelApproverWorker->findProp( 'approverId', $q );
		$ret[ '55-approver' ] = [ '.approver-index', '<span>__Approvers__</span><i>(' . count($listApproverId) . ')</i>' ];
		return $ret;
	}

	public function navApprover( array $x )
	{
		$ret = [];

		$ret[ '33-timesheet-dashboard' ] = [ 'approver-timesheet-dashboard', '__My workers\' timesheet summary__' ];
		$ret[ '35-timesheet' ] = [ 'approver-timesheet-index', '__My workers\' timesheets__' ];
		$ret[ '36-record' ] = [ 'approver-record-index', '__My workers\' time records__' ];
		$ret[ '54-profile' ] = [ '.profile-approver', '__Approver profile__' ];

	// to approve
		$userId = $this->auth->getCurrentUserId( $x );
		if( $userId ){
			$q = [];
			$q[] = [ 'approverId', '=', $userId ];
			$listWorkerId = $this->modelApproverWorker->findProp( 'workerId', $q );

			if( $listWorkerId ){
				$q = [];
				$q[] = [ 'id', '=', $listWorkerId ];
				$q[] = [ 'stateId', '=', 'active' ];
				$listWorkerId = $this->modelWorker->findProp( 'id', $q );

				if( $listWorkerId ){
					$q = [];
					$q[] = [ 'stateId', '=', 'submit' ];
					$q[] = [ 'workerId', '=', $listWorkerId ];
					$count = $this->modelTimesheet->count( $q );
					if( $count ){
						$p = [ 'state' => 'submit' ];
						$ret[ '22-approve' ] = [ ['approver-timesheet-index', $p], '<span>__Approval queue__</span><i>(' . $count . ')</i>' ];
					}
				}
			}
		}

		return $ret;
	}

	public function renderApprover( array $x )
	{
		$nav = $this->self->navApprover( $x );
		$navView = $this->htmlMenu->render( $x, $nav );
		if( ! $navView ) return $ret;
?>

<header>
<h3>__Approver area__</h3>
</header>

<nav><?php echo $navView; ?></nav>

<?php
	}
}