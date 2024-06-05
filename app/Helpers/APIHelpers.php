<?php

namespace App\Helpers;

use App\Models\ListingLog;
use App\Models\JobListing;
use Illuminate\Support\Facades\Auth;


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

    public static function employerAuthentication($job_id)
    {
        $check = JobListing::where('id', $job_id)->where('employer_id', Auth::user()->id)->first() ?? NULL;
        return $check;
    }
}
