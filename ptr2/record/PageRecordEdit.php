<?php
namespace Plainware\PlainTracker;

class PageRecordEdit
{
	public $self = __CLASS__;

	public $modelApp = ModelApp::class;

	public $modelRecord = ModelRecord::class;
	public $modelTimesheet = ModelTimesheet::class;

	public $modelActivity = ModelActivity::class;
	public $pageActivityId = PageActivityId::class;

	public $modelWorker = ModelWorker::class;
	public $pageWorkerId = PageWorkerId::class;

	public $modelActivityProjectWorker = ModelActivityProjectWorker::class;

	public $inputDuration = \Plainware\HtmlInputDuration::class;
	public $t = \Plainware\Time::class;

	public $auth = Auth::class;
	public $acl = Acl::class;

	public function can( array $x )
	{
		$ret = false;

		$recordId = $x['id'];
		$q = $this->modelApp->queryTimesheetByRecord( $recordId );
		$res = $this->modelTimesheet->find( $q );
		$timesheet = current( $res );
		if( ! $timesheet ){
			return $ret;
		}

	// can edit for draft and submit only
		if( ! in_array($timesheet->stateId, ['draft', 'submit']) ){
			return $ret;
		}

		$userId = $this->auth->getCurrentUserId( $x );
		if( $this->acl->isAdmin($userId) ){
			$ret = true;
			return $ret;
		}

		return $ret;
	}

	public function title( array $x )
	{
		$ret = '__Edit time record__';
		return $ret;
	}

	public function msg()
	{
		$ret = '__Time record updated__';
		return $ret;
	}

	public function nav( array $x )
	{
		$ret = [];
		return $ret;
	}

	public function post( array $x )
	{
		$m0 = $x['$m'];

		$m = clone $m0;

		$duration = $this->inputDuration->grab( 'duration', $x['post'] );
		// echo "Du = '$duration'<br>";
// _print_r( $x['post'] );
// _print_r( $_POST );
		// exit;

		$m->duration = $duration;

		if( isset($x['post']['activity']) ){
			$activityId = $x['post']['activity'];
			$dictActivity = $x[ '$dictActivity' ];
			if( isset($dictActivity[$activityId]) ){
				$m->activityId = $activityId;
			}
		}

		if( $m->duration ){
			$this->modelRecord->update( $m0, $m );
		}
		else {
			$this->modelRecord->delete( $m0 );
		}

		$x['redirect'] = '..';

		return $x;
	}

	public function get( array $x )
	{
		$id = $x['id'];
		$m = $this->modelRecord->findById( $id );
		$x[ '$m' ] = $m;

		$q = [];
		$q[] = [ 'workerId', '=', $m->workerId ];
		$q[] = [ 'projectId', '=', $m->projectId ];
		$listActivityId = $this->modelActivityProjectWorker->findProp( 'activityId', $q );

		$q = [];
		$q[] = [ 'id', '=', $listActivityId ];
		$q[] = [ 'stateId', '=', 'active' ];
		$dictActivity = $this->modelActivity->find( $q );
		$x[ '$dictActivity' ] = $dictActivity;

		return $x;
	}

	public function render( array $x )
	{
		$ret = [];
		$ret[ '41-main' ] = [ $this->self, 'renderMain' ];
		return $ret;
	}

	public function renderMain( array $x )
	{
?>

<form method="post">

<?php echo $this->self->renderForm( $x ); ?>

<footer>
<button type="submit">__Update time record__</button>
</footer>

</form>

<?php
	}

	public function renderForm( array $x )
	{
		$m = $x['$m'];
		$activity = $this->modelActivity->findById( $m->activityId );
		$worker = $this->modelWorker->findById( $m->workerId );

		$dictActivity = $x[ '$dictActivity' ];
?>

<section>
<table>
<tr>
	<th scope="row">__Date__</th>
	<td>
		<?php echo $this->t->formatDateFull( $m->startDate ); ?>
	</td>
</tr>
<?php if( 1 == count($dictActivity) ): ?>
	<tr>
		<th scope="row">__Activity__</th>
		<td>
			<?php echo $this->pageActivityId->renderTo( $x, $activity ); ?>
		</td>
	</tr>
<?php endif; ?>
</table>
</section>

<?php if( count($dictActivity) > 1 ): ?>
<section>
<label>
	<span>__Activity__</span>
	<div>
		<select name="activity">
		<?php foreach( $dictActivity as $e ): ?>
			<option value="<?php echo esc_attr($e->id); ?>"<?php if( $e->id == $m->activityId ): ?> selected<?php endif; ?>><?php echo esc_html( $this->pageActivityId->renderTextLabel($x, $e) ); ?></option>
		<?php endforeach; ?>
		</select>
	</div>
</label>
</section>
<?php endif; ?>

<?php
$attr = [ 'step' => 15 * 60 ];
?>
<section>
	<fieldset>
		<legend><span>__Duration__</span></legend>
		<div>
			<div>
				<?php echo $this->inputDuration->render( 'duration', $m->duration, $attr ); ?>
			</div>
			<div>
				<small>__Set duration to 0 to delete this time record.__</small>
			</div>
		</div>
	</fieldset>
</section>


<?php
	}
}