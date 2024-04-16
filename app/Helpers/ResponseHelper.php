<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Validator;

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

}


?>