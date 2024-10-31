<?php
namespace Plainware\PlainTracker;

class PageUninstall
{
	public $modelInstall = \Plainware\ModelInstall::class;

	public $auth = Auth::class;
	public $acl = Acl::class;

	public function can( array $x )
	{
		return $this->acl->isAdmin( $this->auth->getCurrentUserId($x) );
	}

	public function title( array $x )
	{
		$ret = '__Uninstall__';
		return $ret;
	}

	public function post( array $x )
	{
		$x['post']['sure'] = isset( $x['post']['sure'] ) && $x['post']['sure'] ? true : false;
		if( ! $x['post']['sure'] ){
			$x['error']['sure'] = '__Required field__';
			return $x;
		}

		$this->modelInstall->doDown();

		$x['redirect'] = '';
		return $x;
	}

	public function get( array $x )
	{
		return $x;
	}

	public function render( array $x )
	{
?>

<p>
<strong>__All current data will be deleted without possibility to restore it later.__</strong>
</p>

<form method="post">

<section>
	<div>
		<label>
			<input type="checkbox" name="sure" value="1" required="true"><span>__Are you sure?__</span>
		</label>
	</div>
</section>

<section>
	<button type="submit">__Confirm uninstall__</button>
</section>

</form>

<?php
	}
}