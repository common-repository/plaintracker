<?php
namespace Plainware;

class PageInstall
{
	public $self = __CLASS__;
	public $app = \Plainware\App::class;
	public $modelInstall = ModelInstall::class;

	public function can( array $x )
	{
		$ret = true;
		return $ret;
	}

	public function title( array $x )
	{
		$ret = '__Install__';
		return $ret;
	}

	public function post( array $x )
	{
		if( $x['error'] ) return $x;

	// migration
		$this->modelInstall->doUp();

	// setups
		// $modelScripts = [ $this->modelInstallBar ];
		// $modelScript = current( $modelScripts );
		// $modelScript->__invoke();

		// $remember = true;
		// $this->commandLogin->login( $admin->id, $remember );

		$x['redirect'] = 'install-ok';
		return $x;
	}

	public function get( array $x )
	{
		return $x;
	}

	public function render( array $x )
	{
?>

<section>
<table>
<tbody>
<tr>
	<th scope="row">__Name__</th><td><?php echo esc_html( $this->app->name() ); ?></td>
</tr>
<tr>
	<th scope="row">__Version__</th><td><?php echo esc_html( $this->app->version() ); ?></td>
</tr>
</tbody>
</table>
</section>

<section>
<form method="post">

<?= $this->self->renderForm( $x ); ?>

<footer>
	<button type="submit">__Click to install__</button>
</footer>
</form>
</section>

<?php
	}

	public function renderForm( array $x )
	{
?>

<div></div>

<?php
	}
}