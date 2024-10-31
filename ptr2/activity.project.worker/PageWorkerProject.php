<?php
namespace Plainware\PlainTracker;

class PageWorkerProject
{
	public $self = __CLASS__;

	public $modelActivity = ModelActivity::class;
	public $pageActivityId = PageActivityId::class;

	public $modelWorker = ModelWorker::class;
	public $pageWorkerId = PageWorkerId::class;

	public $modelProject = ModelProject::class;
	public $pageProjectId = PageProjectId::class;

	public $modelActivityProjectWorker = ModelActivityProjectWorker::class;

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
		$ret = '__Worker projects__';
		return $ret;
	}

	public function post( array $x )
	{
		$project = $x[ '$project' ]; 
		$worker = $x[ '$worker' ];

		$listActivityId = $x['post']['activity'] ?? [];

		$q = [];
		$q[] = [ 'projectId', '=', $project->id ];
		$q[] = [ 'workerId', '=', $worker->id ];
		$listCurrentActivityId = $this->modelActivityProjectWorker->findProp( 'activityId', $q );

		$toDelete = array_diff( $listCurrentActivityId, $listActivityId );
		$toAdd = array_diff( $listActivityId, $listCurrentActivityId );
		if( $toDelete ){
			$q = [];
			$q[] = [ 'activityId', '=', $toDelete ];
			$q[] = [ 'projectId', '=', $project->id ];
			$q[] = [ 'workerId', '=', $worker->id ];
			$this->modelActivityProjectWorker->deleteMany( $q );
		}

		foreach( $toAdd as $activityId ){
			$m = $this->modelActivityProjectWorker->construct();
			$m->activityId = $activityId;
			$m->projectId = $project->id;
			$m->workerId = $worker->id;
			$this->modelActivityProjectWorker->create( $m );
		}

		$p = [ 'project' => null ];
		$x['redirect'] = [ '.', $p ];

		return $x;
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

		$project = null;
		$projectId = $x['project'] ?? null;
		if( $projectId ){
			$project = $this->modelProject->findById( $projectId );
		}
		$x[ '$project' ] = $project;

		return $x;
	}

	public function render( array $x )
	{
		$ret = [];

		$project = $x[ '$project' ];
		if( $project ){
			$ret[ '53-project' ] = [ $this->self, 'renderProject' ];
		}
		else {
			$ret[ '53-list' ] = [ $this->self, 'renderList' ];
			$ret[ '54-new' ] = [ $this->self, 'renderNew' ];
		}

		return $ret;
	}

	public function renderProject( array $x )
	{
		$worker = $x[ '$worker' ];
		$project = $x[ '$project' ];

		$q = [];
		$q[] = [ 'stateId', '=', 'active' ];
		$dictActivity = $this->modelActivity->find( $q );

		$q = [];
		$q[] = [ 'projectId', '=', $project->id ];
		$q[] = [ 'workerId', '=', $worker->id ];
		$list = $this->modelActivityProjectWorker->find( $q );
		$v = [];
		foreach( $list as $e ){
			$v[ $e->activityId ] = $e->activityId;
		}
?>

<form method="post">

<section>
	<table>
		<tr>
			<th scope="row">__Worker__</th>
			<td>
				<?php echo $this->pageWorkerId->renderTo( $x, $worker ); ?>
			</td>
		</tr>
		<tr>
			<th scope="row">__Project__</th>
			<td>
				<?php echo $this->pageProjectId->renderTo( $x, $project ); ?>
			</td>
		</tr>
	</table>
</section>

<section>
	<p>
	__Select activities that the worker can do in the project.__ <?php if( $v ): ?>__Uncheck all options to remove the worker from the project.__<?php endif; ?>
	</p>

	<table>
		<?php $headon = 0; ?>
		<?php foreach( $dictActivity as $activity ): ?>
			<tr>
				<?php if( ! $headon++ ): ?>
					<th scope="row" rowspan="<?php echo count($dictActivity); ?>">__Activity__</th>
				<?php endif; ?>
				<?php
				$label = $this->pageActivityId->renderTo( $x, $activity, false );
				?>
				<td>
					<label><input type="checkbox" name="activity[]" value="<?php echo $activity->id; ?>"<?php if( in_array($activity->id, $v) ): ?> checked<?php endif; ?>><mark><?php echo $label; ?></mark><s><?php echo $label; ?></s></label>
				</td>
			</tr>
		<?php endforeach; ?>
	</table>
</section>

<footer>
	<button type="submit"><?php if( $v ): ?>__Save__<?php else : ?>__Add worker to project__<?php endif; ?></button>
</footer>

</form>

<?php
	}

	public function renderList( array $x )
	{
		$worker = $x[ '$worker' ];

		$q = [];
		$q[] = [ 'workerId', '=', $worker->id ];

		$dictProjectActivity = $listActivityId = [];
		$res = $this->modelActivityProjectWorker->find( $q );
		foreach( $res as $e ){
			$dictProjectActivity[ $e->projectId ][ $e->activityId ] = $e->activityId;
			$listActivityId[ $e->activityId ] = $e->activityId;
		}

		$dictProject = [];
		$listProjectId = array_keys( $dictProjectActivity );
		if( $listProjectId ){
			$q = [];
			$q[] = [ 'id', '=', $listProjectId ];
			$dictProject = $this->modelProject->find( $q );
		}
		// if( ! $dictProject ) return;

		$q = [];
		$q[] = [ 'id', '=', $listActivityId ];
		$dictActivityAll = $this->modelActivity->find( $q );
?>

<?php if( $dictProject ): ?>

<table>

<thead>
<tr>
	<th>__Project__</th>
	<th class="pw-col-3">__Activity__</th>
	<th role="menu"></th>
</tr>
</thead>

<tbody>
<?php foreach( $dictProject as $project ): ?>
	<?php
	$dictActivity = array_intersect_key( $dictActivityAll, $dictProjectActivity[$project->id] );
	?>
	<?php $headon = 0; ?>
	<?php foreach( $dictActivity as $activity ): ?>
		<tr>
			<?php if( ! $headon ): ?>
				<td title="__Project__" rowspan="<?php echo count($dictActivity); ?>">
					<?php echo $this->pageProjectId->renderTo( $x, $project ); ?>
				</td>
			<?php endif; ?>

			<td title="__Activity__">
				<?php echo $this->pageActivityId->renderTo( $x, $activity ); ?>
			</td>

			<?php if( ! $headon++ ): ?>
				<th role="menu" rowspan="<?php echo count($dictActivity); ?>">
					<nav>
						<a href="URI:.?project=<?php echo $project->id; ?>"><i>..</i><span>__Edit activities__</span></a>
					</nav>
				</th>
			<?php endif; ?>
		</tr>
	<?php endforeach; ?>
<?php endforeach; ?>
</tbody>
</table>

<?php else : ?>

<table>
<tr>
	<td>
		<strong>
			__This worker is not associated with any projects.__ __Add the worker to at least one project to let the worker report their time.__
		</strong>
	</td>
</tr>
</table>

<?php endif; ?>

<?php
	}

	public function renderNew( array $x )
	{
		$worker = $x[ '$worker' ];

		$q = [];
		$q[] = [ 'workerId', '=', $worker->id ];
		$listProjectId = $this->modelActivityProjectWorker->findProp( 'projectId', $q );

		$q = [];
		$q[] = [ 'stateId', '=', 'active' ];
		if( $listProjectId ){
			$q[] = [ 'id', '<>', $listProjectId ];
		}
		$dictProject = $this->modelProject->find( $q );

		if( ! $dictProject ) return;
?>

<p>
__Add this worker to other projects.__
</p>

<table>
<thead>
<tr>
	<th>__Project__</th>
	<th role="menu"></th>
</tr>
</thead>

<tbody class="pw-valign-middle">
<?php foreach( $dictProject as $e ): ?>
<tr>
	<td title="__Project__">
		<?php echo $this->pageProjectId->renderTo( $x, $e ); ?>
	</td>
	<th role="menu">
		<nav>
			<a href="URI:.?project=<?php echo $e->id; ?>"><i>&plus;</i><span>__Add to project__</span></a>
		</nav>
	</th>
</tr>
<?php endforeach; ?>
</tbody>

</table>

<?php
	}
}