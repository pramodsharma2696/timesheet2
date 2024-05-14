<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\ResponseHelper;
use App\Services\TimeSheetServices;

class TimeSheetController extends Controller
{
    function __construct(ResponseHelper $responseHelper, TimeSheetServices $TimeSheetServices)
    {
        $this->timeSheetServices = $TimeSheetServices;
        $this->responseHelper = $responseHelper;
    }
    public function createTimeSheet(Request $request){
       
        $validation = $this->responseHelper->api_validate_request($request->all(), config('rules.validate_createTimesheet'));
        if ($validation !== false) {
            try {
                $this->timeSheetServices->storeTimesheet($request->all());
            } catch (\Exception $e) {
                return $this->responseHelper->api_response(null, 422,"error", $e->getMessage());
            }
        }else{
            return $this->responseHelper->api_response(null,422,"error", $validation);
        }
    }

    public function updateTimeSheet(Request $request){
        $validation = $this->responseHelper->api_validate_request($request->all(), config('rules.validate_updateTimesheet'));
        if ($validation !== false) {
            try {
                $this->timeSheetServices->updateTimesheet($request->all());
            } catch (\Exception $e) {
                return $this->responseHelper->api_response(null, 422,"error", $e->getMessage());
            }
        }else{
            return $this->responseHelper->api_response(null,422,"error", $validation);
        }
    }

    public function showTimeSheet($id){
        try {
            $this->timeSheetServices->showTimesheet($id);
        } catch (\Exception $e) {
            return $this->responseHelper->api_response(null, 422,"error", $e->getMessage());
        }
    }
    public function showAllTimeSheet(){
        try {
            $this->timeSheetServices->showallTimesheet();
        } catch (\Exception $e) {
            return $this->responseHelper->api_response(null, 422,"error", $e->getMessage());
        }
    }
    public function deleteTimeSheet($id){
        try {
            $this->timeSheetServices->deleteTimesheet($id);
        } catch (\Exception $e) {
            return $this->responseHelper->api_response(null, 422,"error", $e->getMessage());
        }
    }

    public function generateTimeSheetId(){
        try {
            $this->timeSheetServices->generateTimeSheetId();
        } catch (\Exception $e) {
            return $this->responseHelper->api_response(null, 422,"error", $e->getMessage());
        }
    }
    public function generateQR($id){
        try {
            $this->timeSheetServices->generateQR($id);
        } catch (\Exception $e) {
            return $this->responseHelper->api_response(null, 422,"error", $e->getMessage());
        }
    }
    public function RefreshQR($id){
        try {
            $this->timeSheetServices->refreshQR($id);
        } catch (\Exception $e) {
            return $this->responseHelper->api_response(null, 422,"error", $e->getMessage());
        }
    }
   

    public function addLocalWorker(Request $request){

        foreach ($request->all() as $data) {
            $validation = $this->responseHelper->api_validate_request($data, config('rules.addLocalWorker'));
            if ($validation !== false) {
                $this->timeSheetServices->addLocalWorker($request->all());
            }else{
                return $this->responseHelper->api_response(null,422,"error", $validation);
            }
        }

    }
    public function InviteWorker(Request $request){
        foreach ($request->all() as $data) {
            $validation = $this->responseHelper->api_validate_request($data, config('rules.InviteWorker'));
            if ($validation !== false) {
                $this->timeSheetServices->InviteWorker($request->all());
            }else{
                return $this->responseHelper->api_response(null,422,"error", $validation);
            }
        }

    }

    public function UpdateWorker(Request $request){
        try {
            $this->timeSheetServices->updateWorker($request->all());
        } catch (\Exception $e) {
            return $this->responseHelper->api_response(null, 422,"error", $e->getMessage());
        }
    }

    public function showWorkers(){
        try {
            $this->timeSheetServices->showWorkers();
        } catch (\Exception $e) {
            return $this->responseHelper->api_response(null, 422,"error", $e->getMessage());
        }
    }
    public function getTimesheetIdBasedWorker($id){
        try {
            $this->timeSheetServices->getTimesheetIdBasedWorker($id);
        } catch (\Exception $e) {
            return $this->responseHelper->api_response(null, 422,"error", $e->getMessage());
        }
    }
   

