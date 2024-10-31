<?php
namespace Plainware\PlainTracker;

class PageWorkerEdit
{
	public $self = __CLASS__;

	public $inputText = \Plainware\HtmlInputText::class;
	public $inputRadio = \Plainware\HtmlInputRadio::class;

	public $model = ModelWorker::class;
	public $pageId = PageWorkerId::class;

	public $modelUser = ModelUser::class;

	public $auth = Auth::class;
	public $acl = Acl::class;

	public function can( array $x )
	{
		return $this->acl->isAdmin( $this->auth->getCurrentUserId($x) );
	}

	public function title()
	{
		$ret = '__Edit__';
		return $ret;
	}

	public function nav( array $x )
	{
		$ret = [];
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
		$m = $x[ '$m' ];

		$m2 = clone $m;
		if( isset($x['post']['title']) ){
			$m2->title = $x['post']['title'];
		}
		$m2->stateId = $x['post']['state'];

		$m = $this->model->update( $m, $m2 );
		$x['redirect'] = '..';

		return $x;
	}

	public function render( array $x )
	{
		$m = $x['$m'];
		$user = $m->userId ? $this->modelUser->findById( $m->userId ) : null;
?>

<form method="post">

<?php if( $user ): ?>

	<section>
		<p>__These details are retrieved from the linked user's details__</p>
		<table>
			<tr>
				<th scope="row">__Full name__</th>
				<td>
					<?php echo esc_html( $m->title ); ?>
				</td>
			</tr>
			<tr>
				<th scope="row">__Email__</th>
				<td>
					<?php echo esc_html( $m->email ); ?>
				</td>
			</tr>
		</table>
	</section>

<?php else: ?>

	<section>
		<label>
			<span>__Full name__</span>
			<div>
				<?php echo $this->inputText->render( 'title', $m->title, ['required' => true] ); ?>
			</div>
		</label>
	</section>

	<section>
		<label>
			<span>__Email__</span>
			<div>
				<?php echo $this->inputText->render( 'email', $m->email, [] ); ?>
			</div>
		</label>
	</section>
<?php endif; ?>

<section>
	<fieldset>
		<legend><span>__Status__</span></legend>
		<section>
			<?php echo $this->self->renderInputState( 'state', $m->stateId, [] ); ?>
		</section>
	</fieldset>
</section>

<footer>
	<button type="submit">__Save__</button>
</footer>

</form>

<?php
	}

	public function renderInputState( $name, $value = null, $attr = [] )
	{
		$list = [ 'active', 'archive' ];
?>

<span>
<?php foreach( $list as $e ) : ?>
	<span>
		<label>
			<?= $this->inputRadio->render( 'state', $e, ( $e == $value ) ? true : false ); ?><span><?php echo $this->pageId->renderState( [], $e ); ?></span>
		</label>
	</span>
<?php endforeach; ?>
</span>

<?php
	}
}