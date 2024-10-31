<?php
namespace Plainware\PlainTracker;

return [
	[ \Plainware\CrudInstall::class . '::migrate', CrudProject::class . '::migrate' ],

	[ PageIndex::class . '::navAdmin', X_PageIndex_Project::class . '::navAdmin' ],
];