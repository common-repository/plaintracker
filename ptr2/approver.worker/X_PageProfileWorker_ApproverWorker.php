<?php
namespace Plainware\PlainTracker;

class X_PageProfileWorker_ApproverWorker
{
	public $self = __CLASS__;

	public $modelApp = ModelApp::class;

	public $modelUser = ModelUser::class;
	public $pageUserId = PageUserId::class;

	public $modelWorker = ModelWorker::class;

	public $modelApproverWorker = ModelApproverWorker::class;

	public $auth = Auth::class;

	public function render( array $ret, array $x )
	{
		$ret[ '42-approver' ] = [ $this->self, 'renderApprover' ];
		return $ret;
	}

	public function renderApprover( array $x )
	{
		$userId = $this->auth->getCurrentUserId($x);

		$q = $this->modelApp->queryWorkerByUser( $userId );
		$res = $this->modelWorker->find( $q );
		$worker = current( $res );
		if( ! $worker ){
			return;
		}

		$q = [];
		$q[] = [ 'workerId', '=', $worker->id ];
		$listId = $this->modelApproverWorker->findProp( 'approverId', $q );

		$dictApprover = [];
		if( $listId ){
			$q = [];
			$q[] = [ 'stateId', '=', 'active' ];
			$q[] = [ 'id', '=', $listId ];
			$dictApprover = $this->modelUser->find( $q );
		}
?>

<header>
<h3>__Approvers__</h3>
</header>

<p>
__The following people approve your timesheets.__
</p>

<table>
<tbody>

<?php if( $dictApprover ): ?>

	<?php foreach( $dictApprover as $approver ): ?>
	<tr>
		<td>
			<?php echo $this->pageUserId->renderTo( $x, $approver ); ?>
		</td>
	</tr>
	<?php endforeach; ?>

<?php else: ?>

	<tr>
		<td>
			<strong>__None__</strong>
		</td>
	</tr>

<?php endif; ?>

<tr>
</tr>
</tbody>
</table>

<?php
	}
}