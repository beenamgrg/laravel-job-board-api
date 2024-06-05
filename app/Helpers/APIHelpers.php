<?php

namespace App\Helpers;

use App\Models\ListingLog;


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

    public static function jobListingLog($user_id, $job_id, $action)
    {
        $log = new ListingLog();
        $log->user_id = $user_id;
        $log->job_id = $job_id;
        $log->action = $action;
        $log->save();
    }
}
