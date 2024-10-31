<?php
namespace Plainware\PlainTracker;

class SettingTimesheet
{
	public $self = __CLASS__;

	public $modelWorker = ModelWorker::class;

	public $setting = \Plainware\Setting::class;
	public $t = \Plainware\Time::class;

	public function setPayPeriod( $payPeriod, $workerId )
	{
		return $this->setting->set( 'timesheet_pay_period', $payPeriod, $workerId );
	}

	public function getPayPeriod( $date, $workerId )
	{
		$range = $this->setting->get( 'timesheet_pay_period', $workerId );

		// $worker = $this->modelWorker->findById( $workerId );
		// if( $worker && $worker->payPeriod ){
			// $range = $worker->payPeriod;
		// }

		if( null === $date){
			return $range;
		}

		$d1 = $date;

		if( 'week' == $range ) $d1 = $this->t->getStartWeek( $d1 );
		if( 'month' == $range ) $d1 = $this->t->getStartMonth( $d1 );
		$d1 = $this->t->getDate( $d1 );

		$d2 = $d1;
		if( 'week' == $range ) $d2 = $this->t->getEndWeek( $d2 );
		if( 'month' == $range ) $d2 = $this->t->getEndMonth( $d2 );
		$d2 = $this->t->getDate( $d2 );

// echo "$date: $range: $d1, $d2<br>";

		$ret = [ $d1, $d2 ];
		return $ret;
	}
}