<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use App\Models\Company;
use App\Models\JobListing;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Helpers\APIHelpers;
use Illuminate\Support\Facades\Validator;
use OpenApi\Annotations as OA;

class JobController extends Controller
{
    //Functions accessed job-employers only

    //Get Job
    /**
     * @OA\Get(
     *     path="/api/employer/job-listings",
     *     summary="Get list of jobs listed by employer",
     *     tags={"JobListings"},
     *      security={{"bearer_token":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/JobListing"))
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error"
     *     )
     * )
     */
    public function getjobs(Request $request)
    {
        try
        {
            $paginate = intval($request->get("length", env('PAGINATION', 5)));
            $jobs = JobListing::select('job_listings.*', 'companies.name as company', 'companies.address as location', 'companies.email as company_email')
                ->leftjoin('companies', 'companies.id', 'job_listings.company_id')
                ->where('companies.employer_id', Auth::user()->id)
                ->groupBy('job_listings.id', 'companies.id')
                ->orderBy('job_listings.id', 'DESC')
                ->paginate($paginate);
            $response = $jobs->count() > 0 ? APIHelpers::createAPIResponse(false, 200, 'List of the jobs created by ' . Auth::user()->name, $jobs) : APIHelpers::createAPIResponse(false, 200, 'No jobs created yest', NULL);
            return response()->json($response, 200);
        }
        catch (Exception $e)
        {
            if ($request->wantsJson())
            {
                $response = APIHelpers::createAPIResponse(true, 500, $e->getMessage(), null);
                return response()->json([$response], 500);
            }
        }
    }

    // Store Job
    /**
     * @OA\Post(
     *     path="/api/employer/job-store",
     *     summary="Store job",
     *     tags={"Job Listings"},
     *      security={{"bearer_token":{}}},
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="title", type="string", example="Software Developer"),
     *             @OA\Property(property="description", type="string", example="Develop and maintain software."),
     *             @OA\Property(property="applicationInstruction", type="string", example="Please apply through our website.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/JobListing"))
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
     *         response=500,
     *         description="Internal Server Error"
     *     )
     * )
     */

    public function store(Request $request)
    {
        DB::beginTransaction();
        try
        {
            $validator = Validator::make($request->all(), [
                'title' => 'required',
                'description' => 'required',
                'applicationInstruction' => 'required',
            ]);
            if ($validator->fails())
            {
                $response = APIHelpers::createAPIResponse(true, 422, $validator->errors()->all(), null);
                return response()->json([$response], 422);
            }
            $job = new JobListing();
            $job->title = $request->title;
            $job->company_id = Company::where('employer_id', Auth::user()->id)->first()->id;
            $job->description = $request->description;
            $job->application_instruction = $request->applicationInstruction;
            $job->save();

            APIHelpers::jobListingLog(Auth::user()->id, $job->id, 'create-job');
            $response = APIHelpers::createAPIResponse(false, 200, 'The job has been successfully created!!', $job);
            DB::commit();
            return response()->json($response, 200);
        }

        catch (Exception $e)
        {
            DB::rollBack();
            if ($request->wantsJson())
            {
                $response = APIHelpers::createAPIResponse(true, 500, $e->getMessage(), null);
                return response()->json([$response], 500);
            }
        }
    }

    //Update Job
    /**
     * @OA\Put(
     *     path="/api/employer/job-update",
     *     summary="Update job",
     *     tags={"Job Listings"},
     *      security={{"bearer_token":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="jobId", type="integer", example="1"),
     *             @OA\Property(property="title", type="string", example="Software Developer"),
     *             @OA\Property(property="description", type="string", example="Develop and maintain software."),
     *             @OA\Property(property="applicationInstruction", type="string", example="Please apply through our website.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/JobListing"))
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
     *         response=500,
     *         description="Internal Server Error"
     *     )
     * )
     */

    public function update(Request $request)
    {
        DB::beginTransaction();
        try
        {
            $validator = Validator::make($request->all(), [
                'jobId' => 'required',
                'title' => 'required',
                'description' => 'required',
                'applicationInstruction' => 'required',
            ]);
            if ($validator->fails())
            {
                $response = APIHelpers::createAPIResponse(true, 422, $validator->errors()->all(), null);
                return response()->json([$response], 422);
            }

            //check if job exsists or not
            $check = APIHelpers::employerAuthentication($request->jobId);
            if ($check == NULL)
            {
                $response = APIHelpers::createAPIResponse(true, 403, 'Forbidden Access', NULL);
                return response()->json($response, 403);
            }
            $job = JobListing::where('id', $request->jobId)->firstOrFail();
            $job->title = $request->title;
            $job->company_id = $check->id;
            $job->description = $request->description;
            $job->application_instruction = $request->applicationInstruction;
            $job->save();
            $response = APIHelpers::createAPIResponse(false, 200, 'The job has been successfully updated!!', $job);
            DB::commit();
            return response()->json($response, 200);
        }

        catch (Exception $e)
        {
            DB::rollBack();
            if ($request->wantsJson())
            {
                $response = APIHelpers::createAPIResponse(true, 500, $e->getMessage(), null);
                return response()->json([$response], 500);
            }
        }
    }

