<?php

namespace App\Http\Controllers;

use App\Models\JobApplication;
use App\Jobs\SendReviewMail;
use App\Models\JobListing;
use App\Models\User;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Helpers\APIHelpers;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use App\Mail\JobApplicationNotificationMail;
use Exception;

class JobApplicationController extends Controller
{
    //Submit job application
    /**
     * @OA\Post(
     *     path="/api/submit-job-application",
     *     summary="Submit job application",
     *     tags={"Job Applications"},
     *      security={ {"sanctum": {} }},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="jobId",
     *                     description="Id of Job",
     *                     type="integer",
     *                 ),
     *                 @OA\Property(
     *                     property="resume",
     *                     description="File to upload",
     *                     type="string",
     *                     format="binary"
     *                 ),
     *                 @OA\Property(
     *                     property="coverLetter",
     *                     description="Cover letter for job",
     *                     type="string",
     *                 ),
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer", example="1", description="Job Application Id"),
     *             @OA\Property(property="user_id", type="integer", example="1", description="Job Application Applicant Id"),
     *             @OA\Property(property="job_id", type="integer", example="1", description="Job Id"),
     *             @OA\Property(property="resume", type="string", example="laravel Developer", description="Job Applicant's Resume"),
     *             @OA\Property(property="cover_letter", type="string", example="Firefly Tech", description="Job Applicant's Cover Letter"),
     *      )     
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation Unsucessful"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Job not found"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Repeated Action"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error"
     *     ),
     * )
     */
    public function submitApplication(Request $request)
    {
        DB::beginTransaction();
        try
        {
            $validator = Validator::make($request->all(), [
                'resume' => 'required|mimetypes:application/pdf|max:10000',
                'coverLetter' => 'required',
                'jobId' => 'required'
            ]);
            if ($validator->fails())
            {
                $response = APIHelpers::createAPIResponse(true, 422, $validator->errors()->all(), null);
                return response()->json([$response], 422);
            }
            $restriction = JobListing::select('companies.employer_id')->leftJoin('companies', 'companies.id', 'job_listings.company_id')->where('job_listings.id', $request->jobId)->first();
            //Submission restricted for the employer who has posted the job
            if (auth()->user()->id == $restriction->employer_id)
            {
                $response = APIHelpers::createAPIResponse(true, 400, 'Bad Request!', Auth::user()->name);
                return response()->json($response, 400);
            }

            //Check if the job exsists
            $jobCheck = JobListing::where('id', $request->jobId)->where('status', 1)->get() ?? NULL;
            if ($jobCheck == NULL)
            {
                $response = APIHelpers::createAPIResponse(true, 404, 'The job doesnot exsist!!', Auth::user()->name);
                return response()->json($response, 404);
            }

            //Check if the user has applied the job before
            $check = JobApplication::where('job_id', $request->jobId)->where('user_id', Auth::user()->id)->first() ?? NULL;
            if ($check != NULL)
            {
                $response = APIHelpers::createAPIResponse(true, 400, 'You have already applied for this job before!', Auth::user()->name);
                return response()->json($response, 400);
            }

            // Create a unique name for the file and move the file to public path
            $filePath = '/job-applications/resume/';
            if (!file_exists(public_path() . $filePath))
            {
                mkdir(public_path() . $filePath, 0777, true);
            }
            $fileName =  $filePath . time() . '.' . Str::random(4) . '.' . $request->resume->extension();
            $file = $request->file('resume');
            $file->move(public_path() . $filePath, $fileName);

            $jobApplication = new JobApplication();
            $jobApplication->resume = $fileName;
            $jobApplication->cover_letter = $request->coverLetter;
            $jobApplication->job_id = $request->jobId;
            $jobApplication->user_id = Auth::user()->id;
            $jobApplication->save();

            $employer = User::select('users.email as employerEmail')
                ->leftJoin('companies', 'companies.employer_id', 'users.id')
                ->leftJoin('job_listings', 'job_listings.company_id', 'companies.id')
                ->where('job_listings.id', $jobApplication->job_id)
                ->first();

            $response = APIHelpers::createAPIResponse(false, 200, 'Job application Submitted Successfully!!', $jobApplication);
            $subjectLine = "Job Application Submission Notification";
            $viewName = 'emails.notification';
            DB::commit();
            Mail::to($employer->employerEmail)->send(new JobApplicationNotificationMail($subjectLine, $viewName, $jobApplication, $employer->employerEmail));
            return response()->json($response, 200);
        }

        catch (Exception $e)
        {
            DB::rollBack();
            $response = APIHelpers::createAPIResponse(true, 500, $e->getMessage(), null);
            return response()->json([$response], 500);
        }
    }

