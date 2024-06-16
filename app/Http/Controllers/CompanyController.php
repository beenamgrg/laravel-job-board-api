<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use App\Models\Company;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use App\Helpers\APIHelpers;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\CompanyActivationMail;
use App\Mail\CompanyRegistrationMail;




class CompanyController extends Controller
{
    //Store Company 
    /**
     * @OA\Post(
     *     path="/api/company-store",
     *     summary="Company Store",
     *     tags={"Companies"},
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="employerFullName", type="string", example="John Doe", description="Full Name of Employer"),
     *             @OA\Property(property="employerEmail", type="string", example="john@gmail.com", description="Email of Employer"),
     *             @OA\Property(property="employerPassword", type="string", example="password", description="Password of Employer"),
     *             @OA\Property(property="employerConfirmPassword", type="string", example="password", description="Confirm Password of Employer"),
     *             @OA\Property(property="companyName", type="string", example="Tech Company", description="Full name of Company"),
     *             @OA\Property(property="companyEmail", type="string", example="company@email.com", description="Email of Company"),
     *             @OA\Property(property="companyAddress", type="string", example="Tech Company", description="Address of Company"),
     *             @OA\Property(property="companyPhone", type="string", example="Tech Company", description="Phone of Company"),
     *             @OA\Property(property="companyDescription", type="string", example="Tech Company", description="Description of Company"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="company_name", type="string", example="tech Company", description="Name of Company"),
     *         )     
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation Unsucessful"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request"
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
                'employerFullName' => 'required',
                'employerEmail' => 'required|email|unique:users,email',
                'employerPassword' => 'required|min:6',
                'employerConfirmPassword' => 'required|min:6|same:employerPassword',
                'companyName' => 'required',
                'companyEmail' => 'required|email|unique:companies,email',
                'companyAddress' => 'required',
                'companyPhone' => 'required',
                'companyDescription' => 'required',
            ]);
            if ($validator->fails())
            {
                return response(['errors' => $validator->errors()->all()], 422);
            }
            $check = Company::where('slug', str_replace(' ', '-', strtolower($request->companyName)))->first() ?? NULL;
            if ($check != NULL)
            {
                $response = APIHelpers::createAPIResponse(true, 400, 'The company with this name has already been registered before!!', NULL);
                return response()->json($response, 400);
            }

            $user = new User();
            $user->name = $request->employerFullName;
            $user->email = $request->employerEmail;
            $user->password = Hash::make($request->employerPassword);
            $user->role = 'employer';
            $user->status = 0;
            $user->save();

            $company = new Company();
            $company->name = $request->companyName;
            $company->slug = str_replace(' ', '-', strtolower($request->companyName));
            $company->email = $request->companyEmail;
            $company->address = $request->companyAddress;
            $company->phone = $request->companyPhone;
            $company->description = $request->companyDescription;
            $company->employer_id = $user->id;
            $company->status = 0;
            $company->save();
            $subjectLine = "Registration of New Company";
            $viewName = 'emails.company-registration';
            $response = APIHelpers::createAPIResponse(false, 200, 'Your company has been registered.We will soon contact you after its activation', $company->name);
            $recipient = env('MAIL_FROM_ADDRESS');
            Mail::to($recipient)->send(new CompanyRegistrationMail($subjectLine, $viewName, $company->id));
            DB::commit();
            return response()->json($response, 200);
        }

        catch (Exception $e)
        {
            DB::rollBack();
            $response = APIHelpers::createAPIResponse(true, 400, $e->getMessage(), null);
            return response()->json([$response], 400);
        }
    }


    //Activation of Company
    //Store Company 
    /**
     * @OA\Post(
     *     path="/api/super-admin/company-activation",
     *     summary="Activate Company",
     *     tags={"Companies"},
     *      security={ {"sanctum": {} }},
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="companyId", type="integer", example="1", description="Id of Company"),
     *             @OA\Property(property="activationStatus", type="boolean", example="0", description="Activation Status of Company"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="employerId", type="integer", example="1", description="Id of Employer"),
     *             @OA\Property(property="employerEmail", type="string", example="company@gmail.com", description="Email of Employer"),
     *             @OA\Property(property="companyId", type="integer", example="1", description="Id of Company"),
     *             @OA\Property(property="companyStatus", type="boolean", example="1", description="Status of Company"),
     *         )     
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation Unsucessful"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Repeated Action"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error"
     *     )
     * )
     */

    public function activateCompany(Request $request)
    {
        DB::beginTransaction();
        try
        {
            $validator = Validator::make($request->all(), [
                'companyId' => 'required',
                'activationStatus' => 'required|boolean',
            ]);
            if ($validator->fails())
            {
                return response(['errors' => $validator->errors()->all()], 422);
            }
            $data = User::select('users.email as employerEmail', 'users.id as employerId', 'companies.status as companyStatus', 'companies.id as companyId')
                ->leftJoin('companies', 'users.id', 'companies.employer_id')
                ->where('companies.id', $request->companyId)
                ->first();
            if ($request->activationStatus == 1 && $data->companyStatus == 1)
            {
                $response = APIHelpers::createAPIResponse(true, 400, 'This company has already been activated before!!', $data);
                return response()->json($response, 400);
            }
            $user = User::where('id', $data->employerId)->first();
            $user->status =  ($request->activationStatus == 1) ? 1 : 0;
            $user->save();
            $company = Company::where('id', $request->companyId)->first();
            $company->status = ($request->activationStatus == 1) ? 1 : 0;
            $company->save();
            $subjectLine = ($request->activationStatus == 1) ? "Account Activation Email" : "Account Deactivation Email";
            $viewName = ($request->activationStatus == 1) ? 'emails.activation' : 'emails.deactivation';
            $response = ($request->activationStatus == 1) ? APIHelpers::createAPIResponse(false, 200, 'The company has been activated successfully!!', $data) : APIHelpers::createAPIResponse(false, 200, 'The company has been deactivated successfully!!', $data);
            DB::commit();
            Mail::to($data->employerEmail)->send(new CompanyActivationMail($subjectLine, $viewName, $data->employerEmail));
            return response()->json($response, 200);
        }
        catch (Exception $e)
        {
            DB::rollBack();
            $response = APIHelpers::createAPIResponse(true, 500, $e->getMessage(), null);
            return response()->json([$response], 500);
        }
    }
}
