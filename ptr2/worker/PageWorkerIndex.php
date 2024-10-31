<?php
namespace Plainware\PlainTracker;

class PageWorkerIndex
{
	public $self = __CLASS__;

	public $model = ModelWorker::class;
	public $pageId = PageWorkerId::class;

	public $modelUser = ModelUser::class;
	public $htmlPager = \Plainware\HtmlPager::class;

	public $auth = Auth::class;
	public $acl = Acl::class;

	public function can( array $x )
	{
		return $this->acl->isAdmin( $this->auth->getCurrentUserId($x) );
	}

	public function title( array $x )
	{
		$ret = '__Workers__';
		return $ret;
	}

	public function isParent( array $x )
	{
		$ret = true;
		return $ret;
	}

	public function nav( array $x )
	{
		$ret = [];

	// has archived?
		$q = [];
		$q[] = [ 'stateId', '=', 'active' ];
		$countActive = $this->model->count( $q );

		$q = [];
		$q[] = [ 'stateId', '=', 'archive' ];
		$countArchive = $this->model->count( $q );

		if( $countArchive ){
			$ret[ '21-index-active' ] = [ ['.', ['state' => null]], '<span>' . $this->pageId->renderState( $x, 'active' ) . '</span><i>(' . $countActive . ')</i>' ];
			$ret[ '22-index-archive' ] = [ ['.', ['state' => 'archive']], '<span>' . $this->pageId->renderState( $x, 'archive' ) . '</span><i>(' . $countArchive . ')</i>' ];
		}
		else {
			$ret[ '21-index' ] = [ ['.', ['state' => null]], '<span>__List__</span><i>(' . $countActive . ')</i>' ];
		}

		$ret[ '31-new' ] = [ '.worker-new', '<i>&plus;</i><span>__Create new__</span>' ];

		return $ret;
	}

	public function get( array $x )
	{
		$q = [];
		$x[ '$q' ] = $q;
		return $x;
	}

	public function post( array $x )
	{
		return $x;
	}

	public function render( array $x )
	{
		$ret = [];

		$q = $x['$q'];

		$stateId = $x['state'] ?? 'active';
		$q[] = [ 'stateId', '=', $stateId ];

		$count = $this->model->count( $q );

		if( $count ){
			$limit = 10;
			list( $limit, $offset ) = $this->htmlPager->getLimitOffset( $x, ['limit' => $limit] );
			if( $limit && ($limit < $count) ){
				$q[] = [ 'limit', $limit ];
				$q[] = [ 'offset', $offset ];
				$ret[ '33-pager' ] = $this->htmlPager->render( $x, $count, ['limit' => $limit, 'offset' => $offset] );
			}
			$ret[ '32-list' ] = [ [$this->self, 'renderList'], $x, $q ];
		}
		else {
			$ret[ '32-list' ] = [ $this->self, 'renderNone' ];
		}

		return $ret;
	}

	public function renderList( array $x, array $q )
	{
		$dict = $this->model->find( $q );
		if( ! $dict ) return;
		$dictError = $this->model->findError( $dict );
?>

<table>
<thead>
<?php echo $this->self->renderListHead( $x, $dict, $dictError ); ?>
</thead>
<tbody>
<?php foreach( $dict as $m ): ?>
	<?php
	$hasError = $dictError[ $m->id ] ?? [];
	?>
	<?php echo $this->self->renderListOne( $x, $m, $hasError ); ?>
<?php endforeach; ?>
</tbody>
</table>

<?php
	}

	public function renderListHead( array $x, array $dict, array $dictError )
	{
?>

<tr>
	<th class="pw-col-icon"><small>__ID__</small></th>
	<th>__Worker__</th>
	<th scope="row"></th>
</tr>

<?php
	}

	public function renderListOne( array $x, _Worker $m, $hasError )
	{
?>

<tr>
	<td class="pw-col-icon">
		<small><?php echo esc_html( $m->id ); ?></small>
	</td>

	<td>
		<?php if( $hasError ): ?><i>!</i><span><?php endif; ?><b><?php echo $this->pageId->renderTo( $x, $m, false ); ?></b><?php if( $hasError ): ?></span><?php endif; ?>
	</td>

	<th role="menu">
		<nav>
			<a href="URI:.worker-id?id=<?php echo esc_attr($m->id); ?>"><i>&raquo;</i><span>__View__</span></a>
		</nav>
	</th>

</tr>

<?php
	}

	public function renderNone( array $x )
	{
?>
__No results__
<?php
	}
}