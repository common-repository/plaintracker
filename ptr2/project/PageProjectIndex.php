<?php
namespace Plainware\PlainTracker;

class PageProjectIndex
{
	public $self = __CLASS__;

	public $model = ModelProject::class;
	public $pageId = PageProjectId::class;

	public $t = \Plainware\Time::class;

	public $auth = Auth::class;
	public $acl = Acl::class;

	public function can( array $x )
	{
		return $this->acl->isAdmin( $this->auth->getCurrentUserId($x) );
	}

	public function title( array $x )
	{
		$ret = '__Projects__';
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

		$now = $this->t->getNow();
		$today = $this->t->getDate( $now );

		$q = [];

		$q2 = $q;
		$q2[] = [ 'endDate', '>=', $today ];
		$q2[] = [ 'startDate', '<=', $today ];
		$count = $this->model->count( $q2 );
		if( $count ){
			$ret[ '21-index' ] = [ ['.', ['r' => 'current']], '<span>__Current projects__</span><i>(' . $count . ')</i>' ];
		}

		$q2 = $q;
		$q2[] = [ 'startDate', '>', $today ];
		$count = $this->model->count( $q2 );
		if( $count ){
			$ret[ '22-index-future' ] = [ ['.', ['r' => 'future']], '<span>__Future projects__</span><i>(' . $count . ')</i>' ];
		}

		$q2 = $q;
		$q2[] = [ 'endDate', '<', $today ];
		$count = $this->model->count( $q2 );
		if( $count ){
			$ret[ '23-index-past' ] = [ ['.', ['r' => 'past']], '<span>__Past projects__</span><i>(' . $count . ')</i>' ];
		}

		$ret[ '31-new' ] = [ '.project-new', '<i>&plus;</i><span>__Create new__</span>' ];

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
		$ret[ '42-list' ] = [ $this->self, 'renderList' ];
		return $ret;
	}

	public function renderList( array $x )
	{
		$q = $x['$q'];

		$now = $this->t->getNow();
		$today = $this->t->getDate( $now );

		$r = $x['r'] ?? 'current';

		if( 'future' == $r ){
			$q[] = [ 'startDate', '>', $today ];
			$q[] = [ 'order', 'startDate', 'ASC' ];
		}
		if( 'past' == $r ){
			$q[] = [ 'endDate', '<', $today ];
			$q[] = [ 'order', 'endDate', 'DESC' ];
		}
		if( 'current' == $r ){
			$q[] = [ 'endDate', '>=', $today ];
			$q[] = [ 'startDate', '<=', $today ];
			$q[] = [ 'order', 'startDate', 'ASC' ];
		}

		$dict = $this->model->find( $q );
		if( ! $dict ) return;

		$dictError = $this->model->findError( $dict );
?>

<p>
__Projects link workers and activities together.__ __In order to be able to report time, a worker should be added to at least one project.__ __A worker can be in different projects performing different activities.__
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
 	<th class="pw-col-icon"><small>__ID__</small></th>
	<th>__Project__</th>
 	<th class="pw-col-3">__When__</th>
</tr>

<?php
	}

	public function renderListOne( array $x, _Project $m, $hasError )
	{
?>

<tr>
	<td class="pw-col-icon">
		<small><?php echo esc_html( $m->id ); ?></small>
	</td>

	<td title="__Project__">
		<a href="URI:.project-id?id=<?php echo esc_attr($m->id); ?>">
			<?php if( $hasError ): ?><i>!</i><span><?php endif; ?><?php echo $this->pageId->renderTo( $x, $m, false ); ?><?php if( $hasError ): ?></span><?php endif; ?>
		</a>
	</td>

	<td title="__When__">
		<?php echo $this->t->formatDateRange( $m->startDate, $m->endDate ); ?>
	</td>
</tr>

<?php
	}
}