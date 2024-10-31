<?php
namespace Plainware;

return [
	[ Setting::class . '::getDefaults', X_Setting_Time::class . '::getDefaults' ],
	[ Time::class . '::$timeFormat', '@' . Setting_Time::class . '::timeFormat' ],
	[ Time::class . '::$dateFormat', '@' . Setting_Time::class . '::dateFormat' ],
	[ Time::class . '::$weekStartsOn', '@' . Setting_Time::class . '::weekStartsOn' ],
	[ Time::class . '::$timezone', '@' . Setting_Time::class . '::timezone' ],
	// [ PageSetting::class . '::nav', X_PageSetting_Time::class . '::nav' ],
];