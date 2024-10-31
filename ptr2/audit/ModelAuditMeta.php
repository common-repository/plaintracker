<?php
namespace Plainware\PlainTracker;

class _AuditMeta
{
	// public $id;
	public $auditId;
	public $propName;
	public $valueOld;
}

class ModelAuditMeta extends \Plainware\Model
{
	public static $class = _AuditMeta::class;

	public $self = __CLASS__;
	public $crud = CrudAuditMeta::class;
}