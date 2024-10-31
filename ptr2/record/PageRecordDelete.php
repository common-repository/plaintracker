<?php
namespace Plainware\PlainTracker;

class PageRecordDelete
{
	public $self = __CLASS__;
	public $model = ModelRecord::class;

	public $t = \Plainware\Time::class;

	public $auth = Auth::class;
	public $acl = Acl::class;

	public function can( array $x )
	{
		return $this->acl->isAdmin( $this->auth->getCurrentUserId($x) );
	}

	public function title( array $x )
	{
		$ret = '__Delete time record__';
		return $ret;
	}

	public function nav( array $x )
	{
		$ret = [];
		return $ret;
	}

	public function post( array $x )
	{
		if( $x['error'] ) return $x;

		$m = $x[ '$m' ];
		$this->model->delete( $m );

		$x['redirect'] = '../..';

		return $x;
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
		$ret[ '21-main' ] = $this->self->renderMain( $x );
		return $ret;
	}

	public function renderMain( array $x )
	{
		$m = $x[ '$m' ];
?>

<form method="post">

<?= $this->self->renderForm( $x ); ?>

<footer>
	<a href="URI:.."><i>&laquo;</i><span>__No, cancel deleting__</span></a>
	<button type="submit">__Yes, confirm deleting__</button>
</footer>

</form>

<?php
	}

	public function renderForm( array $x )
	{
?>

<section>
<fieldset>
	<legend><span><strong>__Are you sure?__</strong></span></legend>
	<section>
	__This item will be permanently deleted from the database including all related items without possibility to restore it later. If this is not what you are intending to do, consider changing the item status instead.__
	</section>
</fieldset>
</section>

<?php
	}
}