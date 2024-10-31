<?php
namespace Plainware\PlainTracker;

class PageTimesheetAudit
{
	public $self = __CLASS__;

	public $t = \Plainware\Time::class;
	public $q = \Plainware\Q::class;

	public $modelAudit = ModelAudit::class;

	public $modelUser = ModelUser::class;
	public $pageUserId = PageUserId::class;

	public $model = ModelTimesheet::class;
	public $pageId = PageTimesheetId::class;

	public $modelActivity = ModelActivity::class;
	public $pageActivityId = PageActivityId::class;

	public $modelProject = ModelProject::class;
	public $pageProjectId = PageProjectId::class;

	public $modelWorker = \Plainware\ModelUser::class;
	public $pageWorkerId = \Plainware\PageUserId::class;

	public $auth = Auth::class;
	public $acl = Acl::class;

	public function can( array $x )
	{
		return $this->acl->isAdmin( $this->auth->getCurrentUserId($x) );
	}

	public function title( array $x )
	{
		$ret = '__History__';
		return $ret;
	}

	public function nav( array $x )
	{
		$ret = [];
		return $ret;
	}

	public function get( array $x )
	{
		$id = $x['id'] ?? null;
		if( ! $id ){
			$x['slug'] = 404;
			return $x;
		}

		$m = $this->model->findById( $id );
		if( ! $m ){
			$x['slug'] = 404;
			return $x;
		}

		$x[ '$m' ] = $m;

		return $x;
	}

	public function render( array $x )
	{
		$ret = [];

		$m = $x[ '$m' ];

		$q = [];
		$q[] = [ 'classId', '=', 'timesheet' ];
		$q[] = [ 'objectId', '=', $m->id ];
		$q[] = [ 'order', 'changeAt', 'DESC' ];
		$q[] = [ 'order', 'id', 'DESC' ];
		$listAudit = $this->modelAudit->find( $q );

		if( $listAudit ){
			$ret['list'] = [ [$this->self, 'renderList'], $x, $listAudit ];
		}
		else {
			$ret['list'] = [ $this->self, 'renderNone' ];
		}

		return $ret;
	}

	public function renderList( array $x, array $listAudit )
	{
		if( ! $listAudit ) return;

	// build the chain of objects
		$m = $x[ '$m' ];

		$listModelChange = [];
		$listModelChange[] = [ null, $m ];

		$m2 = clone $m;
		foreach( $listAudit as $mAudit ){
			foreach( $mAudit->meta as $propName => $valueOld ){
				$m2->{ $propName } = $valueOld;
			}
			$listModelChange[] = [ $mAudit, $m2 ];
			$m2 = clone $m2;
		}
// _print_r( $listModelChange );
// exit;
?>

<?php for( $ii = 1; $ii < count($listModelChange); $ii++ ) : ?>
	<?php
	$mAudit =  $listModelChange[$ii][0];
	$user = $this->modelUser->findById( $mAudit->userId );
	?>
	<section>
		<table>
			<caption><?php echo $this->t->formatFull( $mAudit->changeAt ); ?></caption>
			<thead>
				<tr>
					<th scope="row">__User__</th>
					<td colspan="2"><?php if( $user ): ?><?php echo $this->pageUserId->renderTo( $x, $user ); ?><?php else : ?>__N/A__<?php endif; ?></td>
				</tr>
			</thead>

			<tbody>
				<?php echo $this->self->renderChange( $x, $mAudit, $listModelChange[$ii][1], $listModelChange[$ii-1][1] ); ?>
			</tbody>

			<?php if( $mAudit->description ): ?>
			<tfoot>
				<tr>
					<th scope="row">__Comment__</th>
					<td colspan="2">
						<?php echo esc_html( $mAudit->description ); ?>
					</td>
				</tr>
			</tfoot>
			<?php endif; ?>
		</table>
	</section>
<?php endfor; ?>

<?php
	}

	public function renderChange( array $x, _Audit $mAudit, _Timesheet $m1, _Timesheet $m2 )
	{
		if( $m1->id != $m2->id ) return $this->self->renderChangeId( $x, $mAudit, $m1, $m2 );
		if( $m1->stateId != $m2->stateId ) return $this->self->renderChangeState( $x, $mAudit, $m1, $m2 );
		if( $m1->duration != $m2->duration ) return $this->self->renderChangeDuration( $x, $mAudit, $m1, $m2 );

		$ret = 'changed';
		return $ret;
	}

	public function renderChangeId( array $x, _Audit $mAudit, _Timesheet $m1, _Timesheet $m2 )
	{
?>

<tr>
	<th scope="row">__Status__</th>
	<td colspan="2">
		<?php echo $this->pageId->renderState( $x, $m2->stateId ); ?>
	</td>
</tr>

<?php
	}

	public function renderChangeState( array $x, _Audit $mAudit, _Timesheet $m1, _Timesheet $m2 )
	{
?>

<tr>
	<th scope="row">__Status__</th>
	<td>
		<?php echo $this->pageId->renderState( $x, $m2->stateId ); ?>
	</td>
	<td>
		<del>
			<?php echo $this->pageId->renderState( $x, $m1->stateId ); ?>
		</del>
	</td>
</tr>

<?php
	}

	public function renderChangeDuration( array $x, _Audit $mAudit, _Record $m1, _Record $m2 )
	{
?>

<tr>
	<th scope="row">__Billable hours__</th>
	<td>
		<?php echo $this->t->formatDurationNum( $m2->duration ); ?> (<?php echo $this->t->formatDurationVerbose( $m2->duration ); ?>)
	</td>
	<td>
		<del>
			<?php echo $this->t->formatDurationNum( $m1->duration ); ?> (<?php echo $this->t->formatDurationVerbose( $m1->duration ); ?>)
		</del>
	</td>
</tr>

<?php
	}

	public function renderNone( array $x )
	{
?>
<strong>__No results__</strong>
<?php
	}
}