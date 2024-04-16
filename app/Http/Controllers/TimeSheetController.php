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
    public function deleteTimeSheet($id){
        try {
            $this->timeSheetServices->deleteTimesheet($id);
        } catch (\Exception $e) {
            return $this->responseHelper->api_response(null, 422,"error", $e->getMessage());
        }
    }

    

    
    
}
