<?php
namespace Plainware\PlainTracker;

class PageActivityEdit
{
	public $self = __CLASS__;

	public $inputText = \Plainware\HtmlInputText::class;
	public $inputRadio = \Plainware\HtmlInputRadio::class;

	public $model = ModelActivity::class;
	public $pageId = PageActivityId::class;

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
		$id = $x['id'];
		$m = $this->model->findById( $id );
		if( ! $m ) return $x;
		$x['$m'] = $m;

		$k = isset( $x['k'] ) ? $x['k'] : null;
		$v = isset( $x['v'] ) ? $x['v'] : null;
		if( (null === $k) OR (null === $v) ){
			return $x;
		}

		$m2 = clone $m;
		if( 'state' == $k ){
			$m2->stateId = $v;
		}

		$m = $this->model->update( $m, $m2 );
		$x['redirect'] = '..';

		return $x;
	}

	public function post( array $x )
	{
		$m = $x[ '$m' ];

		$m2 = clone $m;
		$m2->title = $x['post']['title'];
		$m2->stateId = $x['post']['state'];

		$m = $this->model->update( $m, $m2 );
		$x['redirect'] = '..';

		return $x;
	}

	public function render( array $x )
	{
		$m = $x['$m'];
?>

<form method="post">

<section>
	<label>
		<span>__Activity name__</span>
		<?php echo $this->inputText->render( 'title', $m->title, ['required' => true] ); ?>
	</label>
</section>

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