<?php
namespace Plainware\PlainTracker;

class X_PageTimesheetId_AuditTimesheet
{
	public function nav( array $ret, array $x )
	{
		if( ! isset($x['$m']) ) return $ret;

		$m = $x[ '$m' ];
		$ret[ '81-audit' ] = [ '.timesheet-audit?id=' . $m->id, '__History__' ];

		return $ret;
	}
}