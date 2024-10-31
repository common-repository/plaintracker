<?php
namespace Plainware\PlainTracker;

class ModelTimesheet_Date
{
	public $self = __CLASS__;

	public $model = ModelTimesheet::class;
	public $settingTimesheet = SettingTimesheet::class;

	public function createError( array $ret, _Timesheet $m )
	{
	// adjust dates regarding worker
		list( $d1, $d2 ) = $this->settingTimesheet->getPayPeriod( $m->startDate, $m->workerId );

		$m->startDate = $d1;
		$m->endDate = $d2;

	// check if already have overlapping timesheets for this worker
		$q = [];
		$q[] = [ 'workerId', '=', $m->workerId ];
		$q[] = [ 'startDate', '<=', $m->endDate ];
		$q[] = [ 'endDate', '>=', $m->startDate ];
		$q[] = [ 'limit', 1 ];
		$res = $this->model->findProp( 'id', $q );
		if( $res ){
			$ret['startDate'] = '__There is another timesheet for this date.__';
			// $ret['endDate'] = 'overlapping';
		}

		return $ret;
	}

	public function beforeCreate( _Timesheet $m )
	{
	// adjust dates regarding worker
		list( $d1, $d2 ) = $this->settingTimesheet->getPayPeriod( $m->startDate, $m->workerId );

		$m->startDate = $d1;
		$m->endDate = $d2;

		return $m;
	}
}