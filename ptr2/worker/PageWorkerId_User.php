<?php
namespace Plainware\PlainTracker;

class PageWorkerId_User
{
	public $self = __CLASS__;

	public $modelWorker = ModelWorker::class;
	public $pageWorkerId = PageWorkerId::class;

	public $modelUser = ModelUser::class;
	public $pageUserId = PageUserId::class;

	public function nav( array $ret, array $x )
	{
		$id = $x['id'] ?? null;
		if( ! $id ) return;

		$m = $this->modelWorker->findById( $id );
		if( ! $m ) return;

		$user = $m->userId ? $this->modelUser->findById( $m->userId ) : null;
		if( $user ){
			$ret[ '31-user' ] = [ '.worker-user?id=' . $m->id, '__Unlink from user__' ];
		}
		else {
			$ret[ '72-user' ] = [ '.worker-user?id=' . $m->id, '__Link to user__' ];
		}

		return $ret;
	}

	public function render( array $ret, array $x )
	{
		$ret[ '31-user' ] = [ $this->self, 'renderUser' ];
		return $ret;
	}

	public function renderUser( array $x )
	{
		$m = $x['$m'];
		if( ! $m->userId ) return;
		$user = $this->modelUser->findById( $m->userId );
		if( ! $user ) return;
?>

<header>
<h3>__Linked to user account__</h3>
</header>


<table>
<tbody>

<tr>
	<th scope="row">__User account__</th>
	<td>
		<?php echo $this->pageUserId->renderTo( $x, $user ); ?>
	</td>
</tr>

<tr>
	<th scope="row">__User ID__</th>
	<td>
		<?php echo esc_html( $user->id ); ?>
	</td>
</tr>

</tbody>
</table>

<?php
	}

	public function afterRender( array $ret, array $x )
	{
		$m = $x['$m'] ?? null;
		if( $m && $m->userId ){
			$ret[ '31-user' ] = [ $this->self, 'renderUser' ];
		}
		return $ret;
	}
}