<?php
namespace Plainware\PlainTracker;

class _ApproverWorker
{
	public $approverId;
	public $workerId;
}

class ModelApproverWorker extends \Plainware\Model
{
	public static $class = _ApproverWorker::class;
	public $self = __CLASS__;
	public $crud = CrudApproverWorker::class;
}