<?php
namespace Plainware\PlainTracker;

class PageWorkerId
{
	public $self = __CLASS__;

	public $model = ModelWorker::class;

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

	public function renderTo( array $x, _Worker $m, $to = null )
	{
		if( false !== $to ){
			if( null === $to ){
				$can = $this->self->can( ['id' => $m->id ] + $x );
				if( $can ){
					$to = 'worker-index--worker-id?id=' . $m->id;
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

	public function nav( array $x )
	{
		$ret = [];

		$id = $x['id'] ?? null;
		if( ! $id ) return $ret;

		$ret[ '11-overview' ] = [ '.', '__Overview__' ];
		$ret[ '71-edit' ] = [ '.worker-edit?id=' . $id, '__Edit__' ];
		$ret[ '81-delete' ] = [ '.worker-delete?id=' . $id, '<i>&times;</i><span>__Delete__</span>' ];

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
		$ret[ '75-error' ] = [ $this->self, 'renderError' ];

		return $ret;
	}

	public function renderMain( array $x )
	{
		$m = $x['$m'];
?>

<table>
<tbody>

<tr>
	<th scope="row">__Full name__</th>
	<td>
		<?= esc_html( $m->title ); ?>
	</td>
</tr>

<tr>
	<th scope="row">__Email__</th>
	<td>
		<?php if( $m->email ): ?><?= esc_html( $m->email ); ?><?php else : ?>__N/A__<?php endif; ?>
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

	public function renderStateText( array $x, $stateId )
	{
		if( 'active' == $stateId ){
			$ret = '__Active__';
		}
		else {
			$ret = '__Archived__';
		}
		return $ret;
	}

	public function renderState( array $x, $stateId )
	{
		$bgcolor = '#900';
		$color = '#fff';
		$textLabel = $this->self->renderStateText( $x, $stateId );

		if( 'active' == $stateId ){
			$bgcolor = 'transparent';
			$color = '#060';
		}
		else {
			$bgcolor = 'transparent';
			$color = '#666';
		}
?>
<span style="background-color: <?= esc_attr($bgcolor); ?>; color: <?= esc_attr($color); ?>;"><?= esc_html($textLabel); ?></span>
<?php
	}
}