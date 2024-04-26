<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\TimeSheet;
use App\Models\Attendance;
use App\Models\LocalWorker;
use App\Models\ProjectList;
use App\Helpers\ResponseHelper;
use BaconQrCode\Encoder\QrCode;
use Illuminate\Support\Facades\Response;

class TimeSheetServices
{

    function __construct(ResponseHelper $responseHelper)
    {
        $this->responseHelper = $responseHelper;
    }

    public function storeTimesheet($request)
    {
        $userid = auth()->user()->id;
        $start_date = date('Y-m-d', strtotime($request['start_date']));
        if (isset($request['end_date']) && !empty($request['end_date'])) {
            $end_date = date('Y-m-d', strtotime($request['end_date']));
        } else {
            $end_date = null;
        }
        if ($end_date < $start_date) {
            return $this->responseHelper->api_response(null, 422, "error", "End date must be after the start date");
        } else {
            $createTimesheet = new TimeSheet();
            $createTimesheet->user_id = $userid;
            $createTimesheet->timesheet_id = $request['timesheet_id'];
            $createTimesheet->project_id = $request['projectid'];
            $createTimesheet->start_date = $start_date;
            $createTimesheet->end_date = $end_date;
            $createTimesheet->status = $request['status'];
            $createTimesheet->localwork = $request['localwork'];
            $createTimesheet->scanning = $request['scanning'];
            $createTimesheet->hours = $request['hours'];
            if (isset($request['break']) && $request['break'] == 1) {
                $createTimesheet->break = $request['break'];
                $createTimesheet->break_duration = $request['break_duration'];
                $createTimesheet->break_duration_type = $request['break_duration_type'];
            }
            $assignAdminData = collect($request['assign_admin'])->map(function ($adminData) {
                return [
                    'admin_id' => $adminData['admin_id'],
                    'role' => json_encode([
                        'manage_time' => $adminData['manage_time'],
                        'manage_worker' => $adminData['manage_worker']
                    ])
                ];
            })->toJson();

            $createTimesheet->assign_admin = $assignAdminData;
            $createTimesheet->timesheet_qr = $this->generateQR($request['projectid']);
            $createTimesheet->save();
            $timesheetData = TimeSheet::with('project')->where('id', $createTimesheet->id)->first();
            return $this->responseHelper->api_response($timesheetData, 200, "success", 'Timesheet created.');
        }
    }


    public function updateTimesheet($request)
    {
        $userid = auth()->user()->id;
        $timesheetData = TimeSheet::where('user_id', $userid)->where('id', $request['timesheetid'])->first();
        if (empty($timesheetData)) {
            return $this->responseHelper->api_response(null, 422, "error", "Timesheet does not exist to update.");
        } else {
            $start_date = date('Y-m-d', strtotime($request['start_date']));
            if (isset($request['end_date']) && !empty($request['end_date'])) {
                $end_date = date('Y-m-d', strtotime($request['end_date']));
            } else {
                $end_date = null;
            }
            if ($end_date < $start_date) {
                return $this->responseHelper->api_response(null, 422, "error", "End date must be after the start date");
            } else {
                $timesheetData->start_date = $start_date;
                $timesheetData->end_date = $end_date;
                $timesheetData->project_id = $request['projectid'];
                $timesheetData->status = $request['status'];
                $timesheetData->localwork = $request['localwork'];
                $timesheetData->scanning = $request['scanning'];
                $timesheetData->hours = $request['hours'];
                if (isset($request['break']) && $request['break'] == 1) {
                    $timesheetData->break = $request['break'];
                    $timesheetData->break_duration = $request['break_duration'];
                    $timesheetData->break_duration_type = $request['break_duration_type'];
                }
                $assignAdminData = collect($request['assign_admin'])->map(function ($adminData) {
                    return [
                        'admin_id' => $adminData['admin_id'],
                        'role' => json_encode([
                            'manage_time' => $adminData['manage_time'],
                            'manage_worker' => $adminData['manage_worker']
                        ])
                    ];
                })->toJson();
                $timesheetData->assign_admin = $assignAdminData;
                $timesheetData->save();
                $timesheetData = TimeSheet::with('project')->where('id', $timesheetData->id)->first();
                return $this->responseHelper->api_response($timesheetData, 200, "success", 'Timesheet updated.');
            }
        }
    }



