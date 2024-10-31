<?php
namespace Plainware\PlainTracker;

class PageProfileWorker
{
	public $self = __CLASS__;

	public $modelApp = ModelApp::class;
	public $modelWorker = ModelWorker::class;

	public $modelActivity = ModelActivity::class;
	public $pageActivityId = PageActivityId::class;

	public $modelProject = ModelProject::class;
	public $pageProjectId = PageProjectId::class;

	public $modelActivityProjectWorker = ModelActivityProjectWorker::class;

	public $auth = Auth::class;
	public $acl = Acl::class;

	public function can( array $x )
	{
		$ret = false;
		$userId = $this->auth->getCurrentUserId($x);
		if( ! $userId ){
			return $ret;
		}

		$q = $this->modelApp->queryWorkerByUser( $userId );
		$res = $this->modelWorker->find( $q );
		$worker = current( $res );
		if( ! $worker ){
			return $ret;
		}

		$ret = true;
		return $ret;
	}

	public function title( array $x )
	{
		$ret = '__Worker profile__';
		return $ret;
	}

	public function nav( array $x )
	{
		$ret = [];
		return $ret;
	}

	public function get( array $x )
	{
		$userId = $this->auth->getCurrentUserId($x);
		if( ! $userId ){
			$x['slug'] = 404;
			return $x;
		}

		$q = $this->modelApp->queryWorkerByUser( $userId );
		$res = $this->modelWorker->find( $q );
		$worker = current( $res );

		if( ! $worker ){
			$x['slug'] = 404;
			return $x;
		}

		$x[ '$worker' ] = $worker;

		return $x;
	}

	public function post( array $x )
	{
		return $x;
	}

	public function render( array $x )
	{
		$ret = [];
		$ret[ '31-project' ] = [ $this->self, 'renderProject' ];
		return $ret;
	}

	public function renderProject( array $x )
	{
		$worker = $x[ '$worker' ];

		$q = [];
		$q[] = [ 'workerId', '=', $worker->id ];
		$list = $this->modelActivityProjectWorker->find( $q );

		$dictProjectActivityId = $listActivityId = [];
		foreach( $list as $e ){
			$dictProjectActivityId[ $e->projectId ][ $e->activityId ] = $e->activityId;
			$listActivityId[ $e->activityId ] = $e->activityId;
		}

		$q = [];
		$q[] = [ 'stateId', '=', 'active' ];
		$q[] = [ 'id', '=', array_keys($dictProjectActivityId) ];
		$dictProject = $this->modelProject->find( $q );

		$q = [];
		$q[] = [ 'stateId', '=', 'active' ];
		$q[] = [ 'id', '=', $listActivityId ];
		$dictActivity = $this->modelActivity->find( $q );
?>

<header>
<h3>__Projects and activities__</h3>
</header>

<?php if( $dictProject ): ?>

	<p>
	__You can report time in the following projects for the following activities.__
	</p>

	<?php foreach( $dictProject as $project ): ?>
		<?php
		$dictThisActivity = array_intersect_key( $dictActivity, $dictProjectActivityId[$project->id] );
		if( ! $dictThisActivity ) continue;
		?>

		<section>
			<table>
				<tr>
					<th scope="row">__Project__</th>
					<td>
						<?php echo $this->pageProjectId->renderTo( $x, $project ); ?>
					</td>
				</tr>

				<?php $headon = 0; ?>
				<?php foreach( $dictThisActivity as $activity ): ?>
					<tr>
						<?php if( ! $headon++ ): ?>
							<th scope="row" rowspan="<?php echo count($dictThisActivity); ?>">__Activity__</th>
						<?php endif; ?>
						<td>
							<?php echo $this->pageActivityId->renderTo( $x, $activity ); ?>
						</td>
					</tr>
				<?php endforeach; ?>
			</table>
		</section>
	<?php endforeach; ?>

<?php else: ?>

	<table>
		<tr>
			<td>
				<strong>
					__This worker is not associated with any projects.__
				</strong>
			</td>
		</tr>
	</table>

<?php endif; ?>

<?php
	}
}