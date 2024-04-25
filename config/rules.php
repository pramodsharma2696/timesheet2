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
    'InviteWorker' => [
        'first_name' => 'required',
        'last_name' => 'required',
    ],
];