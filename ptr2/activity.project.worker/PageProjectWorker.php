<?php
namespace Plainware\PlainTracker;

class PageProjectWorker
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
		$ret = '__Project workers__';
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

		$p = [ 'worker' => null ];
		$x['redirect'] = [ '.', $p ];

		return $x;
	}

	public function get( array $x )
	{
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

		$worker = null;
		$workerId = $x['worker'] ?? null;
		if( $workerId ){
			$worker = $this->modelWorker->findById( $workerId );
		}
		$x[ '$worker' ] = $worker;

		return $x;
	}

	public function render( array $x )
	{
		$ret = [];

		$worker = $x[ '$worker' ];
		if( $worker ){
			$ret[ '53-worker' ] = [ $this->self, 'renderWorker' ];
		}
		else {
			$ret[ '53-list' ] = [ $this->self, 'renderList' ];
			$ret[ '54-new' ] = [ $this->self, 'renderNew' ];
		}

		return $ret;
	}

	public function renderWorker( array $x )
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
			<th scope="row">__Project__</th>
			<td>
				<?php echo $this->pageProjectId->renderTo( $x, $project ); ?>
			</td>
		</tr>
		<tr>
			<th scope="row">__Worker__</th>
			<td>
				<?php echo $this->pageWorkerId->renderTo( $x, $worker ); ?>
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
	<button type="submit">__Save__</button>
</footer>

</form>

<?php
	}

	public function renderList( array $x )
	{
		$project = $x[ '$project' ];

		$q = [];
		$q[] = [ 'projectId', '=', $project->id ];

		$dictWorkerActivity = $listActivityId = [];
		$res = $this->modelActivityProjectWorker->find( $q );
		foreach( $res as $e ){
			$dictWorkerActivity[ $e->workerId ][ $e->activityId ] = $e->activityId;
			$listActivityId[ $e->activityId ] = $e->activityId;
		}

		$listWorkerId = array_keys( $dictWorkerActivity );
		if( $listWorkerId ){
			$q = [];
			$q[] = [ 'id', '=', $listWorkerId ];
			$dictWorker = $this->modelWorker->find( $q );
		}
		else {
			$dictWorker = [];
		}

		// if( ! $dictWorker ) return;

		$q = [];
		$q[] = [ 'id', '=', $listActivityId ];
		$dictActivityAll = $this->modelActivity->find( $q );
?>

<?php if( $dictWorker ): ?>

<p>
__The list of workers in the project.__
</p>

<table>
<thead>
<tr>
	<th>__Worker__</th>
	<th class="pw-col-3">__Activity__</th>
	<th role="menu"></th>
</tr>
</thead>

<tbody>
<?php foreach( $dictWorker as $worker ): ?>
	<?php
	$dictActivity = array_intersect_key( $dictActivityAll, $dictWorkerActivity[$worker->id] );
	?>
	<?php $headon = 0; ?>
	<?php foreach( $dictActivity as $activity ): ?>
		<tr>
			<?php if( ! $headon ): ?>
				<td title="__Worker__" rowspan="<?php echo count($dictActivity); ?>">
					<?php echo $this->pageWorkerId->renderTo( $x, $worker ); ?>
				</td>
			<?php endif; ?>

			<td title="__Activity__">
				<?php echo $this->pageActivityId->renderTo( $x, $activity ); ?>
			</td>

			<?php if( ! $headon++ ): ?>
				<th role="menu" rowspan="<?php echo count($dictActivity); ?>">
					<nav>
						<a href="URI:.?worker=<?php echo $worker->id; ?>"><i>..</i><span>__Edit activities__</span></a>
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
<strong>__There are no workers in this project.__</strong>
</td>
</tr>
</table>

<?php endif; ?>

<?php
	}

	public function renderNew( array $x )
	{
		$project = $x[ '$project' ];

		$q = [];
		$q[] = [ 'projectId', '=', $project->id ];
		$listWorkerId = $this->modelActivityProjectWorker->findProp( 'workerId', $q );

		$q = [];
		$q[] = [ 'stateId', '=', 'active' ];
		if( $listWorkerId ){
			$q[] = [ 'id', '<>', $listWorkerId ];
		}
		$dictWorker = $this->modelWorker->find( $q );

		if( ! $dictWorker ) return;
?>

<header>
<h3>__Add worker__</h3>
</header>

<p>
__Add a new worker to the project.__
</p>

<table>

<thead>
<tr>
	<th>__Worker__</th>
	<th role="menu"></th>
</tr>
</thead>

<tbody class="pw-valign-middle">
<?php foreach( $dictWorker as $e ): ?>
<tr>
	<td>
		<?php echo $this->pageWorkerId->renderTo( $x, $e ); ?>
	</td>
	<th role="menu">
		<nav>
			<a href="URI:.?worker=<?php echo $e->id; ?>"><i>&plus;</i><span>__Add worker__</span></a>
		</nav>
	</th>
</tr>
<?php endforeach; ?>
</tbody>

</table>

<?php
	}
}