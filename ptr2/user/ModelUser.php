<?php
namespace Plainware\PlainTracker;

class _User
{
	public $id;
	public $title;
	public $email;
}

class ModelUser extends \Plainware\Model
{
	public $self = __CLASS__;

	public static $class = _User::class;
	// public static $required = [ 'title' ];
	// public static $unique = [ 'title' ];
	public static $order = [ ['title', 'ASC'] ];

	public $crud = CrudUser::class;
}