<?php
namespace Plainware\PlainTracker;

class X_PageIndex_Promo
{
	public $self = __CLASS__;
	public $htmlMenu = \Plainware\HtmlMenu::class;

	public $auth = Auth::class;
	public $acl = Acl::class;

	public function render( array $ret, array $x )
	{
		$ret[ '89-promo' ] = [ $this->self, 'renderPromo' ];
		return $ret;
	}

	public function navPromo( array $x )
	{
		$ret[ '12-index' ] = [ '.promo', '<i>&star;</i><span>__View add-ons__</span>' ];
		return $ret;
	}

	public function renderPromo( array $x )
	{
		$isAdmin = $this->acl->isAdmin( $this->auth->getCurrentUserId($x) );
		if( ! $isAdmin ) return;

		$nav = $this->self->navPromo( $x );
		$navView = $this->htmlMenu->render( $x, $nav );
?>

<header>
<h3>Powered by Plain Tracker</h3>
</header>

<p>
A timesheet plugin designed to help you keep track of times and expenses by projects, activities and workers.
Visit <a target="_blank" href="https://www.plaintracker.net/">Plain Tracker homepage</a> for more details.
</p>

<?php if( $navView ): ?>
<nav>
<?php echo $navView; ?>
</nav>
<?php endif; ?>

<?php
	}
}