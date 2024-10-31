<?php
namespace Plainware\PlainTracker;

class X_PageProjectId_Record
{
	public $self = __CLASS__;

	public function nav( array $ret, array $x )
	{
		$id = $x['id'] ?? null;
		if( ! $id ) return $ret;

		$p = [];
		$p['project'] = $id;
		$p['iknow'] = [ 'project' ];

		$ret[ '35-record' ] = [ ['.record-index', $p], '__Browse time records__' ];

		return $ret;
	}
}