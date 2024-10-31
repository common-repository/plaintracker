<?php
namespace Plainware\PlainTracker;

class PageApproverWorker
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
		$ret = '__Approval team__';
		return $ret;
	}

	public function get( array $x )
	{
		$approverId = $x['approver'] ?? null;
		if( ! $approverId ){
			$x['slug'] = 404;
			return $x;
		}

		$approver = $this->modelUser->findById( $approverId );
		if( ! $approver ){
			$x['slug'] = 404;
			return $x;
		}

		$x[ '$approver' ] = $approver;

		$a = $x['a'] ?? null;
		if( 'add' == $a ){
			$workerId = $x['worker'];
			$m = $this->modelApproverWorker->construct();
			$m->approverId = $approver->id;
			$m->workerId = $workerId;
			$this->modelApproverWorker->create( $m );

			$p = [ 'a' => null, 'worker' => null ];
			$x['redirect'] = [ '.', $p ];
			return $x;
		}
		if( 'remove' == $a ){
			$workerId = $x['worker'];
			$q = [];
			$q[] = [ 'approverId', '=', $approver->id ];
			$q[] = [ 'workerId', '=', $workerId ];
			$this->modelApproverWorker->deleteMany( $q );

			$p = [ 'a' => null, 'worker' => null ];
			$x['redirect'] = [ '.', $p ];
			return $x;
		}

		return $x;
	}

	public function render( array $x )
	{
		$ret = [];

		$ret[ '24-approver' ] = [ $this->self, 'renderApprover' ];
		$ret[ '34-current' ] = [ $this->self, 'renderCurrent' ];
		$ret[ '44-new' ] = [ $this->self, 'renderNew' ];

		return $ret;
	}

	public function renderCurrent( array $x )
	{
		$approver = $x[ '$approver' ];

		$q = [];
		$q[] = [ 'approverId', '=', $approver->id ];
		$listId = $this->modelApproverWorker->findProp( 'workerId', $q );

		if( $listId ){
			$q = [];
			$q[] = [ 'id', '=', $listId ];
			$q[] = [ 'stateId', '=', 'active' ];
			$dict = $this->modelWorker->find( $q );
		}
		else {
			$dict = [];
		}
?>

<table>

<p>
__This user can approve or decline timesheets of the following workers.__
</p>

<?php if( $dict ): ?>
	<thead>
		<tr>
			<th>__Worker__</th>
			<th role="menu"></th>
		</tr>
	</thead>

	<tbody class="pw-valign-middle">
		<?php foreach( $dict as $m ): ?>
		<tr>
			<td>
				<?php echo $this->pageWorkerId->renderTo( $x, $m ); ?>
			</td>
			<th role="menu">
				<nav>
					<a title="__Remove worker__" href="URI:.?a=remove&approver=<?php echo esc_attr($approver->id); ?>&worker=<?php echo esc_attr($m->id); ?>"><i>&minus;</i><span>__Remove worker__</span></a>
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
		$approver = $x[ '$approver' ];

		$q = [];
		$q[] = [ 'approverId', '=', $approver->id ];
		$listId = $this->modelApproverWorker->findProp( 'workerId', $q );

		$q = [];
		$q[] = [ 'stateId', '=', 'active' ];
		if( $listId ){
			$q[] = [ 'id', '<>', $listId ];
		}

		$dict = $this->modelWorker->find( $q );
		if( ! $dict ) return;
?>

<header><h3>__Add more workers__</h3></header>

<table>
<thead>
<tr>
<th>__Worker__</th>
<th role="menu"></th>
</tr>
</thead>
<tbody class="pw-valign-middle">
<?php foreach( $dict as $m ): ?>
<tr>
	<td>
		<?php echo $this->pageWorkerId->renderTo( $x, $m ); ?>
	</td>
	<th role="menu">
		<nav>
			<a title="__Add worker__" href="URI:.?a=add&approver=<?php echo esc_attr($approver->id); ?>&worker=<?php echo esc_attr($m->id); ?>"><i>&plus;</i><span>__Add worker__</span></a>
		</nav>
	</th>
</tr>
<?php endforeach; ?>
</tbody>
</table>

<?php
	}
}