    //Delete Job
    /**
     * @OA\Delete(
     *     path="/api/employer/job-delete",
     *     summary="Delete job",
     *     tags={"Job Listings"},
     *      security={{"bearer_token":{}}},
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="jobId", type="integer", example="1"),
     *         )
     *     ),

     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/JobListing"))
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
     *         response=500,
     *         description="Internal Server Error"
     *     )
     * )
     */

    public function delete(Request $request)
    {
        DB::beginTransaction();
        try
        {
            $validator = Validator::make($request->all(), [
                'jobId' => 'required',
            ]);
            if ($validator->fails())
            {
                $response = APIHelpers::createAPIResponse(true, 422, $validator->errors()->all(), null);
                return response()->json([$response], 422);
            }
            //check if job exsists or not
            $check = APIHelpers::employerAuthentication($request->jobId);
            if ($check == NULL)
            {
                $response = APIHelpers::createAPIResponse(true, 403, 'Forbidden Access', NULL);
                return response()->json($response, 403);
            }
            JobListing::where('id', $request->jobId)->delete();
            $response = APIHelpers::createAPIResponse(false, 200, 'The job has been successfully deleted!!', NULL);
            DB::commit();
            return response()->json($response, 200);
        }
        catch (Exception $e)
        {
            DB::rollBack();
            if ($request->wantsJson())
            {
                $response = APIHelpers::createAPIResponse(true, 500, $e->getMessage(), null);
                return response()->json([$response], 500);
            }
        }
    }


    //Functions accessed by seekers and job-employers

    //Search Job
    /**
     * @OA\Get(
     *     path="/api/search",
     *     summary="Search job",
     *     tags={"Job Listings"},
     *     @OA\Parameter(
     *         name="keyword",
     *         in="path",
     *         description="keyword for searching the job",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/JobListing"))
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error"
     *     )
     * )
     */
    public function search(Request $request)
    {
        try
        {
            $keyword = $request->keyword;
            $paginate = intval($request->get("length", env('PAGINATION', 5)));
            $jobs = JobListing::select('job_listings.id as jobId', 'job_listings.title as title', 'companies.name as companyName', 'companies.address as companyAddress')
                ->leftjoin('companies', 'companies.id', 'job_listings.company_id')
                ->groupBy('job_listings.id', 'companies.id')
                ->orderBy('job_listings.id', 'DESC')
                ->where('job_listings.status', 1)
                ->Where(function ($query) use ($keyword)
                {
                    if ($keyword != NULL)
                    {
                        $query->where('job_listings.title', 'LIKE', '%' . $keyword . '%');
                        $query->orWhere('companies.name', 'LIKE', '%' . $keyword . '%');
                        $query->orwhere('companies.address', 'LIKE', '%' . $keyword . '%');
                    }
                })
                ->paginate($paginate);
            $response = $jobs->count() > 0 ? APIHelpers::createAPIResponse(false, 200, 'Search Results:', $jobs) : APIHelpers::createAPIResponse(false, 200, 'No results found!', NULL);
            DB::commit();
            return response()->json($response, 200);
        }
        catch (Exception $e)
        {
            if ($request->wantsJson())
            {
                $response = APIHelpers::createAPIResponse(true, 500, $e->getMessage(), null);
                return response()->json([$response], 500);
            }
        }
    }

    //Get job
    /**
     * @OA\Get(
     *     path="/api/job-listings",
     *     summary="Get list of active jobs listed ",
     *     tags={"JobListings"},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/JobListing"))
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server Error"
     *     )
     * )
     */
    public function getAllJobs(Request $request)
    {
        try
        {
            $paginate = intval($request->get("length", env('PAGINATION', 5)));
            $jobs = JobListing::select('job_listings.id as jobId', 'job_listings.title as title', 'job_listings.description as jobDescription', 'job_listings.application_instruction as applicationInstruction', 'job_listings.status as status', 'companies.name as company', 'companies.address as location', 'companies.email as companyEmail')
                ->leftjoin('companies', 'companies.id', 'job_listings.company_id')
                ->where('job_listings.status', 1)
                ->groupBy('job_listings.id', 'companies.id')
                ->orderBy('job_listings.id', 'DESC')
                ->paginate($paginate);
            $response = $jobs->count() > 0 ? APIHelpers::createAPIResponse(false, 200, 'List of the active job submissions!!', $jobs) : APIHelpers::createAPIResponse(false, 200, 'No Active Job-Submissions at Moment!', NULL);
            return response()->json($response, 200);
        }
        catch (Exception $e)
        {
            if ($request->wantsJson())
            {
                $response = APIHelpers::createAPIResponse(true, 500, $e->getMessage(), null);
                return response()->json([$response], 500);
            }
        }
    }
}
