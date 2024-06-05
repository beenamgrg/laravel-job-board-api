<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Helpers\APIHelpers;
use Exception;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class SessionController extends Controller
{
    //User Login
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
                return response(['errors' => $validator->errors()->all()], 422);
            }
            if (Auth::attempt($data))
            {
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
                $response = APIHelpers::createAPIResponse(true, 400, $e->getMessage(), null);
                return response()->json([$response], 400);
            }
        }
    }


    //user logout
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
                $response = APIHelpers::createAPIResponse(true, 400, $e->getMessage(), null);
                return response()->json([$response], 400);
            }
        }
    }

    //user signup

    public function store(Request $request)
    {
        DB::beginTransaction();
        try
        {
            $validator = Validator::make($request->all(), [
                'first_name' => 'required',
                'last_name' => 'required',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|min:6',
                'confirm_password' => 'required|min:6|same:password',
            ]);
            if ($validator->fails())
            {
                return response(['errors' => $validator->errors()->all()], 422);
            }
            $user = new User();
            $user->name = $request->first_name . ' ' . $request->last_name;
            $user->email = $request->email;
            $user->password = $request->password;
            $user->role = 'seeker';
            $user->save();
            $data = array(
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
                $response = APIHelpers::createAPIResponse(true, 400, $e->getMessage(), null);
                return response()->json([$response], 400);
            }
        }
    }
}
