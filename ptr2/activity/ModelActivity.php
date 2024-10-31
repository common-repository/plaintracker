<?php
namespace Plainware\PlainTracker;

class _Activity
{
	public $id;

	public $title;
	public $stateId = 'active';
	public $showOrder;
}

class ModelActivity extends \Plainware\Model
{
	public static $class = _Activity::class;
	public static $required = [ 'title' ];
	public static $unique = [ 'title' ];
	public static $order = [ ['title', 'ASC'] ];

	public $self = __CLASS__;
	public $crud = CrudActivity::class;
}