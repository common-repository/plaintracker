<?php
namespace Plainware\PlainTracker;

class X_PageAbout_Install
{
	public function nav( array $ret, array $x )
	{
		$ret[ '91-uninstall' ] = [ '.uninstall', '__Uninstall__' ];
		return $ret;
	}
}