<?php
namespace Plainware\PlainTracker;

class _Record
{
	public $id;

	public $startDate;
	public $duration;

	public $workerId;
	public $projectId;
	public $activityId;

	public $clockIn = 0;
	public $clockOut = 0;

	// public $stateId = 'submit';
	public $stateId;
}

class ModelRecord extends \Plainware\Model
{
	public static $class = _Record::class;
	public $self = __CLASS__;
	public $crud = CrudRecord::class;

	public function enum( $propName, $m = null )
	{
		if( 'stateId' == $propName ){
			$ret = [ 'clockin', 'submit', 'approve' ];
			return $ret;
		}
		return parent::enum( $propName, $m );
	}
}