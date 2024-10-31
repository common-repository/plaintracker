<?php
namespace Plainware\PlainTracker;

class PageUserId
{
	public $self = __CLASS__;
	public $userModel = ModelUser::class;

	public $auth = Auth::class;
	public $acl = Acl::class;

	public function can( array $x )
	{
		return $this->acl->isAdmin( $this->auth->getCurrentUserId($x) );
	}

	public function nav( array $x )
	{
		$ret = [];
		return $ret;
	}

	public function title( array $x )
	{
		$id = $x['id'];
		$m = $this->userModel->findById( $id );
		$ret = $m->title;

		return $ret;
	}

	public function renderTo( array $x, _User $m, $to = null )
	{
		if( false !== $to ){
			if( null === $to ){
				$can = $this->self->can( ['id' => $m->id ] + $x );
				if( $can ){
					$to = 'user-index--user-id?id=' . $m->id;
				}
			}
		}
?>
<?php if( $to ): ?><a href="URI:<?php echo esc_attr($to); ?>"><?php endif; ?>
<?php if( 0 ): ?><?php echo esc_html($m->title); ?> (<?php echo esc_html($m->email); ?>)<?php endif; ?>
<?php if( 1 ): ?><?php echo esc_html($m->title); ?><?php endif; ?>
<?php if( $to ): ?></a><?php endif; ?>
<?php
	}

	public function get( array $x )
	{
		$id = $x['id'];
		$m = $this->userModel->findById( $id );
		$x[ '$m' ] = $m;

		return $x;
	}

	public function render( array $x )
	{
		$m = $x[ '$m' ];

		$ret = [];
		$ret[ '21-main' ] = [ [$this->self, 'renderMain'], $x, $m ];

		return $ret;
	}

	public function renderMain( array $x, _User $m )
	{
?>

<table>
<tbody>

<tr>
	<th scope="row">__Full Name__</th>
	<td>
		<?= esc_html( $m->title ); ?>
	</td>
</tr>

<tr>
	<th scope="row">__Email__</th>
	<td>
		<?= esc_html( $m->email ); ?>
	</td>
</tr>

<tr>
	<th scope="row">__Is administrator?__</th>
	<td>
		<span>
			<?php if( $this->acl->isAdmin($m->id) ): ?>
				<em><i>&check;</i><span>__Yes__</span></em>
			<?php else : ?>
				<strong><i>&times;</i><span>__No__</span></strong>
			<?php endif; ?>
		</span>
	</td>
</tr>

</tbody>
</table>

<?php
	}
}