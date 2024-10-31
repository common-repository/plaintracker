<?php
namespace Plainware\PlainTracker;

return [
	[ \Plainware\CrudInstall::class . '::migrate', ModelAppInit::class . '::migrate' ],
	[ \Plainware\Layout::class . '::render', X_Layout_App::class . '::render' ],

	[ PageIndex::class . '::navAdmin', X_PageIndex_App::class . '::navAdmin' ],
];