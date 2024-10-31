<?php
namespace Plainware\PlainTracker;

class PageApproverIndex
{
	public $self = __CLASS__;

	public $modelWorker = ModelWorker::class;

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
		$ret[ '21-new' ] = [ '.approver-new', '<i>&plus;</i><span>__Add new__</span>' ];
		return $ret;
	}

	public function title( array $x )
	{
		$ret = '__Approvers__';
		return $ret;
	}

	public function post( array $x )
	{
		$project = $x[ '$project' ];

		$dictAll = $x[ '$dictAll' ];
		$listCurrentId = $x[ '$listCurrentId' ];

		$listId = $x['post']['worker'] ?? [];

		$listRemoveId = array_diff( $listCurrentId, $listId );
		$listAddId = array_diff( $listId, $listCurrentId );

		if( $listRemoveId ){
			$q = [];
			$q[] = [ 'projectId', '=', $project->id ];
			$q[] = [ 'workerId', '=', $listRemoveId ];
			$this->modelProjectManager->deleteMany( $q );
		}

		if( $listAddId ){
			$ms = [];
			foreach( $listAddId as $workerId ){
				$m = $this->modelProjectWorker->construct();
				$m->projectId = $project->id;
				$m->workerId = $workerId;
				$ms[] = $m;
			}
			$this->modelProjectWorker->createMany( $ms );
		}

		$x['redirect'] = '..';

		return $x;
	}

	public function get( array $x )
	{
return $x;
		$projectId = $x['project'] ?? null;
		if( ! $projectId ){
			$x['slug'] = 404;
			return $x;
		}

		$project = $this->modelProject->findById( $projectId );
		if( ! $project ){
			$x['slug'] = 404;
			return $x;
		}

		$x[ '$project' ] = $project;


		$a = $x['a'] ?? null;
		if( 'add' == $a ){
			$managerId = $x['manager'];
			$m = $this->modelProjectManager->construct();
			$m->projectId = $project->id;
			$m->managerId = $managerId;
			$this->modelProjectManager->create( $m );

			$p = [ 'a' => null, 'manager' => null ];
			$x['redirect'] = [ '.', $p ];
			return $x;
		}
		if( 'remove' == $a ){
			$managerId = $x['manager'];
			$q = [];
			$q[] = [ 'managerId', '=', $managerId ];
			$q[] = [ 'projectId', '=', $projectId ];
			$this->modelProjectManager->deleteMany( $q );

			$p = [ 'a' => null, 'manager' => null ];
			$x['redirect'] = [ '.', $p ];
			return $x;
		}

		$q = [];
		$q[] = [ 'stateId', '=', 'active' ];
		$dictAll = $this->modelUser->find( $q );
		$x[ '$dictAll' ] = $dictAll;

		$q = [];
		$q[] = [ 'projectId', '=', $project->id ];
		$listCurrentId = $this->modelProjectManager->findProp( 'managerId', $q );
		$listCurrentId = array_intersect( $listCurrentId, array_keys($dictAll) );
		$x[ '$listCurrentId' ] = $listCurrentId;

		return $x;
	}

	public function render( array $x )
	{
		$ret = [];

		$ret[ '24-current' ] = [ $this->self, 'renderCurrent' ];

		return $ret;
	}

	public function renderCurrent( array $x )
	{
		$q = [];
		$listApproverWorker = $this->modelApproverWorker->find( $q );

		$dictApproverWorkerId = [];
		foreach( $listApproverWorker as $e ){
			$dictApproverWorkerId[ $e->approverId ][ $e->workerId ] = $e->workerId;
		}

		if( $dictApproverWorkerId ){
			$q = [];
			$q[] = [ 'id', '=', array_keys($dictApproverWorkerId) ];
			$q[] = [ 'stateId', '=', 'active' ];
			$dict = $this->modelUser->find( $q );
		}
		else {
			$dict = [];
		}

		$q = [];
		$q[] = [ 'stateId', '=', 'active' ];
		$listWorkerId = $this->modelWorker->findProp( 'id', $q );
?>

<p>
__Approvers can approve or decline timesheets of their team.__
</p>

<table>

<?php if( $dict ): ?>
	<thead>
		<tr>
			<th>__User__</th>
			<th class="pw-col-2 pw-col-align-end">__Workers__</th>
			<th role="menu"></th>
		</tr>
	</thead>

	<tbody class="pw-valign-middle">
		<?php foreach( $dict as $m ): ?>
			<?php
			$listThisWorkerId = array_intersect( $listWorkerId, $dictApproverWorkerId[$m->id] );
			?>
			<tr>
				<td>
					<?php echo $this->pageUserId->renderTo( $x, $m ); ?>
				</td>
				<td class="pw-col-align-end">
					<?php echo count( $listThisWorkerId ); ?>
				</td>
				<th role="menu">
					<nav>
						<a href="URI:.approver-worker?approver=<?php echo esc_attr($m->id); ?>"><i>..</i><span>__Edit team__</span></a>
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
}