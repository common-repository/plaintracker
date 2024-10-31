<?php
namespace Plainware\PlainTracker;

class X_Setting_Timesheet
{
	public function getDefaults( array $ret )
	{
		$ret[ 'timesheet_pay_period' ] = 'week'; // week, biweek, semimonth, month
		return $ret;
	}
}