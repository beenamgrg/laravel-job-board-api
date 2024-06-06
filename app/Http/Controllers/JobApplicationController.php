<?php

namespace App\Http\Controllers;

use App\Models\JobApplication;
use App\Jobs\SendReviewEmail;
use App\Models\JobListing;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Helpers\APIHelpers;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use App\Mail\NotificationMail;
use App\Mail\ReviewMail;
use Exception;

use Illuminate\Bus\Batch;
use Illuminate\Support\Facades\Bus;

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
            $jobCheck = JobListing::where('id', $request->job_id)->where('status', 1)->get() ?? NULL;
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

            $response = APIHelpers::createAPIResponse(false, 200, 'Job application Submitted Successfully!!', $job_application);
            DB::commit();
            Mail::to('beenamgrg089@gmail.com')->send(new NotificationMail($job_application->cover_letter));
            // Mail::to('beenamgrg089@gmail.com')->send(new ReviewMail('Testy', 'emails.notification', $job_application));
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

    public function getApplication(Request $request)
    {
        try
        {
            // dd('fg');
            $paginate = intval($request->get("length", env('PAGINATION', 5)));
            $job_applications = JobApplication::select('job_applications.*', 'job_listings.title as job_title', 'companies.name as company', 'companies.email as company_email', 'users.name as applicant', 'users.email as applicant_email')
                ->leftjoin('job_listings', 'job_listings.id', 'job_applications.job_id')
                ->leftjoin('users', 'users.id', 'job_applications.user_id')
                ->leftjoin('companies', 'companies.id', 'job_listings.company_id')
                ->where('job_applications.status', 1)
                ->where('companies.employer_id', Auth::user()->id)
                ->groupBy('job_listings.id', 'companies.id', 'job_applications.id', 'users.id')
                ->orderBy('job_applications.id', 'DESC')
                ->paginate($paginate);

            $response = $job_applications->count() > 0 ? APIHelpers::createAPIResponse(false, 200, 'List of the active job submissions!!', $job_applications) : APIHelpers::createAPIResponse(false, 200, 'No Active Job-Submissions at Moment!', NULL);
            return response()->json($response, 200);
        }
        catch (Exception $e)
        {
            if ($request->wantsJson())
            {
                $response = APIHelpers::createAPIResponse(true, 400, $e->getMessage(), null);
                return response()->json([$response], 400);
            }
        }
    }

    public function approve(Request $request)
    {
        DB::beginTransaction();
        try
        {
            $jobApplication = JobApplication::findOrFail($request->jobApplicationId);
            $check = APIHelpers::employerAuthentication($jobApplication->job_id);
            if ($check == NULL)
            {
                $response = APIHelpers::createAPIResponse(true, 401, 'Unauthorized Access', NULL);
                return response()->json($response, 402);
            }
            $data = JobApplication::select('job_applications.*', 'users.name as applicant_name', 'users.email as applicant_email', 'companies.name as company_name', 'companies.email as company_email', 'job_listings.title as job_title')
                ->leftjoin('job_listings', 'job_listings.id', 'job_applications.job_id')
                ->leftjoin('users', 'users.id', 'job_applications.user_id')
                ->leftjoin('companies', 'companies.employer_id', 'users.id')
                ->where('job_applications.id', $request->jobApplicationId)
                ->first();
            // dd($data);
            if ($jobApplication->is_approved == 1)
            {
                $response = APIHelpers::createAPIResponse(true, 400, 'Job application has already been approved!!', $data);
                return response()->json($response, 400);
            }
            $jobApplication->is_approved = 1;
            $jobApplication->save();
            $response = APIHelpers::createAPIResponse(false, 200, 'Job application approved Successfully!!', $data);
            DB::commit();
            // dd($data);
            $subjectLine = 'Job Application Review';
            $viewName = 'emails.approval';
            // dd($mailData);
            // SendReviewEmail::dispatch($subjectLine, $viewName, $data, $data->applicant_email);
            SendReviewEmail::dispatch($subjectLine, $viewName, $data, $data->applicant_email)->delay(now()->addMinutes(10));


            return response()->json($response, 200);
        }
        catch (Exception $e)
        {
            if ($request->wantsJson())
            {
                $response = APIHelpers::createAPIResponse(true, 400, $e->getMessage(), null);
                return response()->json([$response], 400);
            }
        }
    }

    public function reject(Request $request)
    {
        DB::beginTransaction();
        try
        {
            $jobApplication = JobApplication::findOrFail($request->jobApplicationId);
            $check = APIHelpers::employerAuthentication($jobApplication->job_id);
            if ($check == NULL)
            {
                $response = APIHelpers::createAPIResponse(true, 401, 'Unauthorized Access', NULL);
                return response()->json($response, 402);
            }
            $data = JobApplication::select('job_applications.*', 'users.name as applicant_name', 'users.email as applicant_email', 'companies.name as company_name', 'companies.email as company_email', 'job_listings.title as job_title')
                ->leftjoin('job_listings', 'job_listings.id', 'job_applications.job_id')
                ->leftjoin('users', 'users.id', 'job_applications.user_id')
                ->leftjoin('companies', 'companies.employer_id', 'users.id')
                ->where('job_applications.id', $request->jobApplicationId)
                ->first();
            // dd($data);
            if ($jobApplication->is_rejected == 1)
            {
                $response = APIHelpers::createAPIResponse(true, 400, 'Job application has already been rejected!!', $data);
                return response()->json($response, 400);
            }
            $jobApplication->is_rejected = 1;
            $jobApplication->save();
            $response = APIHelpers::createAPIResponse(false, 200, 'Job application rejected Successfully!!', $data);
            DB::commit();
            $subjectLine = 'Job Application Review';
            $viewName = 'emails.rejection';
            // dd($mailData);
            SendReviewEmail::dispatch($subjectLine, $viewName, $data, $data->applicant_email)->delay(now()->addMinutes(10));
            return response()->json($response, 200);
        }
        catch (Exception $e)
        {
            if ($request->wantsJson())
            {
                $response = APIHelpers::createAPIResponse(true, 400, $e->getMessage(), null);
                return response()->json([$response], 400);
            }
        }
    }
}
