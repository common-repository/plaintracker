<?php
namespace Plainware\PlainTracker;

class _Audit
{
	public $id;

	public $classId;
	public $objectId;

	public $changeAt;
	public $userId;
	public $description;

	public $meta = [];
}

class ModelAudit extends \Plainware\Model
{
	public static $class = _Audit::class;

	public $self = __CLASS__;
	public $crud = CrudAudit::class;

	public $t = \Plainware\Time::class;
	public $currentUserId = null;

	public $modelAuditMeta = ModelAuditMeta::class;

	public function setCurrentUserId( $id )
	{
		$this->currentUserId = $id ? $id : 0;
		return $this;
	}

	public function construct( array $a = [] )
	{
		$m = parent::construct( $a );

		if( null === $m->userId ){
			$m->userId = $this->currentUserId ? $this->currentUserId : 0;
		}

		if( null === $m->changeAt ){
			$now = $this->t->getNow();
			$m->changeAt = $now;
		}

		return $m;
	}

// with meta
	public function afterFind( array $ret )
	{
		if( ! $ret ) return;

		$listId = array_keys( $ret );
		$q2 = [];
		$q2[] = [ 'auditId', '=', $listId ];
		$listMeta = $this->modelAuditMeta->find( $q2 );
		foreach( $listMeta as $m2 ){
			$ret[ $m2->auditId ]->meta[ $m2->propName ] = $m2->valueOld;
		}

		return $ret;
	}

	public function beforeDeleteMany( array $q )
	{
		$listId = $this->self->findProp( 'id', $q );
		if( ! $listId ) return;

		$q2 = [];
		$q2[] = [ 'auditId', '=', $listId ];
		$this->modelAuditMeta->deleteMany( $q2 );
	}

	public function afterDelete( _Audit $m )
	{
		$q2 = [];
		$q2[] = [ 'auditId', '=', $m->id ];
		$this->modelAuditMeta->deleteMany( $q2 );
	}

	public function afterCreate( _Audit $m )
	{
		$listAuditMeta = [];
		foreach( $m->meta as $propName => $valueOld ){
			$m2 = $this->modelAuditMeta->construct();
			$m2->auditId = $m->id;
			$m2->propName = $propName;
			$m2->valueOld = $valueOld;
			$listAuditMeta[] = $m2;
		}
		$this->modelAuditMeta->createMany( $listAuditMeta );

		return $m;
	}
}