    //Get job applications submitted by job-seekers
    /**
     * @OA\Get(
     *     path="/api/employer/job-applications",
     *     summary="Employers get all active job submissions for its company",
     *     tags={"Job Applications"},
     *      security={ {"sanctum": {} }},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type = "object",
     *             @OA\Property(property="id", type="integer", example="2", description="Job Application Id"),
     *             @OA\Property(property="resume", type="file", example="Sample.pdf", description="Job Applicant's Resume"),
     *             @OA\Property(property="cover_letter", type="string", example="Develop and maintain software.", description="Job Applicant's Coverletter"),
     *             @OA\Property(property="user_id", type="integer", example="2", description="Job Job Applicant's Id"),
     *             @OA\Property(property="job_id", type="integer", example="2", description="Job Application's Company Id"),
     *             @OA\Property(property="is_approved", type="boolean", example="0", description="Job Application's Approval Status"),
     *             @OA\Property(property="is_rejected", type="boolean", example="0", description="Job Application's Rejection Status"),
     *             @OA\Property(property="status", type="boolean", example="1", description="Job Application's Status"),
     *             @OA\Property(property="job_title", type="string", example="Laravel Developer", description="Job Title"),
     *             @OA\Property(property="company", type="string", example="Firefly Tech", description="Job Application Company Name"),
     *             @OA\Property(property="company_email", type="string", example="company@gmail.com", description="Job Application Company Email"),
     *             @OA\Property(property="applicant_name", type="string", example="seeker", description="Job Application Applicant Name"),
     *             @OA\Property(property="applicant_email", type="string", example="applicant@gmail.com", description="Job Application Applicant Name"),
     * 
     *          )     
     **     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error"
     *     ),
     * )
     */

    public function getApplication(Request $request)
    {
        try
        {
            $paginate = intval($request->get("length", env('PAGINATION', 5)));
            $job_applications = JobApplication::select('job_applications.*', 'job_listings.title as job_title', 'companies.name as company', 'companies.email as company_email', 'users.name as applicant_name', 'users.email as applicant_email')
                ->leftjoin('job_listings', 'job_listings.id', 'job_applications.job_id')
                ->leftjoin('users', 'users.id', 'job_applications.user_id')
                ->leftjoin('companies', 'companies.id', 'job_listings.company_id')
                ->where('job_listings.deleted_at', NULL)
                ->where('companies.deleted_at', NULL)
                ->where('users.deleted_at', NULL)
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
            $response = APIHelpers::createAPIResponse(true, 500, $e->getMessage(), null);
            return response()->json([$response], 500);
        }
    }

    //Approve Job-application
    /**
     * @OA\Post(
     *     path="/api/employer/job-application-approve",
     *     summary="Approve the job-applications",
     *     tags={"Job Applications"},
     *      security={ {"sanctum": {} }},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="jobApplicationId", type="integer", example="2"),
     *      )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type = "object",
     *             @OA\Property(property="id", type="integer", example="2", description="Job Application Id"),
     *             @OA\Property(property="resume", type="file", example="Sample.pdf", description="Job Application Resume"),
     *             @OA\Property(property="cover_letter", type="string", example="Develop and maintain software.", description="Job Application Coverletter"),
     *             @OA\Property(property="user_id", type="integer", example="2", description="Job Application Applicant Id"),
     *             @OA\Property(property="job_id", type="integer", example="2", description="Job Application Job Id"),
     *             @OA\Property(property="is_approved", type="boolean", example="0", description="Job Application Approval Status"),
     *             @OA\Property(property="is_rejected", type="boolean", example="0", description="Job Application Rejection Status"),
     *             @OA\Property(property="status", type="boolean", example="1", description="Job Application Status"),
     *             @OA\Property(property="job_title", type="string", example="Laravel Developer", description="Job Application Job Title"),
     *             @OA\Property(property="company", type="string", example="Firefly Tech", description="Job's Company Name"),
     *             @OA\Property(property="company_email", type="string", example="company@gmail.com", description="Job's Company Email"),
     *             @OA\Property(property="applicant_name", type="string", example="seeker", description="Job Applicant Name"),
     *             @OA\Property(property="applicant_email", type="string", example="applicant@gmail.com", description="Job Applicant Email"),
     * 
     *          )     
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation Unsucessful"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden Access"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Repeated Action"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error"
     *     ),
     * )
     */

