<?php

namespace Railroad\Usora\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Railroad\Usora\Services\ResponseService;
use Spatie\Fractal\Fractal;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\JWTAuth;

class ApiController extends Controller
{
    /**
     * @var JWTAuth
     */
    private $jwtAuth;

    public $loginAfterSignUp = true;

    /**
     * ApiController constructor.
     *
     * @param JWTAuth $jwtAuth
     */
    public function __construct(JWTAuth $jwtAuth)
    {
        $this->jwtAuth = $jwtAuth;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function login(Request $request)
    {
        $input = $request->only('email', 'password');
        $jwt_token = null;

        if (!$jwt_token = $this->jwtAuth->attempt($input)) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Invalid Email or Password',
                ],
                401
            );
        }

        return response()->json(
            [
                'success' => true,
                'token' => $jwt_token,
                'userId' => auth()->id(),
            ]
        );
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function logout(Request $request)
    {
        $validator = validator(
            $request->all(),
            [
                'token' => 'required',

            ]
        );

        if ($validator->fails()) {
            return response('');
        }

        try {
            $this->jwtAuth->invalidate($this->jwtAuth->parseToken());

            return response()->json(
                [
                    'success' => true,
                    'message' => 'Successfully logged out',
                ]
            );
        } catch (JWTException $exception) {

            return response()->json(
                [
                    'success' => false,
                    'message' => 'Sorry, the user cannot be logged out',
                ],
                500
            );
        }
    }

    /**
     * @param Request $request
     * @return Fractal
     * @throws JWTException
     */
    public function getAuthUser(Request $request)
    {
        try {
            if (!$user =
                $this->jwtAuth->parseToken()
                    ->authenticate()) {
                return response()->json(['user_not_found'], 404);
            }

        } catch (TokenExpiredException $e) {

            return response()->json(['token_expired'], $e->getStatusCode());

        } catch (TokenInvalidException $e) {

            return response()->json(['token_invalid'], $e->getStatusCode());

        } catch (JWTException $e) {

            return response()->json(['token_absent'], $e->getStatusCode());

        }

        // the token is valid and we have found the user via the sub claim
        return ResponseService::userArray($user);
    }
}