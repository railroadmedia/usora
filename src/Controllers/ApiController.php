<?php

namespace Railroad\Usora\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Railroad\Usora\Entities\User;
use Illuminate\Http\Request;
use Tymon\JWTAuth\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class ApiController extends Controller
{
    public $loginAfterSignUp = true;

    private $jwtAuth;

    /**
     * ApiController constructor.
     *
     * @param bool $loginAfterSignUp
     */
    public function __construct(JWTAuth $jwtAuth)
    {
        $this->jwtAuth = $jwtAuth;
    }

    public function login(Request $request)
    {
        $input = $request->only('email', 'password');
        $jwt_token = null;
        if (!Auth::attempt($input)) {
            throw new InvalidCredentials('Invalid email or password provided.');
        }
//        dd($this->jwtAuth->attempt($input) );
//        $jwt_token = JWTAuth::attemp(Auth::user());

        if (!$jwt_token = $this->jwtAuth->attempt($input)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid Email or Password',
            ], 401);
        }
//
        return response()->json([
            'success' => true,
            'token' => $jwt_token,
        ]);
    }

    public function logout(Request $request)
    {
        $this->validate($request, [
            'token' => 'required'
        ]);

        try {
            JWTAuth::invalidate($request->token);

            return response()->json([
                'success' => true,
                'message' => 'User logged out successfully'
            ]);
        } catch (JWTException $exception) {
            return response()->json([
                'success' => false,
                'message' => 'Sorry, the user cannot be logged out'
            ], 500);
        }
    }

    public function getAuthUser(Request $request)
    {
        $this->validate($request, [
            'token' => 'required'
        ]);

        $user = JWTAuth::authenticate($request->token);

        return response()->json(['user' => $user]);
    }
}