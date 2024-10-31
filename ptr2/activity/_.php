<?php
namespace Plainware\PlainTracker;

return [
	[ \Plainware\CrudInstall::class . '::migrate', CrudActivity::class . '::migrate' ],
	[ PageIndex::class . '::navAdmin', X_PageIndex_Activity::class . '::navAdmin' ],
];