<?php
namespace Plainware\PlainTracker;

class PageWorkerApprover
{
	public $self = __CLASS__;

	public $modelWorker = ModelWorker::class;
	public $pageWorkerId = PageWorkerId::class;

	public $modelUser = ModelUser::class;
	public $pageUserId = PageUserId::class;

	public $modelApproverWorker = ModelApproverWorker::class;

	// public $pageWorkerId = PageProjectId::class;

	public $auth = Auth::class;
	public $acl = Acl::class;

	public function can( array $x )
	{
		return $this->acl->isAdmin( $this->auth->getCurrentUserId($x) );
	}

	public function nav( array $x )
	{
		$ret = [];
		return $ret;
	}

	public function title( array $x )
	{
		$ret = '__Approvers__';
		return $ret;
	}

	public function get( array $x )
	{
		$workerId = $x['worker'] ?? null;
		if( ! $workerId ){
			$x['slug'] = 404;
			return $x;
		}

		$worker = $this->modelWorker->findById( $workerId );
		if( ! $worker ){
			$x['slug'] = 404;
			return $x;
		}

		$x[ '$worker' ] = $worker;

		$a = $x['a'] ?? null;

		if( 'add' == $a ){
			$approverId = $x['approver'];
			$m = $this->modelApproverWorker->construct();
			$m->approverId = $approverId;
			$m->workerId = $workerId;
			$this->modelApproverWorker->create( $m );

			$p = [ 'a' => null, 'approver' => null ];
			$x['redirect'] = [ '.', $p ];
			return $x;
		}

		if( 'remove' == $a ){
			$approverId = $x['approver'];
			$q = [];
			$q[] = [ 'approverId', '=', $approverId ];
			$q[] = [ 'workerId', '=', $workerId ];
			$this->modelApproverWorker->deleteMany( $q );

			$p = [ 'a' => null, 'approver' => null ];
			$x['redirect'] = [ '.', $p ];
			return $x;
		}

		return $x;
	}

	public function render( array $x )
	{
		$ret = [];

		// $ret[ '24-approver' ] = [ $this->self, 'renderApprover' ];
		$ret[ '34-current' ] = [ $this->self, 'renderCurrent' ];
		$ret[ '44-new' ] = [ $this->self, 'renderNew' ];

		return $ret;
	}

	public function renderCurrent( array $x )
	{
		$worker = $x[ '$worker' ];

		$q = [];
		$q[] = [ 'workerId', '=', $worker->id ];
		$listId = $this->modelApproverWorker->findProp( 'approverId', $q );

		if( $listId ){
			$q = [];
			$q[] = [ 'id', '=', $listId ];
			$q[] = [ 'stateId', '=', 'active' ];
			$dict = $this->modelUser->find( $q );
		}
		else {
			$dict = [];
		}
?>

<p>
__The following users can approve this worker's timesheets.__
</p>

<table>

<?php if( $dict ): ?>
	<thead>
		<tr>
			<th>__User__</th>
			<th role="menu"></th>
		</tr>
	</thead>

	<tbody>
		<?php foreach( $dict as $m ): ?>
		<tr>
			<td>
				<?php echo $this->pageUserId->renderTo( $x, $m ); ?>
			</td>
			<th role="menu">
				<nav>
					<a href="URI:.?a=remove&approver=<?php echo esc_attr($m->id); ?>&worker=<?php echo esc_attr($worker->id); ?>" title="__Remove approver__"><i>&times;</i><span>__Remove approver__</span></a>
				</nav>
			</th>
		</tr>
		<?php endforeach; ?>
	</tbody>
<?php else : ?>
	<tbody>
		<tr>
			<td>
				<strong>__None__</strong>
			</td>
		</tr>
	</tbody>
<?php endif; ?>
</table>

<?php
	}

	public function renderApprover( array $x )
	{
		$approver = $x[ '$approver' ];
?>

<table>
<tbody>
	<tr>
		<th scope="row">__Approver__</th>
		<td>
			<?php echo $this->pageUserId->renderTo( $x, $approver ); ?>
		</td>
	</tr>
</tbody>
</table>

<?php
	}

	public function renderNew( array $x )
	{
		$worker = $x[ '$worker' ];

		$q = [];
		$q[] = [ 'workerId', '=', $worker->id ];
		$listId = $this->modelApproverWorker->findProp( 'approverId', $q );

		$q = [];
		$q[] = [ 'stateId', '=', 'active' ];
		if( $listId ){
			$q[] = [ 'id', '<>', $listId ];
		}

		$dict = $this->modelUser->find( $q );
		if( ! $dict ) return;
?>

<p>__Add a new approver for this worker.__</p>

<table>
<thead>
	<tr>
		<th>__User__</th>
		<th role="menu"></th>
	</tr>
</thead>
<tbody>
<?php foreach( $dict as $m ): ?>
<tr>
	<td>
		<?php echo $this->pageUserId->renderTo( $x, $m ); ?>
	</td>
	<th role="menu">
		<nav>
			<a href="URI:.?a=add&approver=<?php echo esc_attr($m->id); ?>&worker=<?php echo esc_attr($worker->id); ?>"><i>&plus;</i><span>__Add approver__</span></a>
		</nav>
	</th>
</tr>
<?php endforeach; ?>
</tbody>
</table>

<?php
	}
}