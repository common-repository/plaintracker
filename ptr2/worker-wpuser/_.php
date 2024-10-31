<?php
namespace Plainware\PlainTracker;

return [
	[ CrudWorker::class . '::', CrudWorkerWpuser::class . '::', 0 ],

	[ PageWorkerId::class . '::', X_PageWorkerId_WorkerWpuser::class . '::' ],
	[ PageWorkerNew::class . '::', X_PageWorkerNew_WorkerWpuser::class . '::' ],
	[ PageWorkerEdit::class . '::', X_PageWorkerEdit_WorkerWpuser::class . '::' ],
	[ PageWorkerUser::class . '::', X_PageWorkerUser_WorkerWpuser::class . '::' ],
	[ PageWorkerDelete::class . '::', X_PageWorkerDelete_WorkerWpuser::class . '::' ],
];