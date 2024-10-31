<?php
namespace Plainware;

class X_Setting_Time
{
	public function getDefaults( array $ret )
	{
		$ret[ 'time_week_starts' ] = 7;
		$ret[ 'time_time_format' ] = 'g:ia';
		$ret[ 'time_date_format' ] = 'j M Y';
		$ret[ 'time_timezone' ] = '';
		return $ret;
	}
}