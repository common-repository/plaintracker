<?php
namespace Plainware\PlainTracker;

class PageActivityDelete
{
	public $self = __CLASS__;
	public $model = ModelActivity::class;

	public $auth = Auth::class;
	public $acl = Acl::class;

	public function can( array $x )
	{
		return $this->acl->isAdmin( $this->auth->getCurrentUserId($x) );
	}

	public function title( array $x )
	{
		$ret = '__Delete__';
		return $ret;
	}

	public function nav( array $x )
	{
		$ret = [];
		return $ret;
	}

	public function post( array $x )
	{
		$x = $this->self->grab( $x );

		$m = $x[ '$m' ];

		$x['error'] = $this->model->deleteError( $m );
		if( $x['error'] ) return $x;

		$m = $this->model->delete( $m );

		$x['redirect'] = '..';

		return $x;
	}

	public function get( array $x )
	{
		$id = $x['id'];
		$m = $this->model->findById( $id );
		$x[ '$m' ] = $m;

		return $x;
	}

	public function render( array $x )
	{
		$m = $x[ '$m' ];
?>

<section>
<p><strong>__Are you sure?__</strong></p>

<p>
__This item will be permanently deleted from the database including all related items without possibility to restore it later. If this is not what you are intending to do, consider changing the item status instead.__
</p>
</section>

<section>
<form method="post">
<section>
<footer>
	<a href="URI:.."><i>&laquo;</i><span>__No, cancel deleting__</span></a>
	<button type="submit">__Yes, confirm deleting__</button>
</footer>
</section>
</form>
</section>

<?php
	}

	public function grab( array $x )
	{
		return $x;
	}
}