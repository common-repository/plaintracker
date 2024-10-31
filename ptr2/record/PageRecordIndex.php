<?php
namespace Plainware\PlainTracker;

class PageRecordIndex
{
	public $self = __CLASS__;

	public $modelRecord = ModelRecord::class;
	public $presenter = PresenterRecord::class;

	public $modelActivity = ModelActivity::class;
	public $pageActivityId = PageActivityId::class;

	public $modelWorker = ModelWorker::class;
	public $pageWorkerId = PageWorkerId::class;

	public $modelProject = ModelProject::class;
	public $pageProjectId = PageProjectId::class;

	public $t = \Plainware\Time::class;

	public $auth = Auth::class;
	public $acl = Acl::class;

	public function can( array $x )
	{
		return $this->acl->isAdmin( $this->auth->getCurrentUserId($x) );
	}

	public function title( array $x )
	{
		$isPrintView = isset( $x['layout-'] ) && ( 'print' == $x['layout-'] ) ? true : false;

		$ret = '';
		if( $isPrintView ){
			$project = $x['$project'] ?? null;
			if( $project && $project->startDate ){
				$ret = '';
			}
			else {
				$d1 = $x['d1'];
				$d2 = $x['d2'];
				$title = $this->t->formatDateRange( $d1, $d2 );
				$ret = $title;
			}

		}
		else {
			$ret = '__Time records__';
		}

		return $ret;
	}

	public function nav( array $x )
	{
		$ret = [];

		$p = [];
		$iknow = $x['iknow'] ?? [];

		if( isset($x['activity']) ){
			$p['activity'] = $x['activity'];
			$iknow[] = 'activity';
		}

		if( isset($x['project']) ){
			$p['project'] = $x['project'];
			$iknow[] = 'project';
		}

		if( isset($x['worker']) ){
			$p['worker'] = $x['worker'];
			$iknow[] = 'worker';
		}

		$iknow = array_unique( $iknow );
		if( $iknow ) $p['iknow'] = $iknow;

		$d = $x['d1'] ?? null;
		if( $d ){
			$p['cal'] = $d;
		}

		$p = [ 'layout-' => 'print', 'target' => 'blank' ];
		$ret[ '85-print' ] = [ ['.', $p], '<span>__Print view__</span><i>&nearr;</i>' ];

		return $ret;
	}

	public function post( array $x )
	{
		return $x;
	}

	public function get( array $x )
	{
		$d1 = $x['d1'] ?? null;
		$d2 = $x['d2'] ?? null;

	// range
		$r = $x['r'] ?? ( ((null !== $d1) && (null !== $d2)) ? 'custom' : '1-week' );
// echo "R = '$r'<br>";
// exit;
		$x['r'] = $r;
		$rText = str_replace( '-', ' ', $r );

		$g = $x['g'] ?? null;
		$iknow = $x['iknow'] ?? [];
		if( (null == $g) OR in_array($g, $iknow) ){
			$g = 'date';
		}
		$x['g'] = $g;

	// query
		$q = [];

		if( isset($x['activity']) ){
			$q[] = [ 'activityId', '=', $x['activity'] ];
		}
		if( isset($x['project']) ){
			$q[] = [ 'projectId', '=', $x['project'] ];
		}
		if( isset($x['worker']) ){
			$q[] = [ 'workerId', '=', $x['worker'] ];
		}

		// $qState = $x['state'] ?? [ 'submit', 'approve' ];
		// $q[] = [ 'stateId', '=', $qState ];

		if( isset($x['state']) ){
			$q[] = [ 'stateId', '=', $x['state'] ];
		}

		$d1 = $x['d1'] ?? null;
		if( (! $d1) && ('custom' != $r) ){
			$now = $this->t->getNow();
			$today = $this->t->getDate( $now );

			$q2 = $q;
			$q2[] = [ 'limit', 1 ];
			$q2[] = [ 'order', 'startDate', 'DESC' ];

			$res = $this->modelRecord->findProp( 'startDate', $q2 );
			if( $res ){
				$d1 = current( $res );
			}
			else {
				$d1 = $this->t->getDate( $now );
			}

		// determine range
			if( 'custom' !== $r ){
				list( $rQty, $rRange ) = explode( '-', $r );
				if( $rQty > 1 ){
					$d1 = $this->t->modify( $d1, '-' . ($rQty-1) . ' ' . $rRange );
				}
				if( 'week' == $rRange ) $d1 = $this->t->getStartWeek( $d1 );
				if( 'month' == $rRange ) $d1 = $this->t->getStartMonth( $d1 );
				$d1 = $this->t->getDate( $d1 );
			}
		}
		$x['d1'] = $d1;

		if( null === $d2 ){
			$d2 = $this->t->modify( $d1, '+' . $rText );
			$d2 = $this->t->getDate( $d2 );
			$d2 = $this->t->getPrevDate( $d2 );
		}
		$x['d2'] = $d2;

	// more query
		if( $d1 ){
			$q[] = [ 'startDate', '>=', $d1 ];
		}
		if( $d2 ){
			$q[] = [ 'startDate', '<=', $d2 ];
		}
		$x['$q'] = $q;

		return $x;
	}

