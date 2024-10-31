<?php
namespace Plainware\PlainTracker;

class PageActivityId
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
		$m = null;
		$id = $x['id'] ?? null;
		if( $id ){
			$m = $this->model->findById( $id );
		}
		$ret = $m ? $m->title : '';

		return $ret;
	}

	public function renderTitle( array $x )
	{
		$id = $x['id'] ?? null;
		if( ! $id ) return;

		$m = $this->model->findById( $id );
		if( ! $m ) return;

		$dictError = $this->model->findError( [$m->id => $m] );
		$hasError = isset( $dictError[$m->id] ) ? true : false;
?>

<h1><?php if( $hasError ): ?><i>!</i><?php endif; ?><span><?php echo esc_html( $m->title ); ?></span></h1>

<?php
	}

	public function isParent( array $x )
	{
		$ret = true;
		return $ret;
	}

	public function renderTo( array $x, _Activity $m, $to = null )
	{
		if( false !== $to ){
			if( null === $to ){
				$can = $this->self->can( ['id' => $m->id ] + $x );
				if( $can ){
					$to = 'activity-index--activity-id?id=' . $m->id;
				}
			}
		}

		$htmlTitle = $m->title . ' [__ID__:' . $m->id . ']';
		if( 'active' != $m->stateId ){
			$htmlTitle .= ' ' . $this->self->renderStateText( $x, $m->stateId );
		}
?>
<?php if( $to ): ?><a href="URI:<?= esc_attr($to); ?>"><?php endif; ?>
<span title="<?php echo esc_attr($htmlTitle); ?>"><?= esc_html($m->title); ?></span><?php if( 'active' != $m->stateId ): ?><i><?= $this->self->renderState( $x, $m->stateId ); ?></i><?php endif; ?>
<?php if( $to ): ?></a><?php endif; ?>
<?php
	}

	public function renderTextLabel( array $x, _Activity $m )
	{
		$ret = $m->title;
		if( 'active' != $m->stateId ){
			$ret .= ' ' . $this->self->renderStateText( $x, $m->stateId );
		}
		return $ret;
	}

	public function nav( array $x )
	{
		$ret = [];

		$id = $x['id'];

		$ret[ '11-overview' ] = [ '.', '__Overview__' ];
		$ret[ '71-edit' ] = [ '.activity-edit?id=' . $id, '__Edit__' ];
		$ret[ '81-delete' ] = [ '.activity-delete?id=' . $id, '<i>&times;</i><span>__Delete__</span>' ];

		return $ret;
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

		$x['$m'] = $m;

		return $x;
	}

	public function post( array $x )
	{
		return $x;
	}

	public function render( array $x )
	{
		$ret = [];

		$ret[ '21-main' ] = [ $this->self, 'renderMain' ];
		$ret[ '55-error' ] = [ $this->self, 'renderError' ];

		return $ret;
	}

	public function renderMain( array $x )
	{
		$m = $x['$m'];
?>

<table>
<tbody>

<tr>
	<th scope="row">__Activity name__</th>
	<td>
		<?= esc_html( $m->title ); ?>
	</td>
</tr>

<tr>
	<th scope="row">__Status__</th>
	<td>
		<?= $this->self->renderState( $x, $m->stateId ); ?>
	</td>
</tr>

<tr>
	<th scope="row">__ID__</th>
	<td>
		<?= esc_html( $m->id ); ?>
	</td>
</tr>
</tbody>
</table>

<?php
	}

	public function renderError( array $x )
	{
		$m = $x['$m'];
		$listError = $this->model->findError( [$m->id => $m] );
		if( ! $listError ) return;
		$listError = current( $listError );
?>

<table>
<tbody>
<?php foreach( $listError as $error ): ?>
	<tr>
		<td>
			<?php echo esc_html( $error ); ?>
		</td>
	</tr>
<?php endforeach; ?>
</tbody>
</table>

<?php
	}

	public function renderState( array $x, $stateId )
	{
		$bgcolor = '#900';
		$color = '#fff';
		$textLabel = $stateId;

		if( 'archive' == $stateId ){
			$bgcolor = 'transparent';
			$color = '#666';
			$textLabel = '__Archived__';
		}

		if( 'active' == $stateId ){
			$bgcolor = 'transparent';
			$color = '#060';
			$textLabel = '__Active__';
		}
?>
<span style="background-color: <?= esc_attr($bgcolor); ?>; color: <?= esc_attr($color); ?>;"><?= esc_html($textLabel); ?></span>
<?php
	}
}