    public function showTimesheet($id)
    {
        $timesheetData = TimeSheet::with('project')->where('id', $id)->first();
        if (!empty($timesheetData)) {
            return $this->responseHelper->api_response($timesheetData, 200, "success", 'Timesheet details.');
        } else {
            return $this->responseHelper->api_response(null, 422, "error", "This timesheet does not exist.");
        }
    }
    public function showallTimesheet()
    {
        $timesheetData = TimeSheet::with('project')->orderBy('created_at', 'desc')->get();
        if (!empty($timesheetData)) {
            return $this->responseHelper->api_response($timesheetData, 200, "success", 'Timesheet details.');
        } else {
            return $this->responseHelper->api_response(null, 422, "error", "This timesheet does not exist.");
        }
    }

    public function deleteTimesheet($id)
    {
        $timesheetData = TimeSheet::where('id', $id)->first();
        if (!empty($timesheetData)) {
            $timesheetData->delete();
            return $this->responseHelper->api_response(null, 200, "success", 'Timesheet deleted.');
        } else {
            return $this->responseHelper->api_response(null, 422, "error", "This timesheet does not exist.");
        }
    }

    public function generateTimeSheetId()
    {
        $timesheetCount = TimeSheet::count();
        $newTimeSheetId = $timesheetCount + 1;
        return $this->responseHelper->api_response(['timesheetId' => $newTimeSheetId], 200, "success", 'Time sheet ID generated successfully.');
    }


    public function generateQR($projectId)
    {
        $projectData = ProjectList::where('id', $projectId)->first();
        if (!empty($projectData)) {
            $qrDirectoryPath = public_path('storage/QRCODE/');
            if (!file_exists($qrDirectoryPath)) {
                mkdir($qrDirectoryPath, 0777, true);
            }
            $checkQRExist = $qrDirectoryPath . 'project_' . $projectData->id . '_qrcode.png';
            if (!file_exists($checkQRExist)) {
                $qrfilename = $this->responseHelper->GenerateQR($projectData);
                $original_dir_path = $qrfilename;
                TimeSheet::where('project_id', $projectData->id)->update(['timesheet_qr' => $original_dir_path]);
            } else {
                $qrfiledata1 = TimeSheet::where('project_id', $projectData->id)->first();

                if (isset($qrfiledata1->timesheet_qr) && !is_null($qrfiledata1->timesheet_qr)) {
                    $original_dir_path = $qrfiledata1->timesheet_qr;
                } else {
                    $qrfilename = $this->responseHelper->GenerateQR($projectData);
                    $original_dir_path = $qrfilename;
                    TimeSheet::where('project_id', $projectData->id)->update(['timesheet_qr' => $original_dir_path]);
                }
            }
            return $original_dir_path;
        } else {
            return null;
        }
    }

    public function refreshQR($projectId)
    {
        $projectData = ProjectList::where('id', $projectId)->first();
        if (!empty($projectData)) {
            $qrDirectoryPath = public_path('storage/REFRESHQRCODE/');

            if (!file_exists($qrDirectoryPath)) {
                mkdir($qrDirectoryPath, 0777, true); // Creates the directory
            }

            $original_dir_path = $this->responseHelper->GenerateRefreshQR($projectData); // Generate new QR code
            TimeSheet::where('project_id', $projectData->id)->update(['timesheet_qr' => $original_dir_path]);
            return $this->responseHelper->api_response(['timesheet_qr' => $original_dir_path], 200, "success", 'Timesheet QR is regenerated.');
        } else {
            return $this->responseHelper->api_response(null, 422, "error", "This project does not exist.");
        }
    }