	public function render( array $x )
	{
		$ret = [];

		$q = $x['$q'];

	// have at all?
		$q2 = $q;
		$q2[] = [ 'limit', 1 ];
		$res = $this->modelRecord->findProp( 'id', $q2 );
		if( $res ){
			$ret[ '41-list' ] = [ $this->self, 'renderList' ];
			$ret[ '42-total' ] = [ $this->self, 'renderTotal' ];
		}
		else {
			$ret[ '41-list-none' ] = [ $this->self, 'renderNone' ];
		}

		return $ret;
	}

	public function renderList( array $x )
	{
		$q = $x['$q'];
		$iknow = $x['iknow'] ?? [];

		$dictRecord = $this->modelRecord->find( $q );

		$listActivityId = $listWorkerId = $listProjectId = [];
		foreach( $dictRecord as $m ){
			$listActivityId[ $m->activityId ] = $m->activityId;
			$listWorkerId[ $m->workerId ] = $m->workerId;
			$listProjectId[ $m->projectId ] = $m->projectId;
		}

		$q2 = [];
		$q2[] = [ 'id', '=', $listActivityId ];
		$dictActivity = $this->modelActivity->find( $q2 );
		$x['$dictActivity'] = $dictActivity;

		$q2 = [];
		$q2[] = [ 'id', '=', $listWorkerId ];
		$dictWorker = $this->modelWorker->find( $q2 );
		$x['$dictWorker'] = $dictWorker;

		$q2 = [];
		$q2[] = [ 'id', '=', $listProjectId ];
		$dictProject = $this->modelProject->find( $q2 );
		$x['$dictProject'] = $dictProject;

		$g = $x['g'];

		$splitPage = false;
		if( $g && ('none' != $g) ){
			$iknow[] = $g;

			$dictGroup = [];
			if( 'activity' == $g ){
				$dictGroup = $dictActivity;
			}
			if( 'worker' == $g ){
				$dictGroup = $dictWorker;
			}
			if( 'project' == $g ){
				$dictGroup = $dictProject;
			}

			$dictGroupRecord = [];
			foreach( $dictRecord as $m ){
				$gid = null;

				if( 'activity' == $g ){
					$gid = $m->activityId;
				}
				if( 'worker' == $g ){
					$gid = $m->workerId;
				}
				if( 'project' == $g ){
					$gid = $m->projectId;
				}
				if( 'date' == $g ){
					$gid = $m->startDate;
					if( ! isset($dictGroup[$gid]) ) $dictGroup[$gid] = $gid;
				}

				if( null === $gid ) continue;

				if( ! isset($dictGroupRecord[$gid]) ) $dictGroupRecord[$gid] = [];
				$dictGroupRecord[ $gid ][ $m->id ] = $m;
			}
		}

		$isPrintView = isset( $x['layout-'] ) && ( 'print' == $x['layout-'] ) ? true : false;
		$splitPrintPage = false;
		if( $isPrintView ){
			$splitPrintPage = ( 'date' == $g ) ? false : true;
			if( $splitPrintPage ){
				$textHeader = $this->self->title( $x );
				$textHeader = current( $textHeader );
			}
		}
?>

<?php if( $g && ('none' != $g) ): ?>

	<?php foreach( $dictGroup as $gid => $group ): ?>

		<?php if( $splitPrintPage ): ?>
			<h1 class="pw-print"><?php echo esc_html( $textHeader ); ?></h1>
		<?php endif; ?>

		<section>
			<table>
				<caption>
					<?php if( 'activity' == $g ): ?><?php echo $this->pageActivityId->renderTo( $x, $group, false ); ?><?php endif; ?>
					<?php if( 'project' == $g ): ?><?php echo $this->pageProjectId->renderTo( $x, $group, false ); ?><?php endif; ?>
					<?php if( 'worker' == $g ): ?><?php echo $this->pageWorkerId->renderTo( $x, $group, false ); ?><?php endif; ?>
					<?php if( 'date' == $g ): ?><?php echo $this->t->formatDateFull( $group ); ?><?php endif; ?>
				</caption>
				<?php echo $this->self->renderList_( $x, $dictGroupRecord[$gid], $iknow ); ?>
			</table>
		</section>

		<?php if( $splitPrintPage ): ?>
			<br class="pw-print" style="break-after: page;">
		<?php endif; ?>

	<?php endforeach; ?>

<?php else : ?>

	<table>
		<?php echo $this->self->renderList_( $x, $dictRecord, $iknow ); ?>
	</table>

<?php endif; ?>

<?php
	}

