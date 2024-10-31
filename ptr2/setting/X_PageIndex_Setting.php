<?php
namespace Plainware\PlainTracker;

class X_PageIndex_Setting
{
	public function navAdmin( array $ret, array $x )
	{
		$ret[ '81-setting' ] = [ '.setting', '__Settings__' ];
		return $ret;
	}
}