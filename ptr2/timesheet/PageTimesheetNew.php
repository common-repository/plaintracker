<?php
namespace Plainware\PlainTracker;

class PageTimesheetNew
{
	public $self = __CLASS__;

	public $modelApp = ModelApp::class;
	public $modelProject = ModelProject::class;

	public $modelTimesheet = ModelTimesheet::class;
	public $pageTimesheetId = PageTimesheetId::class;

	public $modelWorker = ModelWorker::class;
	public $pageWorkerId = PageWorkerId::class;

	public $settingTimesheet = SettingTimesheet::class;

	public $t = \Plainware\Time::class;
	public $inputDate = \Plainware\HtmlInputDateJs::class;

	public $auth = Auth::class;
	public $acl = Acl::class;

	public function can( array $x )
	{
		$ret = false;

		$userId = $this->auth->getCurrentUserId( $x );

	// admin?
		if( $this->acl->isAdmin($userId) ){
			$ret = true;
			return $ret;
		}

		return $ret;
	}

	public function title( array $x )
	{
		$ret = '__New timesheet__';
		return $ret;
	}

	public function nav( array $x )
	{
		$ret = [];
		return $ret;
	}

	public function post( array $x )
	{
		$listError = $x[ '$listError' ];
		if( $listError ){
			return $x;
		}

		$m = $x[ '$m' ];
		$m->startDate = $this->inputDate->grab( 'start_date', $x['post'] );

		$x['error'] = $this->modelTimesheet->createError( $m );

		if( $x['error'] ){
			$m->startDate = null;
			$x[ '$m' ] = $m;
			return $x;
		}

		$m = $this->modelTimesheet->create( $m );
		if( $m->id > 0 ){
			$p = [ 'id' => $m->id ];
			$x['redirect'] = [ '../timesheet-id', $p ];
		}
		else {
			$x['redirect'] = '..';
		}

		return $x;
	}

	public function get( array $x )
	{
		$m = $this->modelTimesheet->construct();

		$m->workerId = $x['worker'] ?? null;
		$m->startDate = $x['d1'] ?? null;
		$x[ '$m' ] = $m;

	// check worker's errors
		$listError = [];
		if( $m->workerId ){
			$worker = $this->modelWorker->findById( $m->workerId );
			if( $worker ){
				$errorWorker = $this->modelWorker->findError( [$worker->id => $worker] );
				if( $errorWorker ){
					$errorWorker = current( $errorWorker );
					$listError += $errorWorker;
				}
			}

			if( $listError OR (! $worker) ){
				$m->workerId = null;
			}
		}

		$x[ '$listError' ] = $listError;

		$ok = true;
		$listCheck = [ 'workerId', 'startDate' ];
		foreach( $listCheck as $e ){
			if( null === $m->{$e} ){
				$ok = false;
				break;
			}
		}

		if( $ok ){
			$m = $this->modelTimesheet->create( $m );
			if( $m->id > 0 ){
				$p = [ 'id' => $m->id ];
				$x['redirect'] = [ '../timesheet-id', $p ];
			}
			else {
				$x['redirect'] = '..';
			}
			return $x;
		}

		return $x;
	}

	public function render( array $x )
	{
		$ret = [];

		$m = $x[ '$m' ];

		$listError = $x[ '$listError' ];
		if( $listError ){
			$ret[ '32-error' ] = [ $this->self, 'renderError' ];
		}
		else {
			if( ! $m->startDate ){
				$ret[ '33-date' ] = [ $this->self, 'renderSelectDate' ];
			}
		}

		return $ret;
	}

	public function renderSelectDate( array $x )
	{
		$m = $x[ '$m' ];

		$q = [];
		$q[] = [ 'workerId', '=', $m->workerId ];
		$q[] = [ 'order', 'endDate', 'DESC' ];
		$q[] = [ 'limit', 1 ];
		$res = $this->modelTimesheet->findProp( 'endDate', $q );
		if( $res ){
			$d = current( $res );
			$d = $this->t->getNextDate( $d );
		}
		else {
			$d = $this->t->getDate( $this->t->getNow() );
		}

		list( $d1, $d2 ) = $this->settingTimesheet->getPayPeriod( $d, $m->workerId );
?>

<form method="post">

<section>
	<fieldset>
		<legend><span>__Start date__</span></legend>
		<div>
			<?php echo $this->inputDate->render( 'start_date', $d1 ); ?>
			<?php if( isset($x['error']['startDate']) ): ?>
				<p>
					<strong><?php echo esc_html( $x['error']['startDate'] ); ?></strong>
				</p>
			<?php endif; ?>
		</div>
	</fieldset>
</section>

<footer>
	<button type="submit">__Continue__</button>
</footer>

</form>

<?php
	}

	public function renderError( array $x )
	{
		$listError = $x[ '$listError' ];
		$m = $x[ '$m' ];

		$workerId = $x['worker'] ?? null;
		$worker = $workerId ? $this->modelWorker->findById( $workerId ) : null;
?>

<p>
__We can't create a new timesheet due to the following errors:__
</p>

<section>
<table>
<?php foreach( $listError as $err ): ?>
	<tr>
		<td>
			<?php echo esc_html( $err ); ?>
		</td>
	</tr>
<?php endforeach; ?>
</table>
</section>

<?php if( null !== $worker ): ?>
<section>
<table>
<tr>
	<th scope="row">__Worker__</th>
	<td>
		<?php echo $this->pageWorkerId->renderTo( $x, $worker ); ?>
	</td>
</tr>
</table>
</section>
<?php endif; ?>

<?php
	}
}