	public function renderTotal( array $x )
	{
		$q = $x['$q'];
		$count = $this->modelRecord->count( $q );

		$totalDuration = 0;
		$listDuration = $this->modelRecord->findProp( ['id', 'duration'], $q );
		foreach( $listDuration as $e ){
			$totalDuration += $e['duration'];
		}

		$g = $x['g'] ?? null;
		$isPrintView = isset( $x['layout-'] ) && ( 'print' == $x['layout-'] ) ? true : false;
		$splitPrintPage = false;
		if( $isPrintView ){
			$splitPrintPage = ( 'date' == $g ) ? false : true;
			if( $splitPrintPage ){
				$textHeader = $this->self->title( $x );
				$textHeader = current( $textHeader );
			}
		}
?>

<?php if( $splitPrintPage ): ?>
	<h1 class="pw-print"><?php echo esc_html( $textHeader ); ?></h1>
<?php endif; ?>

<table>
	<tbody class="pw-nowrap">
		<tr>
			<th>__Grand total__</th>
			<td class="pw-col-1 pw-col-align-end">
				<?php echo esc_html( $count ); ?>
			</td>
			<td class="pw-col-1 pw-col-align-end">
				<?php echo $this->t->formatDurationNum( $totalDuration ); ?>
			</td>
		</tr>
	</tbody>
</table>

<?php if( $splitPrintPage ): ?>
	<br class="pw-print" style="break-after: page;">
<?php endif; ?>

<?php
	}

