<?php
namespace Plainware\PlainTracker;

return [
	[ \Plainware\Layout::class . '::', Layout_UserOverride::class . '::' ],

	[ Auth::class . '::', Auth_UserOverride::class . '::', 7 ],

	[ PageUserId::class . '::', PageUserId_UserOverride::class . '::' ],
	[ PageWorkerId::class . '::', PageWorkerId_UserOverride::class . '::' ],
];