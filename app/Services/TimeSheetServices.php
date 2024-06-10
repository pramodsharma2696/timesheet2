<?php

namespace App\Services;

use Carbon\Carbon;
use League\Csv\Reader;
use App\Models\TimeSheet;
use App\Models\Attendance;
use App\Models\LocalWorker;
use App\Models\ProjectList;
use League\ISO3166\ISO3166;
use App\Helpers\ResponseHelper;
use App\Models\UniversalWorker;
use BaconQrCode\Encoder\QrCode;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;

class TimeSheetServices
{

    function __construct(ResponseHelper $responseHelper)
    {
        $this->responseHelper = $responseHelper;
        $this->iso3166 = new ISO3166();
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
    if (!is_null($end_date)) {
        if ($end_date < $start_date) {
            return $this->responseHelper->api_response(null, 422, "error", "End date must be after the start date");
        }else{
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
                        'manage_worker' => $adminData['manage_worker'],
                        'manage_approval' => $adminData['manage_approval']
                    ])
                ];
            })->toJson();
            $createTimesheet->planned_hours = $request['planned_hours'];
            $createTimesheet->assign_admin = $assignAdminData;
            $createTimesheet->timesheet_qr = $this->generateQR($request['projectid']);
            $createTimesheet->save();
            $timesheetData = TimeSheet::with('project')->where('id', $createTimesheet->id)->first();
            return $this->responseHelper->api_response($timesheetData, 200, "success", 'Timesheet created.');
        }
    }else{
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
                        'manage_worker' => $adminData['manage_worker'],
                        'manage_approval' => $adminData['manage_approval']
                    ])
                ];
            })->toJson();
            $createTimesheet->planned_hours = $request['planned_hours'];
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
            $newStartDate = date('Y-m-d', strtotime($request['start_date']));
            $newEndDate = isset($request['end_date']) && !empty($request['end_date']) ? date('Y-m-d', strtotime($request['end_date'])) : null;

            $firstAattendanceDate = Attendance::where('timesheet_id',$timesheetData->timesheet_id)->orderBy('date','asc')->first();
            $lastAttendanceDate = Attendance::where('timesheet_id',$timesheetData->timesheet_id)->orderBy('date','desc')->first();

    
            // Check if the new start date is greater than the existing start date
            if ($newStartDate > $firstAattendanceDate->date) {
                return $this->responseHelper->api_response(null, 422, "error", "Start date cannot be changed to a later date.");
            }
    
            // Check if the new end date is less than the existing end date
            if (!is_null($newEndDate) && $newEndDate < $lastAttendanceDate->date) {
                return $this->responseHelper->api_response(null, 422, "error", "End date cannot be changed to an earlier date.");
            }
    
            // Validate the end date to be after the start date
            if (!is_null($newEndDate) && $newEndDate < $newStartDate) {
                return $this->responseHelper->api_response(null, 422, "error", "End date must be after the start date.");
            }
    
            // Proceed with the update
            $timesheetData->start_date = $newStartDate;
            $timesheetData->end_date = $newEndDate;
            $timesheetData->project_id = $request['projectid'];
            $timesheetData->status = $request['status'];
            $timesheetData->localwork = $request['localwork'];
            $timesheetData->scanning = $request['scanning'];
            $timesheetData->hours = $request['hours'];
            $timesheetData->break = $request['break'];
    
            if (isset($request['break']) && $request['break'] == 1) {
                $timesheetData->break_duration = $request['break_duration'];
                $timesheetData->break_duration_type = $request['break_duration_type'];
            }
    
            $assignAdminData = collect($request['assign_admin'])->map(function ($adminData) {
                return [
                    'admin_id' => $adminData['admin_id'],
                    'role' => json_encode([
                        'manage_time' => $adminData['manage_time'],
                        'manage_worker' => $adminData['manage_worker'],
                        'manage_approval' => $adminData['manage_approval']
                    ])
                ];
            })->toJson();
            $timesheetData->planned_hours = $request['planned_hours'];
            $timesheetData->assign_admin = $assignAdminData;
            $timesheetData->save();
            $timesheetData = TimeSheet::with('project')->where('id', $timesheetData->id)->first();
    
            return $this->responseHelper->api_response($timesheetData, 200, "success", 'Timesheet updated.');
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
            $query->select('id', 'worker_id', 'attendance', 'total_hours', 'date','approve','assigned_task_hours')
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

    $timeEntries = [];
    $totalHours = 0;
    $mainHours = 0;

    // Check if we need to calculate hours based on the timesheet's calculate_hours column
    if ($timesheet->hours === '1') {
     
        // Convert time from 12-hour format to 24-hour format and validate
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
        $mainHours = $totalHours;
        

        // Deduct break duration if set
        $breakTotal = 0;
        if ($timesheet->break === '1' && isset($timesheet->break_duration) && isset($timesheet->break_duration_type)) {
            // Convert break duration to hours based on break_duration_type
            switch ($timesheet->break_duration_type) {
                case 'minutes':
                    $breakTotal = $timesheet->break_duration / 60;
                    break;
                case 'hours':
                    $breakTotal = $timesheet->break_duration;
                    break;
                default:
                    $breakTotal = 0; // Set to 0 if the type is unknown
                    break;
            }
            // Check if breakTotal is more than totalHours
            if ($breakTotal > $totalHours) {
                return $this->responseHelper->api_response(null, 422, "error", "Break duration cannot be more than total working hours.");
            }
          
            $totalHours -= $breakTotal;
            $totalHours = max($totalHours, 0); // Ensure total hours is not negative
        }
       
        // Check if total hours exceed planned hours, and if planned_hours is not null
        $totalRecordedHours = $worker->attendance()->sum('total_hours');
        $plannedHours = $worker->planned_hours;
        $newTotalHours = $totalRecordedHours + $totalHours;
        //dd($totalHours, $totalRecordedHours,$plannedHours, $newTotalHours );
        if ($timesheet->planned_hours === '1' && !is_null($plannedHours) && $newTotalHours > $plannedHours) {
            return $this->responseHelper->api_response(null, 422, "error", "Your time has exceeded allocated hours.");
        }
    } else {
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
            }
        }
    }

    // Check if the attendance ID is provided for updating
    if (!empty($request['attendance_id'])) {
        // Update existing attendance record
        $attendance = Attendance::find($request['attendance_id']);

        if (!$attendance) {
            return $this->responseHelper->api_response(null, 422, "error", "Attendance record does not exist.");
        }
        // Update the attendance record with the new data
        $attendance->attendance = json_encode($timeEntries);
        if ($timesheet->hours === '1') {
            $attendance->total_hours = $totalHours;
            $attendance->main_hours = $mainHours;
        }
        $attendance->save();
        $attendanceData = Attendance::find($request['attendance_id']);
        return $this->responseHelper->api_response($attendanceData, 200, "success", 'Attendance updated.');
    } else {
        // Create new attendance record
        $attendance = new Attendance();
        $attendance->user_id = auth()->user()->id;
        $attendance->worker_id = $worker->id;
        $attendance->timesheet_id = $timesheet->timesheet_id;
        $attendance->attendance = json_encode($timeEntries);
        $attendance->date = Carbon::createFromFormat('d-m-Y', $request['date'])->format('Y-m-d');
        $attendance->total_hours = $totalHours;
        $attendance->main_hours = $mainHours;
        $attendance->save();
        $attendanceData = Attendance::find($attendance->id);
        
        return $this->responseHelper->api_response($attendanceData, 200, "success", 'Attendance recorded.');
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
                if($request['approve'] === '1'){
                    $attendance->approve = '1';
                    $attendance->save();
                }
                if($request['approve'] === '0'){
                    $attendance->approve = '0';
                    $attendance->save();
                }
            }
            // Return the updated attendance records
            return $this->responseHelper->api_response($Attendance, 200, "success", 'Success.');
        } else {
            // No attendance records found
            return $this->responseHelper->api_response(null, 422, "error", "Attendance does not exist.");
        }
    }

    public function assignTaskHours($request)
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

        $formattedDate = Carbon::createFromFormat('d-m-Y', $request['date'])->format('Y-m-d');
        $attendance = Attendance::where('worker_id', $worker->id)
                                ->where('timesheet_id', $timesheet->timesheet_id)
                                ->whereDate('date', $formattedDate)
                                ->first();

        $assignTaskHoursJson = json_encode([]);
        $totalHours = 0;
        
        $breakTotal = 0;

        if (!is_null($attendance)) {
            if (!is_null($attendance->total_hours)) {
                $totalHours = $attendance->total_hours;
            }
            if (!is_null($attendance->break_totals)) {
                $breakTotal = $attendance->break_totals;
            }
        }
        $assignTaskHoursJson = json_encode($request['assign_task_hours']);
        // Convert the assign_task_hours array to JSON and calculate total hours
        if ($timesheet->hours === '0') {
            $totalTaskHours = array_sum($request['assign_task_hours']);
            // Validate that combined hours do not exceed 24
            if ($totalTaskHours > 24) {
                return $this->responseHelper->api_response(null, 422, "error", "Total task hours cannot exceed 24.");
            }

            $totalHours = $totalTaskHours;
           

            // Deduct break duration from total hours if set
        if ($timesheet->break === '1' && isset($timesheet->break_duration) && isset($timesheet->break_duration_type)) {
            // Convert break duration to hours based on break_duration_type
            switch ($timesheet->break_duration_type) {
                case 'minutes':
                    $breakTotal = $timesheet->break_duration / 60;
                    break;
                case 'hours':
                    $breakTotal = $timesheet->break_duration;
                    break;
                default:
                    $breakTotal = 0; // Set to 0 if the type is unknown
                    break;
            }

            // Check if breakTotal is more than totalHours
            if ($breakTotal > $totalHours) {
                return $this->responseHelper->api_response(null, 422, "error", "Break duration cannot be more than total working hours.");
            }

            
            $totalHours -= $breakTotal;
            $totalHours = max($totalHours, 0); // Ensure total hours is not negative
          }

        }

        

        if (!is_null($attendance)) {
            // Update attendance record
            $attendance->user_id = auth()->user()->id;
            $attendance->worker_id = $worker->id;
            $attendance->timesheet_id = $timesheet->timesheet_id;
            $attendance->assigned_task_hours = $assignTaskHoursJson;
            $attendance->date = $formattedDate;
            $attendance->total_hours = $totalHours;
            $attendance->save();
            $attendanceData = Attendance::where('worker_id', $attendance->worker_id)
                                        ->where('timesheet_id', $attendance->timesheet_id)
                                        ->whereDate('date', $formattedDate)
                                        ->first();
            return $this->responseHelper->api_response($attendanceData, 200, "success", 'Task hours assigned successfully.');
        } else {
            // Create attendance record
            $attendance = new Attendance();
            $attendance->user_id = auth()->user()->id;
            $attendance->worker_id = $worker->id;
            $attendance->timesheet_id = $timesheet->timesheet_id;
            $attendance->assigned_task_hours = $assignTaskHoursJson;
            $attendance->date = $formattedDate;
            $attendance->total_hours = $totalHours;
            $attendance->save();
            $attendanceData = Attendance::where('worker_id', $attendance->worker_id)
                                        ->where('timesheet_id', $attendance->timesheet_id)
                                        ->whereDate('date', $formattedDate)
                                        ->first();
            return $this->responseHelper->api_response($attendanceData, 200, "success", 'Task hours assigned successfully.');
        }
    }



    public function getInOutAttendanceData($timesheet_id, $worker_id, $startDate, $endDate) {
        // Parse start and end dates to ensure they're in the correct format
        $startDate = date('Y-m-d', strtotime($startDate));
        $endDate = date('Y-m-d', strtotime($endDate));
    
        // Query the database based on the type
        $attendances = Attendance::where('worker_id', $worker_id)
                                 ->where('timesheet_id', $timesheet_id)
                                 ->whereBetween('date', [$startDate, $endDate])
                                 ->orderBy('date') // Ensure attendances are ordered by date
                                 ->get();
    
        // Prepare an array to hold the final attendances data
        $attendancesData = [];
    
        // Loop through each date in the range
        for ($date = $startDate; $date <= $endDate; $date = date('Y-m-d', strtotime($date . ' +1 day'))) {
            // Check if there is an attendance entry for the current date
            $attendance = $attendances->firstWhere('date', $date);
    
            // If there is no attendance entry for the current date, add an entry with null date
            if (!$attendance) {
                $attendancesData[] = [
                    'id' => null,
                    'user_id' => null,
                    'worker_id' => null,
                    'timesheet_id' => null,
                    'attendance' => [],
                    'date' => null,
                    'approve' => null,
                    'total_hours' => null,
                    'created_at' => null,
                    'updated_at' => null,
                    'first_in_time' => null,
                    'last_out_time' => null
                ];
            } else {
                // Process the attendance data to get first inTime and last OutTime
                $attendanceData = json_decode($attendance->attendance, true);
                $firstInTime = null;
                $lastOutTime = null;
                if (is_array($attendanceData) && count($attendanceData) > 0) {
                    $firstInTime = $attendanceData[0]['in_time'];
                    $lastOutTime = end($attendanceData)['out_time'];
                }
    
                // Add the attendance data to the array
                $attendancesData[] = [
                    'id' => $attendance->id,
                    'user_id' => $attendance->user_id,
                    'worker_id' => $attendance->worker_id,
                    'timesheet_id' => $attendance->timesheet_id,
                    'attendance' => $attendanceData,
                    'date' => $attendance->date,
                    'approve' => $attendance->approve,
                    'total_hours' => $attendance->total_hours,
                    'created_at' => $attendance->created_at,
                    'updated_at' => $attendance->updated_at,
                    'first_in_time' => $firstInTime,
                    'last_out_time' => $lastOutTime
                ];
            }
        }
    
        // Prepare response data
        $data = [
            'attendances' => $attendancesData,
        ];
    
        // Return the response
        return $this->responseHelper->api_response($data, 200, "success", 'Success.');
    }
    

    public function getSummaryData($timesheetid)
    {
        // Retrieve all workers along with their attendance records
        $workers = LocalWorker::with(['attendance' => function ($query) use ($timesheetid) {
            $query->select('worker_id')
                ->selectRaw('SUM(total_hours) as total_hours')
                ->selectRaw('SUM(CASE WHEN approve = "1" THEN total_hours ELSE 0 END) as total_hours_approve')
                ->selectRaw('SUM(CASE WHEN approve = "0" THEN total_hours ELSE 0 END) as total_hours_disapprove')
                ->groupBy('worker_id');
        }])
        ->where('timesheet_id', $timesheetid)
        ->get();
        return $this->responseHelper->api_response($workers, 200, "success", 'success.');
    }
    
    public function getTotalWorkerData($timesheetid)
    {
        // Retrieve count of all workers 
        $workers = LocalWorker::where('timesheet_id',$timesheetid)->count();
        return $this->responseHelper->api_response(['total_workers'=>$workers], 200, "success", 'success.');
    }
    public function addLocalWorkerCsv($request)
    {
        // Get the uploaded CSV file
        $file = $request['file'];
        // Read the CSV file
        $csv = Reader::createFromPath($file->getPathname(), 'r');
        $csv->setHeaderOffset(0); // Skip the header row
        // Iterate over each row in the CSV file
        foreach ($csv as $record) {
                // Create a new LocalWorker instance and save it to the database
                $existingCount = LocalWorker::count();
                $createLocalWorker = new LocalWorker();
                $existingCount++;
                $formattedId = 'L-' . $existingCount;
                $createLocalWorker->worker_id = $formattedId;
                $createLocalWorker->timesheet_id = $request['timesheet_id'];
                $createLocalWorker->first_name = $record['firstname'];
                $createLocalWorker->last_name = $record['lastname'];
                $createLocalWorker->save();  
        }
        return $this->responseHelper->api_response(null, 200, "success", 'Local Workers added successfully.');
    }

