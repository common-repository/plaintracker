<?php
namespace Plainware\PlainTracker;

class _ActivityProjectWorker
{
	public $activityId;
	public $projectId;
	public $workerId;
}

class ModelActivityProjectWorker extends \Plainware\Model
{
	public static $class = _ActivityProjectWorker::class;
	public $self = __CLASS__;
	public $crud = CrudActivityProjectWorker::class;
}