<?php
namespace Plainware\PlainTracker;

class PageAbout
{
	public $self = __CLASS__;
	public $app = \Plainware\App::class;

	public $auth = Auth::class;
	public $acl = Acl::class;

	public function can( array $x )
	{
		return $this->acl->isAdmin( $this->auth->getCurrentUserId($x) );
	}

	public function title( array $x )
	{
		$ret = '__About__';
		return $ret;
	}

	public function nav( array $x )
	{
		$ret = [];
		return $ret;
	}

	public function get( array $x )
	{
		return $x;
	}

	public function render( array $x )
	{
		$ret = [];
		$ret[ '21-main' ] = [ $this->self, 'renderMain' ];
		return $ret;
	}

	public function renderMain( array $x )
	{
		$ret = [];
		$ret[ '21-core' ] = [ $this->self, 'renderCore' ];
		return $ret;
	}

	public function renderCore( array $x )
	{
		$version = $this->app->version();
		$name = $this->app->name();
?>

<table>
<tbody>
	<tr>
		<th scope="row">__Application__</th>
		<td>
			<?php echo esc_html( $name ); ?>
		</td>
	</tr>
	<tr>
		<th scope="row">__Version__</th>
		<td>
			<?php echo esc_html( $version ); ?>
		</td>
	</tr>
</tbody>
</table>

<?php
	}

}