<?php
namespace Plainware\PlainTracker;

class PageTimesheetId_Print
{
	public $self = __CLASS__;

	public function nav( array $ret, array $x )
	{
		$ret[ '87-print' ] = [ '.?layout-=print&target=_blank', '<span>__Print view__</span><i>&nearr;</i>' ];
		return $ret;
	}
}