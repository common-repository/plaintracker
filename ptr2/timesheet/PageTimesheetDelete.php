<?php
namespace Plainware\PlainTracker;

class PageTimesheetDelete
{
	public $self = __CLASS__;
	public $model = ModelTimesheet::class;

	public $t = \Plainware\Time::class;

	public $auth = Auth::class;
	public $acl = Acl::class;

	public function can( array $x )
	{
		return $this->acl->isAdmin( $this->auth->getCurrentUserId($x) );
	}

	public function title( array $x )
	{
		$ret = '__Delete timesheet__';
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
?>

<form method="post">

<?php echo $this->self->renderForm( $x ); ?>

<footer>
	<ul>
		<li>
			<a href="URI:.."><i>&laquo;</i><span>__No, cancel deleting__</span></a>
		</li>
		<li>
			<button type="submit">__Yes, confirm deleting__</button>
		</li>
	</ul>
</footer>

</form>

<?php
	}

	public function renderForm( array $x )
	{
?>

<section>
<table>
<tr>
	<th scope="row">
		<strong>__Are you sure?__</strong>
	</th>
	<td>
		__This item will be permanently deleted from the database including all related items without possibility to restore it later. If this is not what you are intending to do, consider changing the item status instead.__
	</td>
</tr>
</table>
</section>

<?php
	}
}