<?php

namespace App\Services;

use App\Models\TimeSheet;
use App\Models\ProjectList;
use App\Helpers\ResponseHelper;
use App\Models\LocalWorker;
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
    // public function generateQR($projectId){
    //     $projectData = ProjectList::where('id', $projectId)->first();

    //     if (!empty($projectData)) {
    //         $qrDirectoryPath = public_path('storage/QRCODE/');
    //         // Check if the directory exists, if not, create it
    //         if (!file_exists($qrDirectoryPath)) {
    //             mkdir($qrDirectoryPath, 0777, true); // Creates the directory
    //         }
    //         // Check if the QR code file exists
    //         $checkQRExist = $qrDirectoryPath . 'project_' . $projectData->id . '_qrcode.png';
    //         if (!file_exists($checkQRExist)) {
    //             $qrfilename = $this->responseHelper->GenerateQR($projectData);
    //             $original_dir_path = $qrfilename;
    //             TimeSheet::where('project_id', $projectData->id)->update(['timesheet_qr' => $original_dir_path]);
    //         } else {
    //             $qrfiledata1 = TimeSheet::where('project_id', $projectData->id)->first();

    //             if (isset($qrfiledata1->timesheet_qr) && !is_null($qrfiledata1->timesheet_qr)) {
    //                 $original_dir_path = $qrfiledata1->timesheet_qr;
    //             } else {
    //                 $qrfilename = $this->responseHelper->GenerateQR($projectData);
    //                 $original_dir_path = $qrfilename;
    //                 TimeSheet::where('project_id', $projectData->id)->update(['timesheet_qr' => $original_dir_path]);
    //             }
    //         }

    //         return $this->responseHelper->api_response(['timesheet_qr' => $original_dir_path], 200, "success", 'Timesheet QR is generated.');
    //     } else {
    //         return $this->responseHelper->api_response(null, 422, "error", "This project does not exist.");
    //     }
    // }

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
}
