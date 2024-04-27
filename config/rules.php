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
    'addLocalWorkercsv' => [
        'timesheet_id' => 'required',
        'file' => 'required',
    ],
    'Attendance' => [
        'worker_id' => 'required',
        'timesheet_id' => 'required'
    ],
    'approveAttendance' => [
        'attendance_id' => 'required',
        'approve' => 'required'
    ],
    'assignTaskHours' => [
        'worker_id' => 'required',
        'timesheet_id' => 'required',
        'date' => 'required',
        'assign_task_hours' => 'required|array'
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