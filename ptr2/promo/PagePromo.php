<?php
namespace Plainware\PlainTracker;

class PagePromo
{
	public $self = __CLASS__;

	public $auth = Auth::class;
	public $acl = Acl::class;

	public function can( array $x )
	{
		return $this->acl->isAdmin( $this->auth->getCurrentUserId($x) );
	}

	public function title( array $x )
	{
		$ret = 'Powered by Plain Tracker';
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

	public function post( array $x )
	{
		return $x;
	}

	public function render( array $x )
	{
		$ret = [];
		$ret[ '31-main' ] = [ $this->self, 'renderMain' ];
		return $ret;
	}

	public function renderMain( array $x )
	{
?>

<style>
#pw2 div.pw-box { padding: 1em; border: #ccc 1px solid; background-color: #fff; }
</style>

<section>
<p>With <a href="http://www.plaintracker.net/order/">PlainTracker Pro</a> you will get these add-ons that can highly improve your productivity!</p>
</section>

<section>
<div class="pw-grid-3">
	<?php if( 0 ): ?>
	<div class="pw-box">
		<h3 class="pw-inline-list">
			<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24"><path d="M3.5 3.75a.25.25 0 0 1 .25-.25h13.5a.25.25 0 0 1 .25.25v10a.75.75 0 0 0 1.5 0v-10A1.75 1.75 0 0 0 17.25 2H3.75A1.75 1.75 0 0 0 2 3.75v16.5c0 .966.784 1.75 1.75 1.75h7a.75.75 0 0 0 0-1.5h-7a.25.25 0 0 1-.25-.25V3.75Z"></path><path d="M6.25 7a.75.75 0 0 0 0 1.5h8.5a.75.75 0 0 0 0-1.5h-8.5Zm-.75 4.75a.75.75 0 0 1 .75-.75h4.5a.75.75 0 0 1 0 1.5h-4.5a.75.75 0 0 1-.75-.75Zm16.28 4.53a.75.75 0 1 0-1.06-1.06l-4.97 4.97-1.97-1.97a.75.75 0 1 0-1.06 1.06l2.5 2.5a.75.75 0 0 0 1.06 0l5.5-5.5Z"></path></svg><a target="_blank" href="https://www.plaintracker.net/time-card-bulk-actions/">Bulk actions</a>
		</h3>
		<p>
			Save time by performing various actions on multiple time entries at once. 
		</p>
	</div>
	<?php endif; ?>

	<div class="pw-box">
		<h3 class="pw-inline-list">
			<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24"><path d="M4.75 17.25a.75.75 0 0 1 .75.75v2.25c0 .138.112.25.25.25h12.5a.25.25 0 0 0 .25-.25V18a.75.75 0 0 1 1.5 0v2.25A1.75 1.75 0 0 1 18.25 22H5.75A1.75 1.75 0 0 1 4 20.25V18a.75.75 0 0 1 .75-.75Z"></path><path d="M5.22 9.97a.749.749 0 0 1 1.06 0l4.97 4.969V2.75a.75.75 0 0 1 1.5 0v12.189l4.97-4.969a.749.749 0 1 1 1.06 1.06l-6.25 6.25a.749.749 0 0 1-1.06 0l-6.25-6.25a.749.749 0 0 1 0-1.06Z"></path></svg><a target="_blank" href="https://www.plaintracker.net/time-card-download/">Download time records</a>
		</h3>
		<p>
			Download the list of time entries in CSV format for backup or further processing. 
		</p>
	</div>

	<div class="pw-box">
		<h3 class="pw-inline-list">
			<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24"><path d="M12 .25a.75.75 0 0 1 .673.418l3.058 6.197 6.839.994a.75.75 0 0 1 .415 1.279l-4.948 4.823 1.168 6.811a.751.751 0 0 1-1.088.791L12 18.347l-6.117 3.216a.75.75 0 0 1-1.088-.79l1.168-6.812-4.948-4.823a.75.75 0 0 1 .416-1.28l6.838-.993L11.328.668A.75.75 0 0 1 12 .25Zm0 2.445L9.44 7.882a.75.75 0 0 1-.565.41l-5.725.832 4.143 4.038a.748.748 0 0 1 .215.664l-.978 5.702 5.121-2.692a.75.75 0 0 1 .698 0l5.12 2.692-.977-5.702a.748.748 0 0 1 .215-.664l4.143-4.038-5.725-.831a.75.75 0 0 1-.565-.41L12 2.694Z"></path></svg><a target="_blank" href="https://www.plaintracker.net/white-label/">White label</a>
		</h3>
		<p>
			Specify your own application title and the main menu label.
		</p>
	</div>
</div>
</section>

<section>
<p class="pw-align-center">
Get the Pro version for all of these nice features!
</p>
</section>

<section style="margin-bottom: 2em;">
<a class="button-primary" style="display: block; text-align: center; font-size: 1.5em;" target="_blank" href="https://www.plaintracker.net/order/">Order Now</a>
</section>

<?php
	}
}