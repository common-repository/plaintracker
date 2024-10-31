<?php
namespace Plainware\PlainTracker;

class PageIndex
{
	public $self = __CLASS__;

	public $modelUser = ModelUser::class;
	public $app = \Plainware\App::class;
	public $htmlMenu = \Plainware\HtmlMenu::class;

	public $auth = Auth::class;
	public $acl = Acl::class;

	public function can( array $x )
	{
		$ret = true;
		return $ret;
	}

	public function title( array $x )
	{
		$ret = $this->app->name();
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
		$ret[ '75-admin' ] = [ $this->self, 'renderAdmin' ];
		return $ret;
	}

	public function navAdmin( array $x )
	{
		$ret = [];
		$ret[ '90-about' ] = [ '.about', '__About__' ];
		return $ret;
	}

	public function renderAdmin( array $x )
	{
		$nav = $this->self->navAdmin( $x );
		$navView = $this->htmlMenu->render( $x, $nav );
		if( ! $navView ) return $ret;
?>

<header>
<h3>__Administrator area__</h3>
</header>
<nav><?php echo $navView; ?></nav>

<?php
	}
}