public function getDailyWeeklyWorkerTotalHrs($workerId, $timesheetId, $month, $year){
    // Retrieve the attendance records for the specified worker, timesheet, month, and year
    $monthChecked = is_numeric($month) ? intval($month) : Carbon::parse($month)->format('m');
    $attendances = Attendance::where('worker_id', $workerId)
                    ->where('timesheet_id', $timesheetId)
                    ->whereYear('date', $year)
                    ->whereMonth('date', $monthChecked)
                    ->get();
    
    // Initialize variables to store daily and weekly working hours
    $dailyWorkingHours = [];
    $approveStatus = [];
    
    // Get the first and last day of the month
    $firstDayOfMonth = Carbon::createFromDate($year, $monthChecked, 1);
    $lastDayOfMonth = Carbon::createFromDate($year, $monthChecked, 1)->endOfMonth();
    
    // Iterate through each day in the month
    for ($date = $firstDayOfMonth; $date <= $lastDayOfMonth; $date->addDay()) {
        $dateString = $date->toDateString();
        
        // Set default working hours to null
        $dailyWorkingHours[$dateString] = null;
        $approveStatus[$dateString] = null;
        
        // Check if there is attendance data for the current date
        foreach ($attendances as $attendance) {
            if (Carbon::parse($attendance->date)->toDateString() === $dateString) {
                // Decode the JSON data from the attendance column
                $attendanceData = json_decode($attendance->attendance, true);
                
                // Initialize total working hours for the current date
                $totalWorkingHours = 0;
                
                // Iterate through each in/out record and calculate total working hours
                foreach ($attendanceData as $record) {
                    // Assuming the JSON structure contains 'in_time' and 'out_time'
                    $inTime = Carbon::parse($record['in_time']);
                    $outTime = Carbon::parse($record['out_time']);
                    
                    // Calculate working hours for the current record
                    $workingHours = $outTime->diffInHours($inTime);
                    
                    // Add working hours to the total
                    $totalWorkingHours += $workingHours;
                }
                
                // Set total working hours for the current date
                $dailyWorkingHours[$dateString] = $totalWorkingHours;
                $approveStatus[$dateString] = $attendance->approve;
            }
        }
    }
    
    // Initialize weekly working hours array
    $weeklyWorkingHours = [];

    // Calculate weekly working hours
    foreach ($dailyWorkingHours as $date => $hours) {
        $weekStartDate = Carbon::parse($date)->startOfWeek()->toDateString();
        
        if (!isset($weeklyWorkingHours[$weekStartDate])) {
            $weeklyWorkingHours[$weekStartDate] = 0;
        }
        
        if (!is_null($hours)) {
            $weeklyWorkingHours[$weekStartDate] += $hours;
        }
    }
    
    // Return the response data
    $data = [
        'worker_id' => $workerId,
        'timesheet_id' => $timesheetId,
        'month' => $monthChecked,
        'year' => $year,
        'daily_working_hours' => $dailyWorkingHours,
        'weekly_working_hours' => $weeklyWorkingHours,
        'approve_status' => $approveStatus,
    ];
    
    return $this->responseHelper->api_response($data, 200, "success", 'success.');
}

