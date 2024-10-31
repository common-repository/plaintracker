<?php
namespace Plainware\PlainTracker;

class _Project
{
	public $id;
	public $title;
	public $stateId = 'active';

	public $startDate = 0;
	public $endDate = 99999999;

	public $startSubmit = 0;
	public $endSubmit = 999999999999;

	public $payPeriod = 'week';
}

class ModelProject extends \Plainware\Model
{
	public static $class = _Project::class;
	// public static $required = [ 'title' ];
	public static $unique = [ 'title' ];
	// public static $order = [ ['title', 'ASC'] ];
	public static $order = [ ['startDate', 'ASC'] ];

	public $self = __CLASS__;
	public $crud = CrudProject::class;
}