    public function addLocalWorker($request)
    {
        $existingCount = LocalWorker::count();
        foreach ($request as $data) {
            $createLocalWorker = new LocalWorker();
            $existingCount++;
            $formattedId = 'L-' . $existingCount;
            $createLocalWorker->worker_id = $formattedId;
            $createLocalWorker->timesheet_id = $data['timesheet_id'];
            $createLocalWorker->first_name = $data['first_name'];
            $createLocalWorker->last_name = $data['last_name'];
            $createLocalWorker->save();
        }
        return $this->responseHelper->api_response(null, 200, "success", 'Local Workers added successfully.');
    }
    public function InviteWorker($request)
    {
        $existingCount = LocalWorker::count();
        foreach ($request as $data) {
            $createLocalWorker = new LocalWorker();
            $existingCount++;
            $createLocalWorker->worker_id = $existingCount;
            $createLocalWorker->first_name = $data['first_name'];
            $createLocalWorker->last_name = $data['last_name'];
            $createLocalWorker->save();
        }
        return $this->responseHelper->api_response(null, 200, "success", 'Invited successfully.');
    }
    public function updateWorker($request)
    {
        $worker = LocalWorker::findOrFail($request['workerId']);
        if (!empty($worker)) {
            if (isset($request['status']) && !empty($request['status'])) {
                $worker->status = $request['status'];
            }
            if (isset($request['planned_hours']) && !empty($request['planned_hours'])) {
                $worker->planned_hours = $request['planned_hours'];
            }
            if (isset($request['work_assignment']) && !empty($request['work_assignment'])) {
                $workAssignmentArray = explode(',', $request['work_assignment']);
                $workAssignmentJson = json_encode($workAssignmentArray);
                $worker->work_assignment = $workAssignmentJson;
            }
            $worker->save();
            return $this->responseHelper->api_response($worker, 200, "success", 'Worker updated successfully.');
        } else {
            return $this->responseHelper->api_response(null, 422, "error", "Worker does not exist.");
        }
    }
    public function showWorkers()
    {
        $worker = LocalWorker::all();
        if (!empty($worker)) {
            return $this->responseHelper->api_response($worker, 200, "success", 'success.');
        } else {
            return $this->responseHelper->api_response(null, 422, "error", "Worker does not exist.");
        }
    }

    public function getTimesheetIdBasedWorker($timesheetId)
    {
        $worker = LocalWorker::where('timesheet_id', $timesheetId)->get();
        if (!empty($worker)) {
            return $this->responseHelper->api_response($worker, 200, "success", 'success.');
        } else {
            return $this->responseHelper->api_response(null, 422, "error", "data does not exist.");
        }
    }

    public function getTimesheetIdAndDateBasedWorker($timesheetid, $date)
    {
        // Convert the provided date to match the database format
        $formattedDate = Carbon::createFromFormat('d-m-Y', $date)->format('Y-m-d');
        // Retrieve all workers
        $workers = LocalWorker::with(['attendance' => function ($query) use ($formattedDate) {
            $query->select('id', 'worker_id', 'attendance', 'total_hours', 'date','approve')
                ->whereDate('date', $formattedDate);
        }])
            ->where('timesheet_id', $timesheetid)
            ->get();
        // Iterate over each worker and check if attendance matches the date
        foreach ($workers as $worker) {
            if ($worker->attendance && $worker->attendance->date !== $formattedDate) {
                $worker->attendance = null;
            }
        }
        return $this->responseHelper->api_response($workers, 200, "success", 'success.');
    }

