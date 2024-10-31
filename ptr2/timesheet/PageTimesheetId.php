<?php
namespace Plainware\PlainTracker;

class PageTimesheetId
{
	public $self = __CLASS__;

	public $settingRecord = SettingRecord::class;
	public $modelApp = ModelApp::class;

	public $modelActivityProjectWorker = ModelActivityProjectWorker::class;

	public $modelTimesheet = ModelTimesheet::class;
	public $modelRecord = ModelRecord::class;

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
		$id = $x['id'];

		$id = $x['id'] ?? null;
		$m = $this->modelTimesheet->findById( $id );
		if( $m ){
			$title = $this->t->formatDateRange( $m->startDate, $m->endDate );			
		}
		else {
			$title = '__Timesheet__' . ' #' . $id;
		}
		$ret = $title;
		return $ret;
	}

	public function renderTitle( array $x )
	{
		$id = $x['id'];
		$m = $this->modelTimesheet->findById( $id );

		$dictError = $this->modelTimesheet->findError( [$m->id => $m] );
		$hasError = isset( $dictError[$m->id] ) ? true : false;

		$worker = $this->modelWorker->findById( $m->workerId );
?>

<h1>
	<span>
		<?php if( $hasError ): ?><i>!</i><?php endif; ?><span><?php echo $this->t->formatDateRange( $m->startDate, $m->endDate ); ?></span> <span><?php echo $this->self->renderState( $x, $m->stateId ); ?></span>
	</span>
</h1>

<dl>
	<dt>__Worker__</dt>
	<dd>
		<?php if( $worker ): ?><?php echo $this->pageWorkerId->renderTo( $x, $worker ); ?><?php else : ?>__N/A__<?php endif; ?>
	</dd>
</dl>

<?php
	}

	public function nav( array $x )
	{
		$ret = [];
		// $id = $x['id'];

		$ret[ '11-index' ] = [ '.', '__View timesheet__' ];

		$id = $x['id'] ?? null;
		$p = [ 'id' => $id ];
		$ret[ '89-delete' ] = [ ['.timesheet-delete', $p], '<i>&times;</i><span>__Delete__</span>' ];

		return $ret;
	}

	public function post( array $x )
	{
		$a = $x['a-'] ?? null;

		$m0 = $x[ '$m' ];
		$m = clone $m0;

		if( ! $a ){
			$dictRecordAll = $x[ '$dictRecord' ];

			$m = $x[ '$m' ];
			$d1 = $m->startDate;
			$d2 = $m->endDate;
			$dictDate = $this->t->getDates( $m->startDate, $m->endDate );
			$listProjectActivity = $x[ '$listProjectActivity' ];

			$dictCell = [];
			$toCreate = $toDelete = $toUpdate = [];
			foreach( $dictRecordAll as $record ){
				$cellId = join( '-', [$record->startDate, $record->projectId, $record->activityId] );

				if( isset($dictCell[$cellId]) ){
					$dictCell[ $cellId ]->duration += $record->duration;
					$toDelete[] = $record->id;
					$toUpdate[ $dictCell[$cellId]->id ] = $dictCell[ $cellId ]->duration;
				}
				else {
					$dictCell[ $cellId ] = $record;
				}
			}

			foreach( $dictDate as $d => $dStartEnd ){
				foreach( $listProjectActivity as $projectActivity ){
					list( $project, $activity ) = $projectActivity;
					$cellId = join( '-', [$d, $project->id, $activity->id] );
					$p = 'duration_' . join( '_', [$d, $project->id, $activity->id] );
					$v = $x['post'][$p] ?? 0;

					if( $v ){
						$duration = $v * 60 * 60;
						if( isset($dictCell[$cellId]) ){
							if( $dictCell[$cellId]->duration != $duration ){
								$toUpdate[ $dictCell[$cellId]->id ] = $duration;
							}
						}
						else {
							$record = $this->modelRecord->construct();
							$record->startDate = $d;
							$record->activityId = $activity->id;
							$record->projectId = $project->id;
							$record->workerId = $m->workerId;
							$record->duration = $duration;
							$toCreate[] = $record;
						}
					}
					else {
						if( isset($dictCell[$cellId]) ){
							$toDelete[] = $dictCell[$cellId]->id;
						}
					}
				}
			}

			foreach( $toDelete as $id ){
				$this->modelRecord->delete( $dictRecordAll[$id] );
			}
			foreach( $toCreate as $e ){
				$this->modelRecord->create( $e );
			}
			foreach( $toUpdate as $id => $duration ){
				$e0 = $dictRecordAll[ $id ];
				$e = clone( $e0 );
				$e->duration = $duration;
				$this->modelRecord->update( $e0, $e );
			}

			$x['redirect'] = '.';
		}

		return $x;
	}

	public function get( array $x )
	{
		$id = $x['id'];
		$m = $this->modelTimesheet->findById( $id );
		$x[ '$m' ] = $m;

		$q = [];
		$q[] = [ 'workerId', '=', $m->workerId ];
		$q[] = [ 'startDate', '>=', $m->startDate ];
		$q[] = [ 'startDate', '<=', $m->endDate ];
		$dictRecord = $this->modelRecord->find( $q );
		$x[ '$dictRecord' ] = $dictRecord;

	// project-activity
		$listActivityId = [];
		$dictProjectActivity = [];
		$q = [];
		$q[] = [ 'workerId', '=', $m->workerId ];
		$listActivityProjectWorker = $this->modelActivityProjectWorker->find( $q );
		foreach( $listActivityProjectWorker as $e ){
			$dictProjectActivity[ $e->projectId ][ $e->activityId ] = $e->activityId;
			$listActivityId[ $e->activityId ] = $e->activityId;
		}

	// add of already existing records even if the worker was unlinked from the project/activity
		foreach( $dictRecord as $e ){
			$dictProjectActivity[ $e->projectId ][ $e->activityId ] = $e->activityId;
			$listActivityId[ $e->activityId ] = $e->activityId;
		}

		$q = [];
		$q[] = [ 'id', '=', array_keys($dictProjectActivity) ];
		$q[] = [ 'startDate', '<=', $m->endDate ];
		$q[] = [ 'endDate', '>=', $m->startDate ];
		$dictProject = $this->modelProject->find( $q );

		$q = [];
		$q[] = [ 'id', '=', $listActivityId ];
		$dictActivity = $this->modelActivity->find( $q );

		$listProjectActivity = [];
		foreach( $dictProject as $project ){
			if( ! isset($dictProjectActivity[$project->id]) ) continue;
			foreach( $dictActivity as $activity ){
				if( ! isset($dictProjectActivity[$project->id][$activity->id]) ) continue;
				$listProjectActivity[] = [ $project, $activity ];
			}
		}

		$x[ '$listProjectActivity' ] = $listProjectActivity;

		return $x;
	}

	public function isParent( array $x )
	{
		$ret = true;
		return $ret;
	}

	public function render( array $x )
	{
		$ret = [];

		$a = $x['a-'] ?? null;

		if( null === $a ){
			$ret[ '35-calendar' ] = [ $this->self, 'renderCalendar' ];
		}

		$ret[ '75-total' ] = [ $this->self, 'renderTotal' ];

		return $ret;
	}

	public function renderInfo( array $x )
	{
		$m = $x[ '$m' ];
		$worker = $this->modelWorker->findById( $m->workerId );
?>

<table>

<tr>
	<th scope="row">__Worker__</th>
	<td>
		<?php if( $worker ): ?><?php echo $this->pageWorkerId->renderTo( $x, $worker ); ?><?php else : ?>__N/A__<?php endif; ?>
	</td>
</tr>

</table>

<?php
	}

	public function renderTotal( array $x )
	{
		$dictRecord = $x[ '$dictRecord' ];

		$listProjectId = $listActivityId = [];

		$count = $duration = [];
		$count[0] = 0;
		$duration[0] = 0;

		foreach( $dictRecord as $e ){
			$listProjectId[ $e->projectId ] = $e->projectId;
			$listActivityId[ $e->activityId ] = $e->activityId;

			if( ! isset($count['project-' . $e->projectId]) ) $count[ 'project-' . $e->projectId ] = 0;
			$count[ 'project-' . $e->projectId ]++;

			if( ! isset($count['activity-' . $e->activityId]) ) $count[ 'activity-' . $e->activityId ] = 0;
			$count[ 'activity-' . $e->activityId ]++;

			if( ! isset($duration['project-' . $e->projectId]) ) $duration[ 'project-' . $e->projectId ] = 0;
			$duration[ 'project-' . $e->projectId ] += $e->duration;

			if( ! isset($duration['activity-' . $e->activityId]) ) $duration[ 'activity-' . $e->activityId ] = 0;
			$duration[ 'activity-' . $e->activityId ] += $e->duration;

			$count[0]++;
			$duration[0] += $e->duration;
		}

		if( count($listProjectId) > 1 ){
			$q = [];
			$q[] = [ 'id', '=', $listProjectId ];
			$dictProject = $this->modelProject->find( $q );
		}

		if( count($listActivityId) > 1 ){
			$q = [];
			$q[] = [ 'id', '=', $listActivityId ];
			$dictActivity = $this->modelActivity->find( $q );
		}
?>

<header>
<h3>__Total__</h3>
</header>

<table>

<?php if( ! ((count($listProjectId) > 1) OR (count($listActivityId) > 1)) ): ?>
<thead>
<tr>
	<th></th>
	<th class="pw-col-2 pw-col-align-end">__Hours__</th>
</tr>
</thead>
<?php endif; ?>

<?php if( (count($listProjectId) > 1) OR (count($listActivityId) > 1) ): ?>
<tbody>
	<?php if( count($listProjectId) > 1 ): ?>
		<tr>
			<th>__Project__</th>
			<th class="pw-col-2 pw-col-align-end">__Hours__</th>
		</tr>
		<?php foreach( $dictProject as $e ): ?>
			<tr>
				<td>
					<?php echo $this->pageProjectId->renderTo( $x, $e ); ?>
				</td>
				<td class="pw-col-align-end">
					<?php echo esc_html( $this->t->formatDurationNum($duration['project-'.$e->id]) ); ?>
				</td>
			</tr>
		<?php endforeach; ?>
	<?php endif; ?>

	<?php if( count($listActivityId) > 1 ): ?>
		<tr>
			<th>__Activity__</th>
			<th class="pw-col-2 pw-col-align-end">__Hours__</th>
		</tr>
		<?php foreach( $dictActivity as $e ): ?>
			<tr>
				<td>
					<?php echo $this->pageActivityId->renderTo( $x, $e ); ?>
				</td>
				<td class="pw-col-align-end">
					<?php echo esc_html( $this->t->formatDurationNum($duration['activity-'.$e->id]) ); ?>
				</td>
			</tr>
		<?php endforeach; ?>
	<?php endif; ?>
</tbody>
<?php endif; ?>

<tfoot>
<tr>
	<td>__Grand total__</td>
	<td class="pw-col-align-end"><?php echo esc_html( $this->t->formatDurationNum($duration[0]) ); ?></td>
</tr>
</tfoot>

</table>

<?php
	}

	public function renderCalendar( array $x )
	{
		$m = $x[ '$m' ];

		$listProjectActivity = $x[ '$listProjectActivity' ];

		$canEdit = in_array( $m->stateId, ['draft', 'submit'] ) ? true : false;

		$isPrintView = isset( $x['layout-'] ) && ( 'print' == $x['layout-'] ) ? true : false;
		if( $isPrintView ) $canEdit = false;

		$d1 = $m->startDate;
		$d2 = $m->endDate;

		$dictWkd = $this->t->getFormatWeekdays();
		$weekMatrix = $this->t->getWeekMatrix( $d1, $d2, false );
?>

<header>
<h3>__Time records__</h3>
</header>

<?php if( $canEdit ): ?>
<form method="post">
<?php endif; ?>

<?php if( ! $isPrintView ): ?>
	<section>
	<?php if( 'approve' == $m->stateId ): ?>
		__The timesheet has been approved. You can't add or edit time records.__
	<?php endif; ?>
	<?php if( 'submit' == $m->stateId ): ?>
		__The timesheet is submitted for approval. You can still add and edit time records.__
	<?php endif; ?>
	<?php if( 'draft' == $m->stateId ): ?>
		__You can add and edit time records. When done, submit the timesheet for approval.__
	<?php endif; ?>
	</section>
<?php endif; ?>

<?php foreach( $weekMatrix as $dd1 => $week ): ?>
<?php
$listProjectDisplayed = [];
?>
<section>
<table>
	<thead class="pw-align-center">
		<tr>
			<th scope="row">__Project__ / __Activity__</th>
			<?php foreach( $week as $d ): ?>
				<th>
					<?php if( $d ): ?>
						<?php $wkd = $this->t->getWeekday($d); ?>
						<?php echo $dictWkd[$wkd]; ?>
						<br><small><?php echo $this->t->formatDate( $d ); ?></small>
					<?php endif; ?>
				</th>
			<?php endforeach; ?>
		</tr>
	</thead>

	<tbody class="pw-nowrap">
		<?php foreach( $listProjectActivity as $projectActivity ): ?>
			<?php
			list( $project, $activity ) = $projectActivity;
			?>

			<?php if( ! in_array($project->id, $listProjectDisplayed) ): ?>
				<?php
				$listProjectDisplayed[] = $project->id;
				?>
				<tr>
					<th colspan="<?php echo count($week) + 1; ?>">
						<?php echo $this->pageProjectId->renderTo( $x, $project, false ); ?>
					</th>
				</tr>
			<?php endif; ?>

			<tr class="pw-align-center">
				<th scope="row">
					<?php echo $this->pageActivityId->renderTo( $x, $activity, false ); ?>
				</th>
				<?php foreach( $week as $d ): ?>
					<?php if( $d ): ?>
						<?php echo $this->self->renderDayProjectActivity( $x, $d, $project, $activity ); ?>
					<?php else : ?>
						<td></td>
					<?php endif; ?>
				<?php endforeach; ?>
			</tr>
		<?php endforeach; ?>
	</tbody>
</table>
</section>
<?php endforeach; ?>

<?php if( $canEdit ): ?>
<footer>
<button type="submit">__Save time records__</button>
</footer>

</form>
<?php endif; ?>

<?php
	}

	public function renderDayProjectActivity( array $x, $d, _Project $project, _Activity $activity )
	{
		$timesheet = $x['$m'];
		$canEdit = in_array( $timesheet->stateId, ['draft', 'submit'] ) ? true : false;

		if( ($d < $project->startDate) OR ($d > $project->endDate) ){
			$canEdit = false;
		}

		$isPrintView = isset( $x['layout-'] ) && ( 'print' == $x['layout-'] ) ? true : false;
		if( $isPrintView ) $canEdit = false;

		$dictRecordAll = $x[ '$dictRecord' ];

		$duration = 0;
		foreach( $dictRecordAll as $e ){
			if( $e->startDate != $d ) continue;
			if( $e->projectId != $project->id ) continue;
			if( $e->activityId != $activity->id ) continue;
			$duration += $e->duration;
		}
		$v = ( $duration / (60 * 60) );

		$p = 'duration_' . join( '_', [$d, $project->id, $activity->id] );
?>

<?php if( $canEdit ): ?>
<td role="menu">
<input value="<?php if( $v ){ echo esc_attr($v); } ?>" type="number" name="<?php echo esc_attr($p); ?>" step="0.25" min="0" max="24" style="width: 100%;">
</td>
<?php else: ?>
<td>
<?php if( $duration ): ?><?php echo $this->t->formatDurationNum( $duration ); ?><?php else : ?>--<?php endif; ?>
</td>
<?php endif; ?>

<?php
	}

	public function renderStateText( array $x, $stateId )
	{
		$ret = [];

		$ret[ 'draft' ] = '__Draft__';
		$ret[ 'submit' ] = '__Submitted__';
		$ret[ 'approve' ] = '__Approved__';
		$ret[ 'process' ] = '__Processed__';

		$ret = isset( $ret[$stateId] ) ? $ret[$stateId] : '__N/A__';
		return $ret;
	}

	public function renderState( array $x, $stateId, $textLabel = '' )
	{
		// $bgcolor = '#900';
		// $color = '#fff';
		$bgcolor = '#f99';
		$color = '#000';

		$stateTextLabel = $this->self->renderStateText( $x, $stateId );
		if( ! strlen($textLabel) ) $textLabel = $stateTextLabel;

		if( 'draft' == $stateId ){
			$bgcolor = '#ccc';
			// $color = null;
			$color = '#000';
		}
		if( 'submit' == $stateId ){
			$bgcolor = '#ff9';
			// $color = null;
			$color = '#000';
		}
		if( 'approve' == $stateId ){
			$bgcolor = '#cf9';
			// $color = null;
			$color = '#000';
		}
		if( 'process' == $stateId ){
			$bgcolor = '#c9f';
			// $color = null;
			$color = '#000';
		}
?>
<span title="<?php echo esc_attr($stateTextLabel); ?>" style="padding: 0 .25em; border-radius: .25em; background-color: <?= esc_attr($bgcolor); ?>;<?php if( $color ): ?> color: <?= esc_attr($color); ?>;<?php endif; ?>"><?php echo $textLabel; ?></span>
<?php
	}
}