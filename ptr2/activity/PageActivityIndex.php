<?php
namespace Plainware\PlainTracker;

class PageActivityIndex
{
	public $self = __CLASS__;
	public $model = ModelActivity::class;

	public $pageId = PageActivityId::class;

	public $auth = Auth::class;
	public $acl = Acl::class;

	public function can( array $x )
	{
		return $this->acl->isAdmin( $this->auth->getCurrentUserId($x) );
	}

	public function title( array $x )
	{
		$ret = '__Activities__';
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

		$ret[ '31-new' ] = [ '.activity-new', '<i>&plus;</i><span>__Create new__</span>' ];

		return $ret;
	}

	public function get( array $x )
	{
		$q = [];

		$stateId = $x['state'] ?? 'active';
		$q[] = [ 'stateId', '=', $stateId ];

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
		$ret[ '42-list' ] = [ $this->self, 'renderList' ];
		return $ret;
	}

	public function renderList( array $x )
	{
		$q = $x['$q'];
		$dict = $this->model->find( $q );
		if( ! $dict ) return;

		$dictError = $this->model->findError( $dict );
?>

<p>
__Activities define types of work that workers report in their timesheets.__ __Link workers to activities through projects.__
</p> 

<table>
<thead>
<?php echo $this->self->renderListHead( $x, $dict, $dictError ); ?>
</thead>
<tbody>
<?php foreach( $dict as $m ): ?>
	<?php
	$hasError = isset( $dictError[$m->id] ) && $dictError[$m->id];
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
	<th>__Activity__</th>
 	<th class="pw-col-1 pw-col-align-end">__ID__</th>
</tr>

<?php
	}

	public function renderListOne( array $x, _Activity $m, $hasError )
	{
?>

<tr>
	<td>
		<a href="URI:.activity-id?id=<?php echo esc_attr($m->id); ?>">
			<?php if( $hasError ): ?><i>!</i><span><?php endif; ?><?php echo $this->pageId->renderTo( $x, $m, false ); ?><?php if( $hasError ): ?></span><?php endif; ?>
		</a>
	</td>
	<td class="pw-col-align-end">
		<?php echo esc_html( $m->id ); ?>
	</td>
</tr>

<?php
	}
}