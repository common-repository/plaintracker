<?php
namespace Plainware\PlainTracker;

class PageProjectId
{
	public $self = __CLASS__;

	public $model = ModelProject::class;

	public $t = \Plainware\Time::class;

	public $auth = Auth::class;
	public $acl = Acl::class;

	public function can( array $x )
	{
		return $this->acl->isAdmin( $this->auth->getCurrentUserId($x) );
	}

	public function title( array $x )
	{
		$ret = '';

		$id = $x['id'] ?? null;
		if( ! $id ) return;

		$m = $this->model->findById( $id );
		if( ! $m ) return;

		$ret = $m->title;

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

	public function renderTo( array $x, _Project $m, $to = null )
	{
		if( false !== $to ){
			if( null === $to ){
				$can = $this->self->can( ['id' => $m->id ] + $x );
				if( $can ){
					$to = 'project-index--project-id?id=' . $m->id;
				}
			}
		}

		$title = '';

		// if( $m->startDate && strlen($m->title) ){
			// $title = $m->title . ' (' . $this->t->formatDateRange( $m->startDate, $m->endDate ) . ')';
		// }
		// elseif( $m->startDate ){
			// $title = $this->t->formatDateRange( $m->startDate, $m->endDate );
		// }
		// else {
			// $title = $m->title;
		// }
		$title = $m->title;

		if( $m->startDate > 0 ){
			 $title .= ' (' . $this->t->formatDateRange( $m->startDate, $m->endDate ) . ')';
		}

		$htmlTitle = $title;
?>
<?php if( $to ): ?><a href="URI:<?= esc_attr($to); ?>"><?php endif; ?>
<span title="<?php echo esc_attr($htmlTitle); ?>"><?php echo esc_html($title); ?></span>
<?php if( $to ): ?></a><?php endif; ?>
<?php
	}

	public function nav( array $x )
	{
		$ret = [];

		$id = $x['id'];

		$ret[ '11-overview' ] = [ '.', '__Overview__' ];
		$ret[ '71-edit' ] = [ '.project-edit?id=' . $id, '__Edit project__' ];
		$ret[ '81-delete' ] = [ '.project-delete?id=' . $id, '<i>&times;</i><span>__Delete project__</span>' ];

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
		//$ret[ '31-submit' ] = [ $this->self, 'renderSubmit' ];

		return $ret;
	}

	public function renderMain( array $x )
	{
		$m = $x['$m'];
?>

<table>
<tbody>

<tr>
	<th scope="row">__Title__</th>
	<td>
		<?php echo esc_html($m->title); ?>
	</td>
</tr>


<?php if( $m->startDate ): ?>

	<tr>
		<th scope="row">__Start date__</th>
		<td>
			<?php echo $this->t->formatDateFull( $m->startDate ); ?>
		</td>
	</tr>

	<tr>
		<th scope="row">__End date__</th>
		<td>
			<?php echo $this->t->formatDateFull( $m->endDate ); ?>
		</td>
	</tr>

<?php else: ?>

	<tr>
		<th scope="row">__Dates__</th>
		<td>
			__This project is ongoing and doesn't have start and end dates.__
		</td>
	</tr>

<?php endif; ?>

<tr>
	<th scope="row">__ID__</th>
	<td>
		<?php echo esc_html( $m->id ); ?>
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