    public function getTimesheetIdAndDateBasedWorker($timesheetid, $date){
        try {
            $this->timeSheetServices->getTimesheetIdAndDateBasedWorker($timesheetid, $date);
        } catch (\Exception $e) {
            return $this->responseHelper->api_response(null, 422,"error", $e->getMessage());
        }
    }
    public function getSummaryData($timesheetid){
        try {
            $this->timeSheetServices->getSummaryData($timesheetid);
        } catch (\Exception $e) {
            return $this->responseHelper->api_response(null, 422,"error", $e->getMessage());
        }
    }
    public function getTotalWorkerData($timesheetid){
        try {
            $this->timeSheetServices->getTotalWorkerData($timesheetid);
        } catch (\Exception $e) {
            return $this->responseHelper->api_response(null, 422,"error", $e->getMessage());
        }
    }



    public function Attendance(Request $request){
        $validation = $this->responseHelper->api_validate_request($request->all(), config('rules.Attendance'));
        if ($validation !== false) {
            try {
                $this->timeSheetServices->recordAttendance($request->all());
            } catch (\Exception $e) {
                return $this->responseHelper->api_response(null, 422,"error", $e->getMessage());
            }
        }else{
            return $this->responseHelper->api_response(null,422,"error", $validation);
        }
    }

    public function approveAttendance(Request $request){
        $validation = $this->responseHelper->api_validate_request($request->all(), config('rules.approveAttendance'));
        if ($validation !== false) {
            try {
                $this->timeSheetServices->approveAttendance($request->all());
            } catch (\Exception $e) {
                return $this->responseHelper->api_response(null, 422,"error", $e->getMessage());
            }
        }else{
            return $this->responseHelper->api_response(null,422,"error", $validation);
        }
    }
    public function approveAllAttendance(Request $request){
        $validation = $this->responseHelper->api_validate_request($request->all(), config('rules.approveAllAttendance'));
        if ($validation !== false) {
            try {
                $this->timeSheetServices->approveAllAttendance($request->all());
            } catch (\Exception $e) {
                return $this->responseHelper->api_response(null, 422,"error", $e->getMessage());
            }
        }else{
            return $this->responseHelper->api_response(null,422,"error", $validation);
        }
    }
    public function assignTaskHours(Request $request){
        $validation = $this->responseHelper->api_validate_request($request->all(), config('rules.assignTaskHours'));
        if ($validation !== false) {
            try {
                $this->timeSheetServices->assignTaskHours($request->all());
            } catch (\Exception $e) {
                return $this->responseHelper->api_response(null, 422,"error", $e->getMessage());
            }
        }else{
            return $this->responseHelper->api_response(null,422,"error", $validation);
        }
    }

    public function getInOutAttendanceData($timesheet_id,$worker_id,$startDate,$endDate){
        try {
            $this->timeSheetServices->getInOutAttendanceData($timesheet_id,$worker_id,$startDate,$endDate);
        } catch (\Exception $e) {
            return $this->responseHelper->api_response(null, 422,"error", $e->getMessage());
        }
    }

    public function addLocalWorkerCsvFile(Request $request){
    $validation = $this->responseHelper->api_validate_request($request->all(), config('rules.addLocalWorkercsv'));
    if ($validation !== false) {
        $this->timeSheetServices->addLocalWorkerCsv($request->all());
    }else{
        return $this->responseHelper->api_response(null,422,"error", $validation);
    }
    }

    public function getDailyWeeklyWorkerTotalHrs($workerid, $timesheetid, $month, $year){
        try {
            $this->timeSheetServices->getDailyWeeklyWorkerTotalHrs($workerid, $timesheetid, $month, $year);
        }catch(\Exception $e){
            return $this->responseHelper->api_response(null, 422,"error", $e->getMessage());
        }
    }

    public function assignTaskAdd(Request $request){
        $validation = $this->responseHelper->api_validate_request($request->all(), config('rules.assignTaskAdd'));
        if ($validation !== false) {
            $this->timeSheetServices->assignTaskAdd($request->all());
        }else{
            return $this->responseHelper->api_response(null,422,"error", $validation);
        }
    }
    public function updateAssignTaskCheckbox(Request $request){
        $validation = $this->responseHelper->api_validate_request($request->all(), config('rules.updateAssignTaskCheckbox'));
        if ($validation !== false) {
            $this->timeSheetServices->updateAssignTaskCheckbox($request->all());
        }else{
            return $this->responseHelper->api_response(null,422,"error", $validation);
        }
    }

    
    
}