/*
public function assignTaskAdd($request){
    //dd($request);
    $timesheet = TimeSheet::where('timesheet_id', $request['timesheet_id'])->first();
    $formattedDate = Carbon::createFromFormat('d-m-Y', $request['date'])->format('Y-m-d');
    $attendances = Attendance::where('worker_id', $request['worker_id'])
                    ->where('timesheet_id', $request['timesheet_id'])
                    ->whereDate('date',$formattedDate)
                    ->first();
    if(!empty($attendances)){
        if($timesheet->hours === '0'){
            $attendances->total_hours = $request['total_hours'];
            $attendances->save();
            return $this->responseHelper->api_response($attendances, 200, "success", 'success.');
        }else{
            return $this->responseHelper->api_response(null, 422, "error", "Calculate Hours is set True, Hence can not be updated.");
        }
    }else{
        if($timesheet->hours === '0'){
            $attendance = new Attendance();
            $attendance->user_id = auth()->user()->id;
            $attendance->worker_id = $request['worker_id'];
            $attendance->timesheet_id = $request['timesheet_id'];
            $attendance->date = $formattedDate;
            $attendance->total_hours = $request['total_hours'];
            $attendance->save();
            return $this->responseHelper->api_response($attendance, 200, "success", 'success.');
        }        
        return $this->responseHelper->api_response(null, 422, "error", "Calculate Hours is set True, Hence can not be added.");
    }
}

*/

    public function assignTaskAdd($request)
    {
        // Retrieve the timesheet and format the date
        $timesheet = TimeSheet::where('timesheet_id', $request['timesheet_id'])->first();
        $formattedDate = Carbon::createFromFormat('d-m-Y', $request['date'])->format('Y-m-d');
        
        // Find existing attendance for the worker on the given timesheet and date
        $attendance = Attendance::where('worker_id', $request['worker_id'])
                        ->where('timesheet_id', $request['timesheet_id'])
                        ->whereDate('date', $formattedDate)
                        ->first();
        
        if (!empty($attendance)) {
            // If attendance exists
            if ($timesheet->hours === '0') {
                // If calculate hours is not enabled, update total hours
                $attendance->total_hours = $request['total_hours'];
                $attendance->save();
                // Check if total hours exceed planned hours
                $totalRecordedHours = $attendance->worker->attendance()->sum('total_hours');
                $plannedHours = $attendance->worker->planned_hours;
                $newTotalHours = $totalRecordedHours + $request['total_hours'];
                if ($timesheet->planned_hours === '1' && !is_null($plannedHours) && $newTotalHours > $plannedHours) {
                    return $this->responseHelper->api_response(null, 422, "error", "Your time has exceeded allocated hours.");
                }
                return $this->responseHelper->api_response($attendance, 200, "success", 'success.');
            } else {
                // If calculate hours is enabled, cannot update
                return $this->responseHelper->api_response(null, 422, "error", "Calculate Hours is set True, Hence can not be updated.");
            }
        } else {
            // If attendance doesn't exist
            if ($timesheet->hours === '0') {
                // If calculate hours is not enabled, create new attendance
                $attendance = new Attendance();
                $attendance->user_id = auth()->user()->id;
                $attendance->worker_id = $request['worker_id'];
                $attendance->timesheet_id = $request['timesheet_id'];
                $attendance->date = $formattedDate;
                $attendance->total_hours = $request['total_hours'];
                $attendance->save();
                // Check if total hours exceed planned hours
                $totalRecordedHours = $attendance->worker->attendance()->sum('total_hours');
                $plannedHours = $attendance->worker->planned_hours;
                $newTotalHours = $totalRecordedHours + $request['total_hours'];
                if ($timesheet->planned_hours === '1' && !is_null($plannedHours) && $newTotalHours > $plannedHours) {
                    $attendance->delete();
                    return $this->responseHelper->api_response(null, 422, "error", "Your time has exceeded allocated hours.");
                }
                return $this->responseHelper->api_response($attendance, 200, "success", 'success.');
            }
            // If calculate hours is enabled, cannot add new attendance
            return $this->responseHelper->api_response(null, 422, "error", "Calculate Hours is set True, Hence can not be added.");
        }
    }


