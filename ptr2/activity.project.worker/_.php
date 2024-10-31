<?php
namespace Plainware\PlainTracker;

return [
	[ \Plainware\CrudInstall::class . '::migrate', CrudActivityProjectWorker::class . '::migrate' ],

	[ ModelActivity::class . '::', X_ModelActivity_ActivityProjectWorker::class . '::' ],
	[ ModelProject::class . '::', X_ModelProject_ActivityProjectWorker::class . '::' ],
	[ ModelWorker::class . '::', X_ModelWorker_ActivityProjectWorker::class . '::' ],

	[ PageProjectId::class . '::', X_PageProjectId_ActivityProjectWorker::class . '::' ],
	[ PageWorkerId::class . '::', X_PageWorkerId_ActivityProjectWorker::class . '::' ],

	[ PageRecordEdit::class . '::', X_PageRecordEdit_ActivityProjectWorker::class . '::' ],

	[ PageTimesheetId::class . '::', X_PageTimesheetId_ActivityProjectWorker::class . '::' ],

	[ PageIndex::class . '::render', X_PageIndex_ActivityProjectWorker::class . '::render' ],
];