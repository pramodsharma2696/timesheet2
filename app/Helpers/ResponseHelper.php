<?php

namespace App\Helpers;

use App\Models\ApiLogs;
use App\Classes\SimpleQR;
use League\ISO3166\ISO3166;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ResponseHelper {
    function __construct()
    {
        $this->iso3166 = new ISO3166();
    }

    public function api_response($data = [], $code = '', $type = '', $message = '', $headers = [], $getString = false)
    {
            $data_array['type'] = $type;
            $data_array['data']    = $data;
            $data_array['message'] = $message;

            $data_array['code']    = $code;

            if ($getString === true) {
                    return json_encode($data_array);
            }
            $this->addCorsHeaders();
            response()->json($data_array, $code, $headers)->send();
            die();
    }
    public function api_validate_request($input = [], $rule = [], $msg = [])
    {
            $validator = Validator::make($input, $rule, $msg);

            if ($validator->fails()) {
                    return $this->api_response($validator->errors(), 422, 'error');
            }

            return true;
    }
    public function logAction($action, $requestData, $responseData, $statusCode)
    {
      
        $user = Auth::user();
        ApiLogs::create([
            'user_id' => $user ? $user->id : null,
            'username' => $user ? $user->name : null,
            'action' => $action,
            'request_data' => json_encode($requestData),
            'response_data' => json_encode($responseData),
            'status_code' => $statusCode,
        ]);
    }

    public function GenerateQR($projectData){
        $qrCodeContent = 'Project ID: ' . $projectData->id . ', Project Desr: ' . $projectData->desr;
        $qrfile = 'project_'.$projectData->id.'_qrcode.png';
        $errorCorrectionLevel = 'L';
        $matrixPointSize = 10;
        $qrfilename =  Storage::disk('public_qrcodes')->path($qrfile);
        \QRcode::png($qrCodeContent, $qrfilename, $errorCorrectionLevel, $matrixPointSize, 2);
        return Storage::disk('public_qrcodes')->url($qrfile);
    }
//     public function GenerateRefreshQR($projectData){
//         $qrCodeContent = 'Project ID: ' . $projectData->id . ', Project Desr: ' . $projectData->desr;
//         $qrfile = 'project_'.$projectData->id.'_qrcode.png';
//         $errorCorrectionLevel = 'L';
//         $matrixPointSize = 10;
//         $qrfilename =  Storage::disk('public_refresh_qrcodes')->path($qrfile);
//         \QRcode::png($qrCodeContent, $qrfilename, $errorCorrectionLevel, $matrixPointSize, 2);
//         return $qrfile;
//     }
    public function GenerateRefreshQR($projectData){
        $qrCodeContent = 'Project ID: ' . $projectData->id . ', Project Desr: ' . $projectData->desr;
        $qrfile = 'project_'.$projectData->id.'_qrcode.png';
        $errorCorrectionLevel = 'L';
        $matrixPointSize = 10;
        $qrfilename = Storage::disk('public_refresh_qrcodes')->path($qrfile);
        \QRcode::png($qrCodeContent, $qrfilename, $errorCorrectionLevel, $matrixPointSize, 2);
        return Storage::disk('public_refresh_qrcodes')->url($qrfile);
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

    public function addCorsHeaders(){
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';

        if (!$origin) {
            $referer = $_SERVER['HTTP_REFERER'] ?? '';
        
            if ($referer) {
                $parsedUrl = parse_url($referer);
                $origin = $parsedUrl['scheme'] . '://' . $parsedUrl['host'];
                if (isset($parsedUrl['port'])) {
                    $origin .= ':' . $parsedUrl['port'];
                }
                $origin = rtrim($origin, '/');
            } else {
                $origin = '*';
            }
        }
        
        header('Access-Control-Allow-Origin: ' . $origin);
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Allow-Headers: X-Requested-With,Content-Type,X-Token-Auth,Authorization');
        header('Accept: application/json');
    }


    function generateAlphanumericString($length = 7) {
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    function getCountryCodeByName($countryName)
    {
        $countries = $this->iso3166->all(); // This will get the array of all countries

        foreach ($countries as $country) {
            if (strcasecmp($country['name'], $countryName) == 0) {
                return $country['alpha2'];
            }
        }

        return 'NaN'; // Return NaN if the country is not found
    }


    public function GenerateWorkerQR($request,$workerCode){
        $qrCodeContent = 'Worker ID: ' . $workerCode. ', Name : ' .$request['firstname'].' '.$request['lastname'];
        $qrfile = 'worker_'.$workerCode.'_qrcode.png';
        $errorCorrectionLevel = 'L';
        $matrixPointSize = 10;
        $qrfilename = Storage::disk('public_worker_qrcodes')->path($qrfile);
        \QRcode::png($qrCodeContent, $qrfilename, $errorCorrectionLevel, $matrixPointSize, 2);
        return Storage::disk('public_worker_qrcodes')->url($qrfile);
    }

}


?>