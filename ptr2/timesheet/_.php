<?php
namespace Plainware\PlainTracker;

return [
	[ \Plainware\CrudInstall::class . '::migrate', CrudTimesheet::class . '::migrate' ],
	[ \Plainware\Setting::class . '::', X_Setting_Timesheet::class . '::' ],

	[ ModelProject::class . '::', X_ModelProject_Timesheet::class . '::' ],
	[ ModelRecord::class . '::', X_ModelRecord_Timesheet::class . '::' ],

	[ ModelTimesheet::class . '::', ModelTimesheet_Date::class . '::' ],
	[ ModelTimesheet::class . '::', ModelTimesheet_Record::class . '::' ],

	[ PageIndex::class . '::', X_PageIndex_Timesheet::class . '::' ],

	[ PageTimesheetIndex::class . '::', PageTimesheetIndex_Print::class . '::' ],
	[ PageTimesheetIndex::class . '::', PageTimesheetIndex_Filter::class . '::' ],
	[ PageTimesheetIndex::class . '::', PageTimesheetIndex_Print::class . '::' ],

	[ PageWorkerId::class . '::', X_PageWorkerId_Timesheet::class . '::' ],

	[ PageTimesheetRecordNew::class . '::', PageTimesheetRecordNew_Activity::class . '::' ],
	[ PageTimesheetRecordNew::class . '::', PageTimesheetRecordNew_Date::class . '::' ],

	[ PageTimesheetId::class . '::', PageTimesheetId_Print::class . '::' ],
	[ PageTimesheetId::class . '::', PageTimesheetId_Action::class . '::' ],

	[ PageMyTimesheetId::class . '::', PageMyTimesheetId_Print::class . '::' ],
	[ PageMyTimesheetId::class . '::', PageMyTimesheetId_Submit::class . '::' ],

	[ PageTimesheetDashboard::class . '::', PageTimesheetDashboard_Print::class . '::' ],

	[ PresenterRecord::class . '::', X_PresenterRecord_Timesheet::class . '::' ],
];