<?php
namespace Plainware\PlainTracker;

class PageWorkerUser
{
	public $self = __CLASS__;

	public $inputText = \Plainware\HtmlInputText::class;
	public $inputRadio = \Plainware\HtmlInputRadio::class;

	public $modelWorker = ModelWorker::class;
	public $pageWorkerId = PageWorkerId::class;

	public $modelUser = ModelUser::class;
	public $pageUserId = PageUserId::class;

	public $auth = Auth::class;
	public $acl = Acl::class;

	public function can( array $x )
	{
		return $this->acl->isAdmin( $this->auth->getCurrentUserId($x) );
	}

	public function title( array $x )
	{
		$a = $x['a-'] ?? null;
		$ret = ( 'unlink' == $a ) ? '__Unlink from user__' : '__Link to user__';
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

		$m = $x['$m'] ?? null;
		if( ! $m ) return $ret;

		$oldm = $x['$oldm'] ?? null;
		if( $oldm && $oldm->userId ){
			
		}
		else {
			$ret[ '21-user' ] = [ '.?a-=select&user=null', '__Select user__' ];
			if( $m->userId ){
				$ret[ '81-confirm' ] = [ '.?a-=confirm', '__Confirm__' ];
			}
		}

		return $ret;
	}

	public function get( array $x )
	{
		$id = $x['id'] ?? null;
		if( ! $id ){
			$x['slug'] = 404;
			return $x;
		}

		$m = $this->modelWorker->findById( $id );
		if( ! $m ){
			$x['slug'] = 404;
			return $x;
		}
		$oldm = clone $m;

		if( $oldm->userId ){
			$oldUser = $this->modelUser->findById( $oldm->userId );
			if( ! $oldUser ){
				$oldm->userId = 0;
			}
		}

		$m->userId = $x['user'] ?? 0;
		if( $m->userId ){
			$user = $this->modelUser->findById( $m->userId );
			if( ! $user ){
				$m->userId = 0;
			}
		}

		$a = $x['a-'] ?? null;
		if( null === $a ){
			if( $oldm->userId ){
				$a = 'unlink';
			}
			else {
				if( $m->userId ){
					$a = 'confirm';
				}
				else {
					$a = 'select';
				}
			}
		}

		$x['a-'] = $a;
		$x['$m'] = $m;
		$x['$oldm'] = $oldm;

		return $x;
	}

	public function post( array $x )
	{
		$oldm = $x['$oldm'];
		$m = $x[ '$m' ];

	// unlink - use user's details
		if( $oldm->userId && (! $m->userId) ){
			$user = $this->modelUser->findById( $oldm->userId );
			$m->title = $user->title;
			$m->email = $user->email;
		}

		$m = $this->modelWorker->update( $oldm, $m );
		$x['redirect'] = '..';

		return $x;
	}

	public function render( array $x )
	{
		$ret = [];

		$a = $x['a-'] ?? null;
		$m = $x[ '$m' ];
		$oldm = $x[ '$oldm' ];

		if( $oldm->userId ){
			$ret[ '41-confirm' ] = [ $this->self, 'renderConfirmUnlink' ];
		}
		else {
			if( 'select' == $a ){
				$ret[ '31-select' ] = [ $this->self, 'renderSelect' ];
			}
			if( 'confirm' == $a ){
				$ret[ '41-confirm' ] = [ $this->self, 'renderConfirm' ];
			}
		}

		return $ret;
	}

	public function renderSelect( array $x )
	{
		$m = $x[ '$m' ];

		$q = [];
		$q[] = [ 'userId', '<>', 0 ];
		$listAlreadyId = $this->modelWorker->findProp( 'userId', $q );

		$q = [];
		if( $listAlreadyId ){
			$q[] = [ 'id', '<>', $listAlreadyId ];
		}
		$dictUser = $this->modelUser->find( $q );
?>

<header>
<h3>__Select user__</h3>
</header>

<table>
<thead>
<tr>
	<th class="pw-col-icon"></th>
	<th>__User__</th>
	<th class="pw-col-1 pw-col-align-end">__ID__</th>
</tr>
</thead>
<tbody>
<?php foreach( $dictUser as $user ): ?>
<tr>
<td class="pw-col-icon">
	<i>&raquo;</i>
</td>
<td>
	<a href="URI:.?user=<?php echo esc_attr($user->id); ?>">
		<?php echo $this->pageUserId->renderTo( $x, $user, false ); ?>
	</a>
</td>
<td class="pw-col-align-end">
	<?php echo esc_html($user->id); ?>
</td>
</tr>
<?php endforeach; ?>
</tbody>

</table>

<?php
	}

	public function renderConfirm( array $x )
	{
		$m = $x[ '$m' ];
		$user = $this->modelUser->findById( $m->userId );
?>

<header>
<h3>__Confirm__</h3>
</header>

<form method="post">

<section>
<table>
<tr>
	<th scope="row">__Worker__</th>
	<td>
		<?php echo $this->pageWorkerId->renderTo( $x, $m ); ?>
	</td>
</tr>
<tr>
	<th scope="row">__User__</th>
	<td>
		<?php echo $this->pageUserId->renderTo( $x, $user ); ?>
	</td>
</tr>
</table>
</section>

<footer>
<button type="submit"><i>&harr;</i><span>__Link worker to user__</span></button>
</footer>
</form>

<?php
	}

	public function renderConfirmUnlink( array $x )
	{
		$m = $x[ '$m' ];
		$oldm = $x[ '$oldm' ];
		$user = $this->modelUser->findById( $oldm->userId );
?>

<form method="post">

<section>
<table>
<tr>
	<th scope="row">__Worker__</th>
	<td>
		<?php echo $this->pageWorkerId->renderTo( $x, $m ); ?>
	</td>
</tr>
<tr>
	<th scope="row">__User__</th>
	<td>
		<?php echo $this->pageUserId->renderTo( $x, $user ); ?>
	</td>
</tr>
</table>
</section>

<footer>
<button type="submit"><i>&times;</i><span>__Unlink worker from user__</span></button>
</footer>
</form>

<?php
	}


	public function renderUser( array $x )
	{
		$m = $x['$m'];
		if( ! $m->userId ) return;
		$user = $this->modelUser->findById( $m->userId );
		if( ! $user ) return;
?>

<table>
<tbody>

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
}