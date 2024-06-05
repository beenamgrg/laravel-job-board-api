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




class JobController extends Controller
{
    public function getjobs(Request $request)
    {
        try
        {
            $paginate = intval($request->get("length", env('PAGINATION', 5)));
            $jobs = JobListing::select('job_listings.*', 'companies.name as company', 'companies.address as location', 'companies.email as company_email')
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
                $response = APIHelpers::createAPIResponse(true, 400, $e->getMessage(), null);
                return response()->json([$response], 400);
            }
        }
    }
    public function store(Request $request)
    {
        DB::beginTransaction();
        try
        {
            $validator = Validator::make($request->all(), [
                'title' => 'required',
                'company_name' => 'required',
                'description' => 'required',
                'application_instruction' => 'required',
            ]);
            if ($validator->fails())
            {
                return response(['errors' => $validator->errors()->all()], 422);
            }
            $company_slug = str_replace(' ', '-', strtolower($request->company_name));

            //check if the company exsists or not
            $check = Company::where('slug', $company_slug)->first() ?? NULL;
            if ($check == NULL)
            {
                $response = APIHelpers::createAPIResponse(false, 402, 'Please register the company first', NULL);
                return response()->json($response, 402);
            }


            $job = new JobListing();
            $job->title = $request->title;
            $job->company_id = $check->id;
            $job->description = $request->description;
            $job->application_instruction = $request->application_instruction;
            $job->save();
            APIHelpers::jobListingLog(Auth::user()->id, $job->id, 'create-job');
            $response = APIHelpers::createAPIResponse(false, 200, 'A job has been successfully created!!', $job->title);
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

    public function update(Request $request)
    {
        DB::beginTransaction();
        try
        {
            $validator = Validator::make($request->all(), [
                'title' => 'required',
                'company_name' => 'required',
                'description' => 'required',
                'application_instruction' => 'required',
            ]);
            if ($validator->fails())
            {
                return response(['errors' => $validator->errors()->all()], 422);
            }

            $company_slug = str_replace(' ', '-', strtolower($request->company_name));

            //check if company exsists or not
            $check = Company::where('slug', $company_slug)->first() ?? NULL;
            if ($check == NULL)
            {
                $response = APIHelpers::createAPIResponse(true, 402, 'Please register the company first', NULL);
                return response()->json($response, 402);
            }


            $job = JobListing::where('id', $request->id)->firstOrFail();
            $job->title = $request->title;
            $job->company_id = $check->id;
            $job->description = $request->description;
            $job->application_instruction = $request->application_instruction;
            $job->save();
            $response = APIHelpers::createAPIResponse(false, 200, 'A job has been successfully updated!!', $job->title);
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

    public function delete(Request $request)
    {
        DB::beginTransaction();
        try
        {
            JobListing::where('id', $request->id)->delete();
            $response = APIHelpers::createAPIResponse(false, 200, 'A job has been successfully deleted!!', NULL);
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

    public function search(Request $request)
    {
        try
        {
            $keyword = $request->keyword;
            $paginate = intval($request->get("length", env('PAGINATION', 5)));
            $jobs = JobListing::select('job_listings.title as title', 'companies.name as c_name', 'companies.address as c_addr')
                ->leftjoin('companies', 'companies.id', '=', 'job_listings.company_id')
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
                $response = APIHelpers::createAPIResponse(true, 400, $e->getMessage(), null);
                return response()->json([$response], 400);
            }
        }
    }
}
