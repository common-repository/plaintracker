<?php
namespace Plainware\PlainTracker;

return [
	[ \Plainware\CrudInstall::class . '::migrate', CrudWorker::class . '::migrate' ],

	[ ModelWorker::class . '::', ModelWorker_User::class . '::' ],

	[ PageIndex::class . '::navAdmin', X_PageIndex_Worker::class . '::navAdmin' ],
	[ PageWorkerId::class . '::', PageWorkerId_User::class . '::' ],
];