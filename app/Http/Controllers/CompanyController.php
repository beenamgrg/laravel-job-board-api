<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use App\Models\Company;
use Illuminate\Support\Facades\DB;
use App\Helpers\APIHelpers;
use Illuminate\Support\Facades\Validator;


class CompanyController extends Controller
{
    public function store(Request $request)
    {
        DB::beginTransaction();
        try
        {
            $validator = Validator::make($request->all(), [
                'name' => 'required',
                'address' => 'required',
                'description' => 'required',
            ]);
            if ($validator->fails())
            {
                return response(['errors' => $validator->errors()->all()], 422);
            }

            $company = new Company();
            $company->name = $request->name;
            $company->slug = str_replace(' ', '-', strtolower($request->name));
            $company->address = $request->address;
            $company->description = $request->description;
            $company->save();
            $response = APIHelpers::createAPIResponse(false, 200, 'A company has been successfully created!!', $company->name);
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
