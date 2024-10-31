<?php
namespace Plainware\PlainTracker;

class X_PageWorkerId_WorkerWpuser
{
	public function nav( array $ret, array $x )
	{
		$ret[ 'edit' ] = null;
		$ret[ 'delete' ] = null;
		return $ret;
	}

	public function render( array $ret, array $x )
	{
		$ret[ '31-user' ] = null;
		return $ret;
	}
}