<?php
namespace Plainware\PlainTracker;

return [
	[ \Plainware\CrudInstall::class . '::migrate', CrudApproverWorker::class . '::migrate' ],

	[ PageIndex::class . '::render', X_PageIndex_ApproverWorker::class . '::render' ],
	[ PageIndex::class . '::navAdmin', X_PageIndex_ApproverWorker::class . '::navAdmin' ],

	[ ModelWorker::class . '::', X_ModelWorker_ApproverWorker::class . '::' ],

	[ PageProfileWorker::class . '::', X_PageProfileWorker_ApproverWorker::class . '::' ],
	[ PageWorkerId::class . '::', X_PageWorkerId_ApproverWorker::class . '::' ],
	[ PageUserId::class . '::', X_PageUserId_ApproverWorker::class . '::' ],

	[ PageTimesheetId::class . '::', X_PageTimesheetId_ApproverWorker::class . '::' ],
	[ PageTimesheetNew::class . '::', X_PageTimesheetNew_ApproverWorker::class . '::' ],

	[ PageRecordEdit::class . '::', X_PageRecordEdit_ApproverWorker::class . '::' ],
];