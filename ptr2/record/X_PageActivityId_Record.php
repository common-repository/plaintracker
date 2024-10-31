<?php
namespace Plainware\PlainTracker;

class X_PageActivityId_Record
{
	public $self = __CLASS__;

	public function nav( array $ret, array $x )
	{
		$id = $x['id'] ?? null;
		if( ! $id ) return $ret;

		$p = [];
		$p['activity'] = $id;
		$p['iknow'] = [ 'activity' ];

		$ret[ '31-record' ] = [ ['.record-index', $p], '__Browse time records__' ];

		return $ret;
	}
}