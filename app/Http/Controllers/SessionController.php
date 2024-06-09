<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Helpers\APIHelpers;
use Exception;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

/**
 * @OA\Info(
 *      version="1.0.0",
 *      title="Laravel API Documentation",
 *      description="API documentation for Laravel application",
 *      @OA\Contact(
 *          email="admin@example.com"
 *      ),
 *      @OA\License(
 *          name="Apache 2.0",
 *          url="http://www.apache.org/licenses/LICENSE-2.0.html"
 *      )
 * )
 *  * @OA\SecurityScheme(
 *     securityScheme="BearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 * )
 */
class SessionController extends Controller
{
    //User Login
    /**
     * @OA\Post(
     *     path="/api/login",
     *     summary="User Login",
     *     tags={"Users"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="email", type="string", example="employer1@gmail.com", description="User Email"),
     *             @OA\Property(property="password", type="string", example="employer", description="User Password"),
     *      )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer", example="1", description="User id"),
     *             @OA\Property(property="name", type="string", example="John Doe", description="User Name"),
     *             @OA\Property(property="email", type="string", example="johndoe@gmail.com", description="User Email"),
     *             @OA\Property(property="role", type="enum", example="seeker", description="User Role"),
     *             @OA\Property(property="status", type="boolean", example="1",description="User Status"),
     *      )     
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation Unsucessful"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error"
     *     )
     * )
     */
    public function postLogin(Request $request)
    {
        try
        {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'password' => 'required',
            ]);
            $data = array(
                'email' => $request->email,
                'password' => $request->password,
            );
            if ($validator->fails())
            {
                $response = APIHelpers::createAPIResponse(true, 422, $validator->errors()->all(), null);
                return response()->json([$response], 422);
            }
            if (Auth::attempt($data))
            {
                if (Auth::user()->status == 0)
                {
                    $response = APIHelpers::createAPIResponse(true, 401, 'Your account is not active. Please contact the administrator.', null);
                    return response()->json($response, 401);
                }
                if ($request->wantsJson())
                {
                    Auth::user()->tokens()->delete();
                    $token = Auth::user()->createToken('jobBoard')->accessToken;
                    $response = APIHelpers::createAPIResponse(false, 200, 'Successfully logged in', Auth::user());
                    $response['token'] = $token;
                    return response()->json($response, 200);
                }
            }
            else
            {
                $response = APIHelpers::createAPIResponse(true, 401, 'Credentials did not match', null);
                return response()->json([$response], 401);
            }
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


    //user logout
    /**
     * @OA\Post(
     *     path="/api/logout",
     *     summary="User Logout",
     *     tags={"Users"},
     *      security={{"bearer_token":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/User"))
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error"
     *     )
     * )
     */
    public function logout(Request $request)
    {
        try
        {
            if ($request->wantsJson())
            {
                $token = Auth::user()->token();
                $token->revoke();
                $response = APIHelpers::createAPIResponse(false, 200, 'You have been successfully logged out!', NULL);
                return response()->json($response, 200);
            }
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

    //user signup
    /**
     * @OA\Post(
     *     path="/api/sign-up",
     *     summary="User Sign-up",
     *     tags={"Users"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="firstname", type="string", example="John", description="User firstName"),
     *             @OA\Property(property="lastName", type="string", example="Doe", description="User lastNamae"),
     *             @OA\Property(property="email", type="string", example="johndoe@gmail.com", description="User email"),
     *             @OA\Property(property="password", type="string", example="employer", description="User password"),
     *             @OA\Property(property="confirmPassword", type="string", example="employer", description="User confirm password"),
     *      )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer", example="1", description="user id"),
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", example="johndoe@gmail.com"),
     *      )     
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation Unsucessful"
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
                'firstName' => 'required',
                'lastName' => 'required',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|min:6',
                'confirmPassword' => 'required|min:6|same:password',
            ]);
            if ($validator->fails())
            {
                $response = APIHelpers::createAPIResponse(true, 422, $validator->errors()->all(), null);
                return response()->json([$response], 422);
            }
            $user = new User();
            $user->name = $request->firstName . ' ' . $request->lastName;
            $user->email = $request->email;
            $user->password = $request->password;
            $user->role = 'seeker';
            $user->save();
            $data = array(
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            );
            if ($request->wantsJson())
            {
                auth()->loginUsingId($user->id);
                $token = Auth::user()->createToken('jobboard')->accessToken;
                $response = APIHelpers::createAPIResponse(false, 200, 'Welcome to the job-board family', $data);
                $response['token'] = $token;
                DB::commit();
                return response()->json($response, 200);
            }
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
}