    public function approve(Request $request)
    {
        DB::beginTransaction();
        try
        {
            $validator = Validator::make($request->all(), [
                'jobApplicationId' => 'required',
            ]);
            if ($validator->fails())
            {
                $response = APIHelpers::createAPIResponse(true, 422, $validator->errors()->all(), null);
                return response()->json([$response], 422);
            }
            $jobApplication = JobApplication::findOrFail($request->jobApplicationId);
            $check = APIHelpers::employerAuthentication($jobApplication->job_id);
            if ($check == NULL)
            {
                $response = APIHelpers::createAPIResponse(true, 403, 'Forbidden Access', NULL);
                return response()->json($response, 403);
            }
            $data = JobApplication::select('job_applications.*', 'users.name as applicant_name', 'users.email as applicant_email', 'companies.name as company_name', 'companies.email as company_email', 'job_listings.title as job_title')
                ->leftjoin('job_listings', 'job_listings.id', 'job_applications.job_id')
                ->leftjoin('users', 'users.id', 'job_applications.user_id')
                ->leftjoin('companies', 'job_listings.company_id', 'companies.id')
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
            $subjectLine = 'Job Application Review';
            $viewName = 'emails.approval';
            // SendReviewMail::dispatch($subjectLine, $viewName, $data, $data->applicant_email);
            SendReviewMail::dispatch($subjectLine, $viewName, $data, $data->applicant_email)->delay(now()->addMinutes(10));
            return response()->json($response, 200);
        }
        catch (Exception $e)
        {
            $response = APIHelpers::createAPIResponse(true, 500, $e->getMessage(), null);
            return response()->json([$response], 500);
        }
    }


    //Reject Job-application
    /**
     * @OA\Post(
     *     path="/api/employer/job-application-reject",
     *     summary="Reject the job-applications",
     *     tags={"Job Applications"},
     *      security={ {"sanctum": {} }},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="jobApplicationId", type="integer", example="2"),
     *      )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type = "object",
     *             @OA\Property(property="id", type="integer", example="2", description="Job Application Id"),
     *             @OA\Property(property="resume", type="file", example="Sample.pdf", description="Job Application Resume"),
     *             @OA\Property(property="cover_letter", type="string", example="Develop and maintain software.", description="Job Application Coverletter"),
     *             @OA\Property(property="user_id", type="integer", example="2", description="Job Application Applicant Id"),
     *             @OA\Property(property="job_id", type="integer", example="2", description="Job Application Job Id"),
     *             @OA\Property(property="is_approved", type="boolean", example="0", description="Job Application Approval Status"),
     *             @OA\Property(property="is_rejected", type="boolean", example="0", description="Job Application Rejection Status"),
     *             @OA\Property(property="status", type="boolean", example="1", description="Job Application Status"),
     *             @OA\Property(property="job_title", type="string", example="Laravel Developer", description="Job Application Job Title"),
     *             @OA\Property(property="company", type="string", example="Firefly Tech", description="Job's Company Name"),
     *             @OA\Property(property="company_email", type="string", example="company@gmail.com", description="Job's Company Email"),
     *             @OA\Property(property="applicant_name", type="string", example="seeker", description="Job Applicant Name"),
     *             @OA\Property(property="applicant_email", type="string", example="applicant@gmail.com", description="Job Applicant Email"),
     * 
     *          )     
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation Unsucessful"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden Access"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Repeated Action"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error"
     *     ),
     * )
     */
    public function reject(Request $request)
    {
        DB::beginTransaction();
        try
        {
            $validator = Validator::make($request->all(), [
                'jobApplicationId' => 'required',
            ]);
            if ($validator->fails())
            {
                $response = APIHelpers::createAPIResponse(true, 422, $validator->errors()->all(), null);
                return response()->json([$response], 422);
            }
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
            SendReviewMail::dispatch($subjectLine, $viewName, $data, $data->applicant_email)->delay(now()->addMinutes(10));
            return response()->json($response, 200);
        }
        catch (Exception $e)
        {
            $response = APIHelpers::createAPIResponse(true, 400, $e->getMessage(), null);
            return response()->json([$response], 400);
        }
    }
}
