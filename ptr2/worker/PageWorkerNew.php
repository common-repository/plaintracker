<?php
namespace Plainware\PlainTracker;

class PageWorkerNew
{
	public $self = __CLASS__;

	public $model = ModelWorker::class;
	public $inputText = \Plainware\HtmlInputText::class;

	public $modelUser = ModelUser::class;

	public $auth = Auth::class;
	public $acl = Acl::class;

	public function can( array $x )
	{
		return $this->acl->isAdmin( $this->auth->getCurrentUserId($x) );
	}

	public function title()
	{
		$ret = '__Create a new worker__';
		return $ret;
	}

	public function post( array $x )
	{
		$m = $x[ '$m' ];

		$m->title = $x['post']['title'];
		$m->email = $x['post']['email'];

		$x['error'] = $this->model->createError( $m );
		if( $x['error'] ) return $x;

		$m = $this->model->create( $m );

		$to = [ '../worker-id', ['id' => $m->id] ];
		$x['redirect'] = $to;

		return $x;
	}

	public function get( array $x )
	{
	// new m
		$m = $this->model->construct();
		$x[ '$m' ] = $m;

		return $x;
	}

	public function render( array $x )
	{
		$m = $x['$m'];
?>

<form method="post">

<section>
	<label>
		<span>__Full name__</span>
		<?php echo $this->inputText->render( 'title', $m->title, ['required' => true, 'placeholder' => '__John Doe__'] ); ?>
	</label>
</section>

<section>
	<label>
		<span>__Email__</span>
		<?php echo $this->inputText->render( 'email', $m->email, ['placeholder' => 'john@doe.com'] ); ?>
	</label>
</section>

<footer>
	<button type="submit">__Save__</button>
</footer>

</form>

<?php
	}
}