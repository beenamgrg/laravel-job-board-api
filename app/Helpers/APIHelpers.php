<?php

namespace App\Helpers;

class APIHelpers
{

    public static function createAPIResponse($is_error, $code, $message, $data)
    {
        $result = [];
        $result['code'] = $code;
        $result['success'] = !$is_error;
        $result['message'] = $message;


        if ($is_error)
        {
            $result['code'] = 500;
        }
        else
        {
            $result['data'] = $data;
        }

        return $result;
    }
}
