<?php
namespace Plainware\PlainTracker;

class PageAdminHelp
{
	public $self = __CLASS__;

	public $modelUser = ModelUser::class;
	public $app = \Plainware\App::class;

	public $auth = Auth::class;
	public $acl = Acl::class;

	public function can( array $x )
	{
		return $this->acl->isAdmin( $this->auth->getCurrentUserId($x) );
	}

	public function title( array $x )
	{
		$ret = '__Administration guide__';
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
		$ret[ '31-help' ] = [ $this->self, 'renderHelp' ];
		return $ret;
	}

	public function renderHelp( array $x )
	{
?>

<section>
<table>
<caption>__Worker__</caption>
<tr>
<td>
__Workers are people who do the work.__ __Workers are automatically created from WordPress user accounts users.__
</td>
</tr>
</table>
</section>

<section>
<table>
<caption>__Approver__</caption>
<tr>
<td>
__Approvers can approve or decline timesheets of the people that work for them.__ __Normally this would be the supervisors with their team members under them.__ __Each worker should have at least one approver.__ __Approvers should be manually assigned from WordPress user accounts.__
</td>
</tr>
</table>
</section>

<section>
<table>
<caption>__Activity__</caption>
<tr>
<td>
__Activities define types of work that workers report in their timesheets.__
</td>
</tr>
</table>
</section>

<section>
<table>
<caption>__Project__</caption>
<tr>
<td>
__Projects link workers and activities together.__ __In order to be able to report time, a worker should be added to at least one project.__ __A worker can be in different projects performing different activities.__
</td>
</tr>
</table>
</section>

<section>
<table>
<caption>__Front end__</caption>
<tr>
<td>
__The plugin can be fully functional from a front end page or post with our shortcode.__ 
</td>
</tr>
<tr>
<td>
<b><code>[plaintracker]</code></b>
</td>
</tr>
</table>
</section>

<?php
	}
}