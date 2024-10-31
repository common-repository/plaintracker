<?php
namespace Plainware\PlainTracker;

return [
	[ \Plainware\CrudInstall::class . '::migrate', CrudAudit::class . '::migrate' ],
	[ \Plainware\CrudInstall::class . '::migrate', CrudAuditMeta::class . '::migrate' ],

	[ \Plainware\Handler::class . '::x', X_Handler_Audit::class . '::x' ],

// with meta
	[ ModelAudit::class . '::create', ModelAudit::class . '::afterCreate' ],
	[ ModelAudit::class . '::find', ModelAudit::class . '::afterFind' ],
	[ ModelAudit::class . '::delete', ModelAudit::class . '::afterDelete' ],
	[ ModelAudit::class . '::deleteMany', ModelAudit::class . '::beforeDeleteMany', -1 ],
];