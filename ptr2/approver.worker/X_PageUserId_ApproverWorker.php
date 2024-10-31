<?php
namespace Plainware\PlainTracker;

class X_PageUserId_ApproverWorker
{
	public $self = __CLASS__;

	public $modelUser = ModelUser::class;
	public $pageUserId = PageUserId::class;

	public $modelWorker = ModelWorker::class;
	public $modelApproverWorker = ModelApproverWorker::class;

	public function render( array $ret, array $x )
	{
		$userId = $x['id'] ?? null;
		if( ! $userId ) return;

		$ret[ '43-approver' ] = $this->self->renderApprover( $x );
		return $ret;
	}

	public function renderApprover( array $x )
	{
		$userId = $x['id'] ?? null;

		$q = [];
		$q[] = [ 'approverId', '=', $userId ];
		$listWorkerId = $this->modelApproverWorker->findProp( 'workerId', $q );
?>

<table>
<tbody class="pw-valign-middle">
<tr>
	<th scope="row">__Is approver?__</th>
	<td>
		<?php if( $listWorkerId ): ?>
			<em>__Yes__</em>
		<?php else : ?>
			<strong>__No__</strong>
		<?php endif; ?>
	</td>
	<th role="menu">
		<nav>
			<?php if( $listWorkerId ): ?>
				<a href="URI:.approver-worker?approver=<?php echo esc_attr($userId); ?>"><span>__View team__</span><i>&raquo;</i></a>
			<?php else : ?>
				<a href="URI:.approver-worker?approver=<?php echo esc_attr($userId); ?>">__Make approver__</a>
			<?php endif; ?>
		</nav>
	</th>
</tr>
</tbody>
</table>

<?php
	}


	public function _nav( array $ret, array $x )
	{
		$workerId = $x['id'] ?? null;
		if( ! $workerId ) return;

		$q = [];
		$q[] = [ 'workerId', '=', $workerId ];
		$listApproverId = $this->modelApproverWorker->findProp( 'approverId', $q );

		if( $listApproverId ){
			$q = [];
			$q[] = [ 'stateId', '=', 'active' ];
			$q[] = [ 'id', '=', $listApproverId ];
			$count = $this->modelUser->count( $q );
		}
		else {
			$count = 0;
		}

		$label = '<span>__Approvers__</span><i>(' . $count . ')</i>';
		if( ! $count ) $label = '<strong>' . $label . '</strong>';
		$ret[ '52-approver' ] = [ ['.worker-approver', ['worker' => $workerId]] , $label ];

		return $ret;
	}
}