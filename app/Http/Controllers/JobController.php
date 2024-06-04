<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use App\Models\Company;
use App\Models\JobListing;
use Illuminate\Support\Facades\DB;
use App\Helpers\APIHelpers;
use Illuminate\Support\Facades\Validator;




class JobController extends Controller
{
    public function index()
    {
        dd('no middleware');
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
            $check = Company::where('slug', $company_slug)->first() ?? NULL;
            if ($check == NULL)
            {
                $response = APIHelpers::createAPIResponse(false, 402, 'Please register the company first', NULL);
                return response()->json($response, 200);
            }
            $job = new JobListing();
            $job->title = $request->title;
            $job->company_id = $check->id;
            $job->description = $request->description;
            $job->application_instruction = $request->application_instruction;
            $job->save();
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
}
