<?php
namespace Plainware\PlainTracker;

class PageTimesheetDashboard
{
	public $self = __CLASS__;

	public $modelApp = ModelApp::class;

	public $modelProject = ModelProject::class;
	public $pageProjectId = PageProjectId::class;

	public $settingTimesheet = SettingTimesheet::class;

	public $modelRecord = ModelRecord::class;

	public $modelWorker = ModelWorker::class;
	public $pageWorkerId = PageWorkerId::class;

	public $modelTimesheet = ModelTimesheet::class;
	public $pageTimesheetId = PageTimesheetId::class;

	public $inputDate = \Plainware\HtmlInputDateJs::class;

	public $t = \Plainware\Time::class;

	public $auth = Auth::class;
	public $acl = Acl::class;

	public function can( array $x )
	{
		return $this->acl->isAdmin( $this->auth->getCurrentUserId($x) );
	}

	public function title( array $x )
	{
		$ret = '__Timesheet summary__';
		return $ret;
	}

	public function nav( array $x )
	{
		$ret = [];
		return $ret;
	}

	public function post( array $x )
	{
		$cal = $this->inputDate->grab( 'cal', $x['post'] );

		$p = [ 'cal' => $cal ];
		$x['redirect'] = [ '.', $p ];

		return $x;
	}

	public function get( array $x )
	{
		$cal = $x['cal'] ?? $this->t->getDate( $this->t->getNow() );
		$x['cal'] = $cal;

		$qWorker = [];
		$x[ '$qWorker' ] = $qWorker;

		return $x;
	}

	public function render( array $x )
	{
		$ret = [];
		$ret[ '22-date' ] = [ $this->self, 'renderDateSelect' ];
		$ret[ '43-main' ] = [ $this->self, 'renderPeriod' ];
		return $ret;
	}

	public function renderPeriod( array $x )
	{
		$cal = $x['cal'];

		$qWorker = $x[ '$qWorker' ];
		$dictWorker = $this->modelWorker->find( $qWorker );

		$q = [];
		$q[] = [ 'workerId', '=', array_keys($dictWorker) ];
		$q[] = [ 'startDate', '<=', $cal ];
		$q[] = [ 'endDate', '>=', $cal ];
		$listTimesheet = $this->modelTimesheet->find( $q );

		$dictWorkerTimesheet = [];
		foreach( $listTimesheet as $timesheet ){
			$dictWorkerTimesheet[ $timesheet->workerId ] = $timesheet;
		}

		$isPrintView = isset( $x['layout-'] ) && ( 'print' == $x['layout-'] ) ? true : false;
?>

<table>
<thead class="pw-nowrap">
	<tr>
		<th class="pw-col-2">__Timesheet status__</th>
		<th>__Worker__</th>
		<th class="pw-col-3">__Pay period__</th>
		<th class="pw-col-1 pw-col-align-end">__Hours__</th>
		<?php if( ! $isPrintView ): ?>
			<th role="menu"></th>
		<?php endif; ?>
	</tr>
</thead>

<tbody class="pw-valign-middle">
<?php foreach( $dictWorker as $worker ): ?>
	<?php
	list( $d1, $d2 ) = $this->settingTimesheet->getPayPeriod( $cal, $worker->id ); 
	$timesheet = $dictWorkerTimesheet[ $worker->id ] ?? null;

	if( $timesheet ){
		$q2 = [];
		$q2[] = [ 'workerId', '=', $timesheet->workerId ];
		$q2[] = [ 'startDate', '>=', $timesheet->startDate ];
		$q2[] = [ 'startDate', '<=', $timesheet->endDate ];
		$listDuration = $this->modelRecord->findProp( ['id', 'duration'], $q2 );
		$duration = 0;
		foreach( $listDuration as $e ){
			$duration += $e['duration'];
		}
	}
	?>
	<tr>
		<td title="__Timesheet status__">
			<?php if( $timesheet ): ?>
				<?php echo $this->pageTimesheetId->renderState( $x, $timesheet->stateId ); ?>
			<?php else: ?>
				<?php echo $this->pageTimesheetId->renderState( $x, 'na' ); ?>
			<?php endif; ?>
		</td>

		<td title="__Worker__">
			<?php echo $this->pageWorkerId->renderTo( $x, $worker ); ?>
		</td>

		<td title="__Pay period__">
			<?php echo $this->t->formatDateRange( $d1, $d2 ); ?>
		</td>

		<td title="__Hours__" class="pw-col-align-end">
			<?php if( $timesheet ): ?>
				<?php echo $this->t->formatDurationNum( $duration ); ?>
			<?php else : ?>
				--
			<?php endif; ?>
		</td>

		<?php if( ! $isPrintView ): ?>
			<th role="menu">
				<nav>
					<?php if( $timesheet ): ?>
						<a href="URI:.timesheet-id?id=<?php echo esc_attr($timesheet->id); ?>" title="__View timesheet__"><i>&raquo;</i><span>__View timesheet__</span></a>
					<?php else: ?>
						<a href="URI:.timesheet-new?d1=<?php echo esc_attr($d1); ?>&worker=<?php echo esc_attr($worker->id); ?>" title="__Create timesheet__"><i>&plus;</i><span>__Create timesheet__</span></a>
					<?php endif; ?>
				</nav>
			</th>
		<?php endif; ?>
	</tr>
<?php endforeach; ?>
</tbody>

</table>

<?php
	}

	public function renderDateSelect( array $x )
	{
		$v = $x['cal'];
		$isPrintView = isset( $x['layout-'] ) && ( 'print' == $x['layout-'] ) ? true : false;
?>

<?php if( $isPrintView ): ?>

	<table>
		<tr>
			<th scope="row">__Date__</th>
			<td>
				<?php echo $this->t->formatDateFull( $v ); ?>
			</td>
		</tr>
	</table>

<?php else : ?>

	<form method="post">
		<ul>
			<li>__Date__</li>
			<li>
				<?php echo $this->inputDate->render( 'cal', $v ); ?>
			</li>
			<li>
				<button>__Go to selected date__</button>
			</li>
		</ul>
	</form>

<?php endif; ?>

<?php
	}
}