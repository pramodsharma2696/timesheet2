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
];