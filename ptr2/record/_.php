<?php
namespace Plainware\PlainTracker;

return [
	[ \Plainware\CrudInstall::class . '::migrate', CrudRecord::class . '::migrate' ],

	[ PageIndex::class . '::', X_PageIndex_Record::class . '::' ],

	// [ ModelRecord::class . '::create', X_ModelRecord_Record::class . '::create' ],
	[ ModelRecord::class . '::update', X_ModelRecord_Record::class . '::update' ],
	[ ModelProject::class . '::delete', X_ModelProject_Record::class . '::delete' ],

	[ PageRecordIndex::class . '::', PageRecordIndex_Navi::class . '::' ],

	[ PageActivityId::class . '::', X_PageActivityId_Record::class . '::' ],
	[ PageProjectId::class . '::', X_PageProjectId_Record::class . '::' ],
	[ PageWorkerId::class . '::', X_PageWorkerId_Record::class . '::' ],
];