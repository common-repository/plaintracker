<?php
namespace Plainware\PlainTracker;

class X_PageIndex_ActivityProjectWorker
{
	public $self = __CLASS__;
	public $htmlMenu = \Plainware\HtmlMenu::class;

	public function render( array $ret, array $x )
	{
		$ret[ '25-worker' ] = [ $this->self, 'renderWorker' ];
		return $ret;
	}

	public function navWorker( array $x )
	{
		$ret[ '22-timesheet' ] = [ 'worker-timesheet-index', '__My timesheets__' ];
		$ret[ '31-record' ] = [ 'worker-record-index', '__My time records__' ];
		$ret[ '54-profile' ] = [ '.profile-worker', '__Worker profile__' ];

		return $ret;
	}

	public function renderWorker( array $x )
	{
		$nav = $this->self->navWorker( $x );
		$navView = $this->htmlMenu->render( $x, $nav );
		if( ! $navView ) return $ret;
?>

<header>
<h3>__Worker area__</h3>
</header>

<nav><?php echo $navView; ?></nav>

<?php
	}
}