<?php

namespace App\Http\Controllers;

use App\Models\JobApplication;
use App\Models\JobListing;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Helpers\APIHelpers;
use Illuminate\Support\Facades\Validator;
use Exception;


class JobApplicationController extends Controller
{
    public function submitApplication(Request $request)
    {
        DB::beginTransaction();
        try
        {
            $validator = Validator::make($request->all(), [
                'resume' => 'required|mimetypes:application/pdf|max:10000',
                'cover_letter' => 'required',
                'job_id' => 'required'
            ]);
            if ($validator->fails())
            {
                return response(['errors' => $validator->errors()->all()], 422);
            }

            //Check if the job exsists
            $jobCheck = JobListing::where('id', $request->job_id)->first() ?? NULL;
            if ($jobCheck == NULL)
            {
                $response = APIHelpers::createAPIResponse(true, 400, 'The job doesnot exsist!!', Auth::user()->name);
                return response()->json($response, 400);
            }

            //Check if the user has applied the job before
            $check = JobApplication::where('job_id', $request->job_id)->where('user_id', Auth::user()->id)->first() ?? NULL;
            if ($check != NULL)
            {
                $response = APIHelpers::createAPIResponse(true, 400, 'You have already applied for this job before!', Auth::user()->name);
                return response()->json($response, 400);
            }

            // Create a unique name for the file and move the file to public path
            $path = '/job-applications/resume/';
            if (!file_exists(public_path() . $path))
            {
                mkdir(public_path() . $path, 0777, true);
            }
            $fileName =  $path . time() . '.' . Str::random(4) . $request->resume->extension();
            $file = $request->file('resume');
            $file->move(public_path() . $path, $fileName);


            $job_application = new JobApplication();
            $job_application->resume = $fileName;
            $job_application->cover_letter = $request->cover_letter;
            $job_application->job_id = $request->job_id;
            $job_application->user_id = Auth::user()->id;
            $job_application->save();

            $response = APIHelpers::createAPIResponse(false, 200, 'Job application Submitted Successfully!!', Auth::user()->name);
            DB::commit();
            return response()->json($response, 200);
        }

        catch (Exception $e)
        {
            DB::rollBack();
            if ($request->wantsJson())
            {
                $response = APIHelpers::createAPIResponse(true, 400, $e->getMessage(), null);
                return response()->json([$response], 400);
            }
        }
    }
}
