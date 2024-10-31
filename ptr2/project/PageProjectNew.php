<?php
namespace Plainware\PlainTracker;

class PageProjectNew
{
	public $self = __CLASS__;

	public $model = ModelProject::class;

	public $inputDate = \Plainware\HtmlInputDateJs::class;
	public $inputTime = \Plainware\HtmlInputTimeHourMinute::class;

	public $t = \Plainware\Time::class;

	public $auth = Auth::class;
	public $acl = Acl::class;

	public function can( array $x )
	{
		return $this->acl->isAdmin( $this->auth->getCurrentUserId($x) );
	}

	public function title()
	{
		$ret = '__New project__';
		return $ret;
	}

	public function post( array $x )
	{
		$m = $x[ '$m' ];

		$withDate = $x['withdate'] ?? true;

		if( $withDate ){
			$m->startDate = $this->inputDate->grab( 'start_date', $x['post'] );
			$m->endDate = $this->inputDate->grab( 'end_date', $x['post'] );

			$startSubmitDate = $this->inputDate->grab( 'start_submit_date', $x['post'] );
			$startSubmitTime = $this->inputTime->grab( 'start_submit_time', $x['post'] );

			$m->startSubmit = $this->t->fromDateSeconds( $startSubmitDate, $startSubmitTime );

			$endSubmitDate = $this->inputDate->grab( 'end_submit_date', $x['post'] );
			$endSubmitTime = $this->inputTime->grab( 'end_submit_time', $x['post'] );
			$m->endSubmit = $this->t->fromDateSeconds( $endSubmitDate, $endSubmitTime );
		}

		$m->title = $x['post']['title'] ?? '';
		$m->payPeriod = $x['post']['pay_period'] ?? '';

		$x['error'] = $this->model->createError( $m );
		if( $x['error'] ){
			return $x;
		}

		$m = $this->model->create( $m );

		$to = ($m->id > 0) ? [ '../project-id', ['id' => $m->id] ] : '..';
		$x['redirect'] = $to;

		return $x;
	}

	public function get( array $x )
	{
		$withDate = $x['withdate'] ?? true;

	// new m
		$m = $this->model->construct();

		$q = [];
		$q[] = [ 'endDate', '<', 99999999 ];
		$q[] = [ 'order', 'endDate', 'DESC' ];
		$q[] = [ 'limit', 1 ];
		$res = $this->model->findProp( 'endDate', $q );

		if( $res ){
			$d1 = current( $res );
			$d1 = $this->t->getNextDate( $d1 );
		}
		else {
			$now = $this->t->getNow();
			$d1 = $this->t->getDate( $now );
			$d1 = $this->t->getStartWeek( $d1 );
			$d1 = $this->t->getDate( $d1 );
		}

		$m->startDate = $d1;

		$d2 = $d1;
		$d2 = $this->t->getEndYear( $d1 );
		$d2 = $this->t->getDate( $d2 );
		$m->endDate = $d2;

		$x[ '$m' ] = $m;

		return $x;
	}

	public function render( array $x )
	{
		$m = $x['$m'];
?>

<form method="post">

<?php echo $this->self->renderForm( $x ); ?>

<footer>
	<button type="submit">__Create new project__</button>
</footer>

</form>

<?php
	}

	public function renderForm( array $x )
	{
		$m = $x['$m'];
?>

<?php
$v = $x['post']['title'] ?? $m->title;
?>
<section>
	<label>
		<span>__Project title__</span>
		<input type="text" name="title" value="<?php echo esc_attr($v); ?>" required>
		<?php if( isset($x['error']['title']) ): ?><strong><?php echo esc_html($x['error']['title']); ?></strong><?php endif; ?>
	</label>
</section>

<section>
	<fieldset>
		<legend><span>__Start date__</span></legend>
		<?php echo $this->inputDate->render( 'start_date', $m->startDate ); ?>
	</fieldset>
</section>

<section>
	<fieldset>
		<legend><span>__End date__</span></legend>
		<?php echo $this->inputDate->render( 'end_date', $m->endDate ); ?>
	</fieldset>
</section>

<?php
	}
}