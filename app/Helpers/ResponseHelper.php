<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use App\Classes\SimpleQR;

class ResponseHelper {

    public function api_response($data = [], $code = '', $type = '', $message = '', $headers = [], $getString = false)
    {
            $data_array['type'] = $type;
            $data_array['data']    = $data;
            $data_array['message'] = $message;

            $data_array['code']    = $code;

            if ($getString === true) {
                    return json_encode($data_array);
            }
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
    


}


?>