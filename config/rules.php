<?php 

return [
    'validate_createTimesheet' => [
        'start_date' => 'required',
        // 'projectid' => 'required',
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
    'assignTaskAdd' => [
        'worker_id' => 'required',
        'timesheet_id' => 'required',
        'date' => 'required',
        'total_hours' => 'required',
    ],
    'updateAssignTaskCheckbox' => [
        'timesheet_id' => 'required',
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
    'makeUniversalWorker' => [
        'firstname' => 'required',
        'lastname' => 'required',
        'email' => 'required',
        'country' => 'required'
    ],
    'inviteUniversalWorker' => [
        'worker_id' => 'required',
        'timesheet_id' => 'required'
    ],
    'accept_reject_invitation' => [
        'status' => 'required',
        'worker_id' => 'required'
    ],
];