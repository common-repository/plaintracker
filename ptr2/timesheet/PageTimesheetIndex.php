<?php
namespace Plainware\PlainTracker;

class PageTimesheetIndex
{
	public $self = __CLASS__;

	public $modelApp = ModelApp::class;

	public $modelRecord = ModelRecord::class;

	public $modelWorker = ModelWorker::class;
	public $pageWorkerId = PageWorkerId::class;

	public $modelTimesheet = ModelTimesheet::class;
	public $pageTimesheetId = PageTimesheetId::class;

	public $q = \Plainware\Q::class;
	public $htmlPager = \Plainware\HtmlPager::class;
	public $t = \Plainware\Time::class;

	public $auth = Auth::class;
	public $acl = Acl::class;

	public function can( array $x )
	{
		return $this->acl->isAdmin( $this->auth->getCurrentUserId($x) );
	}

	public function title( array $x )
	{
		$ret = '__Timesheets__';
		return $ret;
	}

	public function nav( array $x )
	{
		$ret = [];

		$workerId = $x['worker'] ?? null;
		if( $workerId ){
			$p = [ 'worker' => $workerId ];
			$ret[ '21-new' ] = [ ['.timesheet-new', $p], '<i>&plus;</i><span>__Add new__</span>' ];
		}

		return $ret;
	}

	public function post( array $x )
	{
		return $x;
	}

	public function get( array $x )
	{
		$q = [];

		if( isset($x['worker']) ){
			$q[] = [ 'workerId', '=', $x['worker'] ];
		}
		// if( isset($x['project']) ){
			// $q[] = [ 'projectId', '=', $x['project'] ];
		// }

		$x[ '$q' ] = $q;

		return $x;
	}

	public function render( array $x )
	{
		$ret = [];

		$q = $x[ '$q' ];
		$count = $this->modelTimesheet->count( $q );

		if( $count ){
			$limit = 10;
			list( $limit, $offset ) = $this->htmlPager->getLimitOffset( $x, ['limit' => $limit] );
			if( $limit && ($limit < $count) ){
				$q[] = [ 'limit', $limit ];
				$q[] = [ 'offset', $offset ];
				$ret[ '34-pager' ] = $this->htmlPager->render( $x, $count, ['limit' => $limit, 'offset' => $offset] );
			}
			$ret[ '33-list' ] = [ [$this->self, 'renderList'], $x, $q ];
		}
		else {
			$ret[ '33-list' ] = [ $this->self, 'renderNone' ];
		}

		return $ret;
	}

	public function renderList( array $x, array $q )
	{
		$isPrintView = isset( $x['layout-'] ) && ( 'print' == $x['layout-'] ) ? true : false;

		$q[] = [ 'order', 'startDate', 'DESC' ];
		$q[] = [ 'order', 'id', 'ASC' ];
		$dictTimesheet = $this->modelTimesheet->find( $q );

		$listWorkerId = [];
		foreach( $dictTimesheet as $timesheet ){
			$listWorkerId[ $timesheet->workerId ] = $timesheet->workerId;
		}

		$q2 = [];
		$q2[] = [ 'id', '=', $listWorkerId ];
		$repoWorker = $this->modelWorker->find( $q2 );
?>

<table class="pw-responsive">
<thead class="pw-nowrap">
	<tr>
		<th class="pw-col-icon"><small>__ID__</small></th>
		<th class="pw-col-2">__Status__</th>
		<th>__Worker__</th>
		<th class="_pw-col-3">__Pay period__</th>
		<th class="_pw-col-1 pw-col-align-end">__Hours__</th>
		<?php if( ! $isPrintView ): ?>
			<th role="menu"></th>
		<?php endif; ?>
	</tr>
</thead>

<tbody class="pw-valign-middle">
<?php foreach( $dictTimesheet as $timesheet ): ?>
	<?php
	$worker = $repoWorker[ $timesheet->workerId ] ?? null;

	$q2 = [];
	$q2[] = [ 'workerId', '=', $timesheet->workerId ];
	$q2[] = [ 'startDate', '>=', $timesheet->startDate ];
	$q2[] = [ 'startDate', '<=', $timesheet->endDate ];
	$dictRecord = $this->modelRecord->find( $q2 );
// _print_r( $dictRecord );
	$duration = 0;
	foreach( $dictRecord as $record ){
		$duration += $record->duration;
	}
	?>
	<tr class="pw-nowrap">
		<td class="pw-col-icon" title="__ID__">
			<small><?php echo esc_html( $timesheet->id ); ?></small>
		</td>

		<td title="__Status__">
			<?php echo $this->pageTimesheetId->renderState( $x, $timesheet->stateId ); ?>
		</td>

		<td title="__Worker__">
			<?php if( $worker ): ?><?php echo $this->pageWorkerId->renderTo( $x, $worker ); ?><?php else : ?>__N/A__<?php endif; ?>
		</td>

		<td title="__Pay period__">
			<?php echo $this->t->formatDateRange( $timesheet->startDate, $timesheet->endDate ); ?>
		</td>

		<td title="__Hours__" class="pw-col-align-end">
			<?php echo $this->t->formatDurationNum( $duration ); ?>
		</td>

		<?php if( ! $isPrintView ): ?>
			<th role="menu">
				<nav>
					<a href="URI:.timesheet-id?id=<?php echo esc_attr($timesheet->id); ?>" title="__View timesheet__">
						<i>&raquo;</i><span>__View timesheet__</span>
					</a>
				</nav>
			</th>
		<?php endif; ?>
	</tr>
<?php endforeach; ?>
</tbody>

</table>

<?php
	}

	public function renderNone( array $x )
	{
?>
__No results__
<?php
	}
}