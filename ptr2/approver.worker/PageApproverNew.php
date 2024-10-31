<?php
namespace Plainware\PlainTracker;

class PageApproverNew
{
	public $self = __CLASS__;

	public $modelApproverWorker = ModelApproverWorker::class;

	public $modelUser = ModelUser::class;
	public $pageUserId = PageUserId::class;
	public $helperUserWordpress = HelperUserWordpress::class;

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
		$ret = '__New approver__';
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
	}

	public function render( array $x )
	{
		$ret = [];
		$ret[ '34-select' ] = [ $this->self, 'renderSelect' ];
		return $ret;
	}

	public function renderSelect( array $x )
	{
		$q = [];
		$listAlreadyId = $this->modelApproverWorker->findProp( 'approverId', $q );

		$q = [];
		if( $listAlreadyId ){
			$q[] = [ 'id', '<>', $listAlreadyId ];
		}

		$dict = $this->modelUser->find( $q );
		if( ! $dict ) return;
?>

<table>
<thead class="pw-nowrap">
	<tr>
		<th>__User__</th>
		<?php if( defined('WPINC') ): ?>
			<th class="pw-col-3">__WordPress username__</th>
			<th class="pw-col-3">__WordPress user role__</th>
		<?php endif; ?>
		<th role="menu"></th>
	</tr>
</thead>
<tbody class="pw-valign-middle">
	<?php foreach( $dict as $m ): ?>
		<tr>
			<td>
				<?php echo $this->pageUserId->renderTo( $x, $m ); ?>
			</td>

			<?php if( defined('WPINC') ): ?>
				<?php
				$wpUser = get_user_by( 'id', $m->id );
				$listRole = $this->helperUserWordpress->getWordpressRole( $m->id );
				?>
				<td>
					<?php echo esc_html( $wpUser->user_login ); ?>
				</td>
				<td>
					<span class="pw-comma-separated">
					<?php foreach( $listRole as $role ): ?>
						<span>
							<?php echo $role; ?>
						</span>
					<?php endforeach; ?>
					</span>
				</td>
			<?php endif; ?>

			<th role="menu">
				<nav>
					<a href="URI:../approver-worker?approver=<?php echo esc_attr($m->id); ?>"><i>&plus;</i><span>__Add approver__</span></a>
				</nav>
			</th>
		</tr>
	<?php endforeach; ?>
</tbody>
</table>

<?php
	}
}