    public function recordAttendance($request)
    {
        // Find the worker and timesheet
        $worker = LocalWorker::find($request['worker_id']);
        $timesheet = TimeSheet::where('timesheet_id', $request['timesheet_id'])->first();

        // Check if the worker and timesheet exist
        if (!$worker) {
            return $this->responseHelper->api_response(null, 422, "error", "Worker does not exist.");
        }

        if (!$timesheet) {
            return $this->responseHelper->api_response(null, 422, "error", "Timesheet does not exist.");
        }

        // Convert time from 12-hour format to 24-hour format and validate
        $timeEntries = [];
        $totalHours = 0;
        for ($i = 1; $i <= 3; $i++) {
            $inTime = $request['in_time' . $i];
            $outTime = $request['out_time' . $i];

            // Skip conversion and validation if both in and out times are empty
            if (empty($inTime) && empty($outTime)) {
                continue;
            }

            // If both in and out times are provided together or only one is provided, add them to the timeEntries array
            if (!empty($inTime) || !empty($outTime)) {
                // If only one of in time or out time is provided, set it to null
                if (empty($inTime)) {
                    $inTime = null;
                }
                if (empty($outTime)) {
                    $outTime = null;
                }
                $timeEntries[] = [
                    'in_time' => $inTime,
                    'out_time' => $outTime
                ];

                // Calculate difference in hours if both in and out times are provided
                if (!is_null($inTime) && !is_null($outTime)) {
                    $inTimeObj = Carbon::createFromFormat('h:i A', $inTime);
                    $outTimeObj = Carbon::createFromFormat('h:i A', $outTime);
                    $differenceInMinutes = $outTimeObj->diffInMinutes($inTimeObj);
                    $totalHours += $differenceInMinutes / 60; // Convert minutes to hours
                }
            }
        }

        // Round total hours to two decimal places
        $totalHours = round($totalHours, 2);

        // Check if the attendance ID is provided for updating
        if (!empty($request['attendance_id'])) {
            // Update existing attendance record
            $attendance = Attendance::find($request['attendance_id']);

            if (!$attendance) {
                return $this->responseHelper->api_response(null, 422, "error", "Attendance record does not exist.");
            }

            // Update the attendance record with the new data
            $attendance->attendance = json_encode($timeEntries);
            $attendance->total_hours = $totalHours;
            $attendance->save();

            return $this->responseHelper->api_response($attendance, 200, "success", 'Attendance updated.');
        } else {
            // Create new attendance record
            $attendance = new Attendance();
            $attendance->user_id = auth()->user()->id;
            $attendance->worker_id = $worker->id;
            $attendance->timesheet_id = $timesheet->timesheet_id;
            $attendance->attendance = json_encode($timeEntries);
            $attendance->date = Carbon::now();
            $attendance->total_hours = $totalHours;
            $attendance->save();

            return $this->responseHelper->api_response($attendance, 200, "success", 'Attendance recorded.');
        }
    }

    public function approveAttendance($request){
        $Attendance = Attendance::where('id', $request['attendance_id'])->first();
        if (!empty($Attendance)) {
            Attendance::where('id', $request['attendance_id'])->update(['approve'=>$request['approve']]);
            $UpdatedAttendace = Attendance::findOrFail($request['attendance_id']);
            return $this->responseHelper->api_response($UpdatedAttendace, 200, "success", 'success.');
        } else {
            return $this->responseHelper->api_response(null, 422, "error", "Attendance does not exist.");
        }
    }
    public function approveAllAttendance($request)
    {
        // Fetch all attendance records for the specified timesheet and date
        $formattedDate = Carbon::createFromFormat('d-m-Y', $request['date'])->format('Y-m-d');
        $Attendance = Attendance::where('timesheet_id', $request['timesheet_id'])
                                ->whereDate('date', $formattedDate)
                                ->get();
    
        // Check if any attendance records exist
        if ($Attendance->isNotEmpty()) {
            // Update each attendance record to mark as approved
            foreach ($Attendance as $attendance) {
                $attendance->approve = 1;
                $attendance->save();
            }
            // Return the updated attendance records
            return $this->responseHelper->api_response($Attendance, 200, "success", 'Success. Attendance approved.');
        } else {
            // No attendance records found
            return $this->responseHelper->api_response(null, 422, "error", "Attendance does not exist.");
        }
    }
    
}
