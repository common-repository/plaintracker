<?php
namespace Plainware\PlainTracker;

class PageProjectEdit
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
		$ret = '__Edit project__';
		return $ret;
	}

	public function post( array $x )
	{
		$m0 = $x[ '$m0' ];
		$m = $x[ '$m' ];

		$m->startDate = $this->inputDate->grab( 'start_date', $x['post'] );
		$m->endDate = $this->inputDate->grab( 'end_date', $x['post'] );
		$m->title = $x['post']['title'] ?? '';

		$x['error'] = $this->model->updateError( $m0, $m );
		if( $x['error'] ){
			return $x;
		}

		$m = $this->model->update( $m0, $m );

		$to = '..';
		$x['redirect'] = $to;

		return $x;
	}

	public function get( array $x )
	{
		$id = $x['id'] ?? null;
		if( ! $id ){
			$x['slug'] = 404;
			return $x;
		}

		$m0 = $this->model->findById( $id );
		if( ! $m0 ){
			$x['slug'] = 404;
			return $x;
		}

		$m = clone $m0;

		$x[ '$m0' ] = $m0;
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
	<button type="submit">__Update project__</button>
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