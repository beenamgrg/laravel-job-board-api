<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Helpers\APIHelpers;
use Auth;
use Exception;

class SessionController extends Controller
{
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
                    $response = APIHelpers::createAPIResponse(false, 200, 'Successfully logged in', $data['email']);
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
                dd($e);
                $response = APIHelpers::createAPIResponse(true, 400, $e->getMessage(), null);
                return response()->json([$response], 400);
            }
        }
    }
}
