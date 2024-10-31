<?php
namespace Plainware\PlainTracker;

class X_PageIndex_App
{
	public $self = __CLASS__;
	public $modelWorker = ModelWorker::class;

	public function navAdmin( array $ret, array $x )
	{
		$ret[ '88-help' ] = [ '.admin-help', '__Administration guide__' ];
		return $ret;
	}
}