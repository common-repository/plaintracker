<?php
namespace Plainware;

class PageInstallOk
{
	public $self = __CLASS__;
	public $app = \Plainware\App::class;

	public function can( array $x )
	{
		$ret = true;
		return $ret;
	}

	public function title( array $x )
	{
		$ret = '__Installation successful__';
		return $ret;
	}

	public function nav( array $x )
	{
		$ret = [];
		return $ret;
	}

	public function get( array $x )
	{}

	public function render( array $x )
	{
?>

<section>
<p>__Thank you!__</p>

<table>
<tbody>
<tr>
	<th scope="row">__Application__</th><td><?= esc_html( $this->app->name() ); ?></td>
</tr>
<tr>
	<th scope="row">__Version__</th><td><?= esc_html( $this->app->version() ); ?></td>
</tr>
</tbody>
</table>
</section>

<section>
<nav>
<ul>
<li>
	<a href="URI:">__Continue to the main page__</a>
</li>
</ul>
</nav>
</section>

<?php
	}
}