	public function renderList_( array $x, array $dict, array $iknow )
	{
		reset( $dict );
		$totalDuration = 0;
		foreach( $dict as $m ){
			$totalDuration += $m->duration;
		}
		$totalCount = count( $dict );
		reset( $dict );

		$select = $x[ '$select' ] ?? [];
		$g = $x['g'] ?? null;
?>

<thead class="pw-nowrap">
<tr>
	<?php if( $select ): ?>
		<th scope="row">__Select__</th>
	<?php endif; ?>

	<?php if( ! in_array('date', $iknow) ): ?>
		<th<?php if( ! (in_array('worker', $iknow) && in_array('activity', $iknow) && in_array('project', $iknow)) ): ?> class="pw-col-2"<?php endif; ?>>__Date__</th>
	<?php endif; ?>

	<?php if( ! in_array('project', $iknow) ): ?>
		<th<?php if( ! (in_array('worker', $iknow) && in_array('activity', $iknow)) ): ?> class="pw-col-3"<?php endif; ?>>__Project__</th>
	<?php endif; ?>

	<?php if( ! in_array('activity', $iknow) ): ?>
		<th<?php if( ! (in_array('worker', $iknow) && in_array('project', $iknow)) ): ?> class="pw-col-3"<?php endif; ?>>__Activity__</th>
	<?php endif; ?>

	<?php if( ! in_array('worker', $iknow) ): ?>
		<th>__Worker__</th>
	<?php endif; ?>

	<th class="pw-col-1">__Status__</th>
	<th class="pw-col-1 pw-col-align-end">__Hours__</th>
</tr>
</thead>

<tbody class="pw-nowrap">
<?php foreach( $dict as $m ): ?>
	<?php echo $this->self->renderListOne_( $x, $m, $iknow ); ?>
<?php endforeach; ?>
</tbody>

<?php if( $g && ! in_array($g, ['none']) ): ?>
<tfoot class="pw-nowrap">
	<tr>
		<th colspan="<?php echo (4 + ($select ? 1 : 0) - count($iknow)); ?>">__Total__</th>
		<th class="pw-col-align-end">
			<?php echo esc_html($totalCount); ?>
		</th>
		<th class="pw-col-align-end">
			<?php echo $this->t->formatDurationNum( $totalDuration ); ?>
		</th>
	</tr>
</tfoot>
<?php endif; ?>

<?php
	}

	public function renderListOne_( array $x, _Record $m, array $iknow )
	{
		$dictActivity = $x[ '$dictActivity' ] ?? [];
		$dictProject = $x[ '$dictProject' ] ?? [];
		$dictWorker = $x[ '$dictWorker' ] ?? [];

		$activity = $dictActivity[ $m->activityId ] ?? $this->modelActivity->findById( $m->activityId );
		$project = $dictProject[ $m->projectId ] ?? $this->modelProject->findById( $m->projectId );
		$worker = $dictWorker[ $m->workerId ] ?? $this->modelWorker->findById( $m->workerId );
?>

<tr>
	<?php if( ! in_array('date', $iknow) ): ?>
		<td title="__Date__">
			<?= $this->t->formatDateFull( $m->startDate ); ?>
		</td>
	<?php endif; ?>

	<?php if( ! in_array('project', $iknow) ): ?>
		<td title="__Project__">
			<?php if( $project ): ?><?= $this->pageProjectId->renderTo( $x, $project ); ?><?php else : ?>__N/A__<?php endif; ?>
		</td>
	<?php endif; ?>

	<?php if( ! in_array('activity', $iknow) ): ?>
		<td title="__Activity__">
			<?php if( $activity ): ?><?= $this->pageActivityId->renderTo( $x, $activity ); ?><?php else : ?>__N/A__<?php endif; ?>
		</td>
	<?php endif; ?>

	<?php if( ! in_array('worker', $iknow) ): ?>
		<td title="__Worker__">
			<?php if( $worker ): ?><?= $this->pageWorkerId->renderTo( $x, $worker ); ?><?php else : ?>__N/A__<?php endif; ?>
		</td>
	<?php endif; ?>

	<td title="__Status__">
		<?php echo $this->presenter->htmlState( $x, $m->stateId ); ?>
	</td>

	<?php
	$canEdit = in_array( $m->stateId, ['draft', 'submit'] ) ? true : false;
	?>
	<td title="__Hours__" class="pw-col-align-end">
		<?php if( $canEdit ): ?><a href="URI:.record-edit?id=<?php echo esc_attr($m->id); ?>"><?php endif; ?>
			<?php echo $this->t->formatDurationNum( $m->duration ); ?></a>
		<?php if( $canEdit ): ?></a><?php endif; ?>
	</td>
</tr>

<?php
	}

	public function renderNone( array $x )
	{
?>
__No results__
<?php
	}
}