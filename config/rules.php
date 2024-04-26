<?php 

return [
    'validate_createTimesheet' => [
        'start_date' => 'required',
        'end_date' => 'required',
        'projectid' => 'required',
        'break_duration' => 'required'
    ],
    'validate_updateTimesheet' => [
        'timesheetid' => 'required'
    ],
    'addLocalWorker' => [
        'timesheet_id' => 'required',
        'first_name' => 'required',
        'last_name' => 'required',
    ],
    'Attendance' => [
        'worker_id' => 'required',
        'timesheet_id' => 'required'
    ],
    'approveAttendance' => [
        'attendance_id' => 'required',
        'approve' => 'required'
    ],
    'approveAllAttendance' => [
        'timesheet_id' => 'required',
        'date' => 'required'
    ],
    'getTimesheetIdAndDateBasedWorker' => [
        'date' => 'required',
        'timesheet_id' => 'required'
    ],
    'InviteWorker' => [
        'first_name' => 'required',
        'last_name' => 'required',
    ],
];