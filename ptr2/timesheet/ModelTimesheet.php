<?php
namespace Plainware\PlainTracker;

class _Timesheet
{
	public $id;

	public $startDate;
	public $endDate;
	public $workerId;

	public $stateId = 'draft';
}

class ModelTimesheet extends \Plainware\Model
{
	public static $class = _Timesheet::class;
	public $self = __CLASS__;
	public $crud = CrudTimesheet::class;
	// public static $order = [ ['startDate', 'DESC'], ['id', 'ASC'] ];

	public function enum( $propName, $m = null )
	{
		if( 'stateId' == $propName ){
			$ret = [ 'draft', 'submit', 'approve', 'process' ];
			return $ret;
		}
		return parent::enum( $propName, $m );
	}
}