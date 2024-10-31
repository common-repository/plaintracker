<?php
namespace Plainware\PlainTracker;

class PageTimesheetId_Action
{
	public $self = __CLASS__;
	public $modelTimesheet = ModelTimesheet::class;

	public function can( $ret, array $x )
	{
		if( ! $ret ) return $ret;

		$a = $x['a-'] ?? null;
		if( ! $a ) return $ret;

		$id = $x['id'];
		$timesheet = $this->modelTimesheet->findById( $id );

		if( 'submit' == $a ){
			if( ! in_array($timesheet->stateId, ['draft']) ){
				$ret = false;
			}
		}
		if( 'approve' == $a ){
			if( ! in_array($timesheet->stateId, ['draft', 'submit']) ){
				$ret = false;
			}
		}
		if( 'revoke' == $a ){
			if( ! in_array($timesheet->stateId, ['approve', 'process']) ){
				$ret = false;
			}
		}
		if( 'unsubmit' == $a ){
			if( ! in_array($timesheet->stateId, ['submit', 'approve', 'process']) ){
				$ret = false;
			}
		}

		return $ret;
	}

	public function nav( array $ret, array $x )
	{
		$ret[ '61-submit' ] = [ '.?a-=submit', '__Submit for approval__' ];
		$ret[ '62-approve' ] = [ '.?a-=approve', '__Approve__' ];
		$ret[ '63-revoke' ] = [ '.?a-=revoke', '__Revoke approval__' ];
		$ret[ '63-unsubmit' ] = [ '.?a-=unsubmit', '__Unsubmit__' ];
		return $ret;
	}

	public function post( array $x )
	{
		$a = $x['a-'] ?? null;

		$m0 = $x[ '$m' ];
		$m = clone $m0;

		if( 'submit' == $a ){
			$m->stateId = 'submit';
			$m = $this->modelTimesheet->update( $m0, $m );
			$x['redirect'] = '..';
			$x[ '$m' ] = $m;
		}
		if( 'approve' == $a ){
			$m->stateId = 'approve';
			$m = $this->modelTimesheet->update( $m0, $m );
			$x['redirect'] = '..';
			$x[ '$m' ] = $m;
		}
		if( 'revoke' == $a ){
			$m->stateId = 'submit';
			$m = $this->modelTimesheet->update( $m0, $m );
			$x['redirect'] = '.';
			$x['$m'] = $m;
		}
		if( 'unsubmit' == $a ){
			$m->stateId = 'draft';
			$m = $this->modelTimesheet->update( $m0, $m );
			$x['redirect'] = '.';
			$x['$m'] = $m;
		}

		return $x;
	}

	public function render( array $ret, array $x )
	{
		$a = $x['a-'] ?? null;

		if( 'submit' == $a ){
			$ret[ '63-submit' ] = [ $this->self, 'renderSubmit' ];
		}
		if( 'approve' == $a ){
			$ret[ '63-approve' ] = [ $this->self, 'renderApprove' ];
		}
		if( 'revoke' == $a ){
			$ret[ '63-revoke' ] = [ $this->self, 'renderRevoke' ];
		}
		if( 'unsubmit' == $a ){
			$ret[ '63-unsubmit' ] = [ $this->self, 'renderUnsubmit' ];
		}

		return $ret;
	}

	public function renderApprove( array $x )
	{
?>

<p>
__The timesheet will be approved and closed for editing.__
</p>

<form action="URI:.?a-=approve" method="post">

<section>
<button type="submit">__Approve timesheet__</button>
</section>

</form>

<?php
	}

	public function renderRevoke( array $x )
	{
?>

<p>
__This will reopen the timesheet for editing and reapproval.__
</p>

<form action="URI:.?a-=revoke" method="post">

<section>
<button type="submit">__Revoke approval__</button>
</section>

</form>

<?php
	}

	public function renderUnsubmit( array $x )
	{
?>

<p>
__This will reopen the timesheet for editing and resubmitting.__
</p>

<form action="URI:.?a-=unsubmit" method="post">

<section>
<button type="submit">__Unsubmit__</button>
</section>

</form>

<?php
	}

	public function renderSubmit( array $x )
	{
?>

<p>
__An approver will review the timesheet.__
</p>

<form action="URI:.?a-=submit" method="post">

<section>
<button type="submit">__Submit for approval__</button>
</section>

</form>

<?php
	}

}