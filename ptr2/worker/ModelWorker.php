<?php
namespace Plainware\PlainTracker;

class _Worker
{
	public $id;

	public $title;
	public $description;
	public $email;
	public $stateId = 'active';
	public $userId;
}

class ModelWorker extends \Plainware\Model
{
	public static $class = _Worker::class;
	public static $required = [ 'title' ];
	public static $unique = [ 'title' ];
	public static $order = [ ['title', 'ASC'] ];

	public $self = __CLASS__;
	public $crud = CrudWorker::class;

	public function findByUserId( $userId )
	{
		$ret = null;
		if( ! $userId ) return $ret;

		$q = [];
		$q[] = [ 'userId', '=', $userId ];
		$q[] = [ 'limit', 1 ];
		$res = $this->self->find( $q );
		$ret = current( $res );

		return $ret;
	}
}