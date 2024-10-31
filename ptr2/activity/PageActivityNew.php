<?php
namespace Plainware\PlainTracker;

class PageActivityNew
{
	public $self = __CLASS__;

	public $model = ModelActivity::class;
	public $inputText = \Plainware\HtmlInputText::class;

	public $auth = Auth::class;
	public $acl = Acl::class;

	public function can( array $x )
	{
		return $this->acl->isAdmin( $this->auth->getCurrentUserId($x) );
	}

	public function title()
	{
		$ret = '__New activity__';
		return $ret;
	}

	public function post( array $x )
	{
		$m = $x[ '$m' ];

		$m->title = $x['post']['title'];

		$x['error'] = $this->model->createError( $m );
		if( $x['error'] ) return $x;

		$m = $this->model->create( $m );

		$to = [ '../activity-id', ['id' => $m->id] ];
		$x['redirect'] = $to;

		return $x;
	}

	public function get( array $x )
	{
	// new m
		$m = $this->model->construct();
		$x[ '$m' ] = $m;

	// count current activities
		$count = $this->model->count( [] );
		$x[ '$count' ] = $count;

		return $x;
	}

	public function render( array $x )
	{
		$m = $x['$m'];
?>

<form method="post">

<?php echo $this->self->renderForm( $x ); ?>

<footer>
	<button type="submit">__Create new activity__</button>
</footer>

</form>

<?php
	}

	public function renderForm( array $x )
	{
		$m = $x['$m'];
?>

<section>
	<label>
		<span>__Activity name__</span>
		<span>
			<?php echo $this->inputText->render( 'title', $m->title, ['required' => true, 'placeholder' => '__Cold Calls__'] ); ?>
			<small>__A descriptive name for this item.__</small>
		</span>
	</label>
	</section>

<?php
	}

}