public function updateAssignTaskCheckbox($request){
    $timeSheet = TimeSheet::where('timesheet_id', $request['timesheet_id'])->first();
    if(!empty($timeSheet)){
        if($timeSheet->assign_task === '1'){
            $timeSheet->assign_task = '0';
        }else{
            $timeSheet->assign_task = '1';
        }
        $timeSheet->save();
        return $this->responseHelper->api_response($timeSheet, 200, "success", 'success.');
    }else{
        return $this->responseHelper->api_response(null, 422, "error", "something went wrong.");
    }
}

public function checkLocalWorkerExist($timesheet_id)
{
    $localWorkerExistOnThisTimesheet = LocalWorker::where('timesheet_id', $timesheet_id)->exists();
    
    if ($localWorkerExistOnThisTimesheet) {
        return $this->responseHelper->api_response(true, 200, "success", 'success.');
    } else {
        return $this->responseHelper->api_response(false, 422, "error", "error.");
    }
}

public function makeUniversalWorker($request){
    $ServenDigitCode = $this->responseHelper->generateAlphanumericString();
    $countryCode = $this->responseHelper->getCountryCodeByName($request['country']);
    $fullCode = $countryCode . '-' . $ServenDigitCode;
    $UniversalWorker = new UniversalWorker();
    $UniversalWorker->worker_id = $fullCode;
    $UniversalWorker->firstname = $request['firstname'];
    $UniversalWorker->lastname = $request['lastname'];
    $UniversalWorker->email = $request['email'];
    $UniversalWorker->country = $request['country'];
    $UniversalWorker->status = $request['status'];
    $UniversalWorker->registration_date = $request['registration_date'];
    $UniversalWorker->worker_qr = $this->responseHelper->GenerateWorkerQR($request,$fullCode);
    $UniversalWorker->save();
    if ($UniversalWorker) {
        return $this->responseHelper->api_response($UniversalWorker, 200, "success", 'success.');
    } else {
        return $this->responseHelper->api_response(false, 422, "error", "error.");
    }


}



    


    
    

    
}
