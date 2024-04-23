<?php
namespace App\Services;

use App\Helpers\ResponseHelper;
use App\Models\TimeSheet;

class TimeSheetServices {

    function __construct(ResponseHelper $responseHelper)
    {
        $this->responseHelper = $responseHelper;
    }

    public function storeTimesheet($request){
        $userid = auth()->user()->id;
        $start_date = date('Y-m-d', strtotime($request['start_date']));
        $end_date = date('Y-m-d', strtotime($request['end_date']));
        if($end_date < $start_date) {
            return $this->responseHelper->api_response(null, 422,"error", "End date must be after the start date");
        }else{
        $createTimesheet = new TimeSheet();
        $createTimesheet->user_id = $userid;
        $createTimesheet->project_id = $request['projectid'];
        $createTimesheet->start_date = $start_date;
        $createTimesheet->end_date = $end_date;
        $createTimesheet->status = $request['status'];
        $createTimesheet->localwork = $request['localwork'];
        $createTimesheet->scanning = $request['scanning'];
        $createTimesheet->hours = $request['hours'];
        $createTimesheet->break = $request['break'];
        $createTimesheet->break_duration = $request['break_duration'];
        $createTimesheet->break_duration_type = $request['break_duration_type'];
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
        $createTimesheet->save();
        $timesheetData = TimeSheet::with('project')->where('id', $createTimesheet->id)->first();
        return $this->responseHelper->api_response($timesheetData, 200,"success", 'Timesheet created.');
        }
    }


    public function updateTimesheet($request){
        $userid = auth()->user()->id;
        $timesheetData = TimeSheet::where('user_id',$userid)->where('id', $request['timesheetid'])->first();
        if(empty($timesheetData)){
            return $this->responseHelper->api_response(null, 422,"error", "Timesheet does not exist to update.");
        }else{
            $start_date = date('Y-m-d', strtotime($request['start_date']));
            $end_date = date('Y-m-d', strtotime($request['end_date']));
            if($end_date < $start_date) {
                return $this->responseHelper->api_response(null, 422,"error", "End date must be after the start date");
            }else{
                $timesheetData->start_date = $start_date;
                $timesheetData->end_date = $end_date;
                $timesheetData->project_id = $request['projectid'];
                $timesheetData->status = $request['status'];
                $timesheetData->localwork = $request['localwork'];
                $timesheetData->scanning = $request['scanning'];
                $timesheetData->hours = $request['hours'];
                $timesheetData->break = $request['break'];
                $timesheetData->break_duration = $request['break_duration'];
                $timesheetData->break_duration_type = $request['break_duration_type'];
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
                return $this->responseHelper->api_response($timesheetData, 200,"success", 'Timesheet updated.');
            }
        }
       
    }

    

    public function showTimesheet($id){
        $timesheetData = TimeSheet::with('project')->where('id', $id)->first();
        if(!empty($timesheetData)){
            return $this->responseHelper->api_response($timesheetData, 200,"success", 'Timesheet details.');
        }else{
            return $this->responseHelper->api_response(null, 422,"error", "This timesheet does not exist.");
        }
    }
    public function showallTimesheet(){
        $timesheetData = TimeSheet::with('project')->orderBy('created_at', 'desc')->get();
        if(!empty($timesheetData)){
            return $this->responseHelper->api_response($timesheetData, 200,"success", 'Timesheet details.');
        }else{
            return $this->responseHelper->api_response(null, 422,"error", "This timesheet does not exist.");
        }
    }
    public function deleteTimesheet($id){
        $timesheetData = TimeSheet::where('id', $id)->first();
        if(!empty($timesheetData)){
            $timesheetData->delete();
            return $this->responseHelper->api_response(null, 200,"success", 'Timesheet deleted.');
        }else{
            return $this->responseHelper->api_response(null, 422,"error", "This timesheet does not exist.");
        }
    }

}




?>