<?php

namespace Railroad\Usora\Controllers;

use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Password;
use Railroad\DoctrineArrayHydrator\JsonApiHydrator;
use Railroad\Usora\Entities\User;
use Railroad\Usora\Managers\UsoraEntityManager;
use Railroad\Usora\Requests\UserJsonUpdateRequest;
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

    /**
     * @var UsoraEntityManager
     */
    private $entityManager;

    /**
     * @var ObjectRepository|EntityRepository
     */
    private $userRepository;

    /**
     * @var JsonApiHydrator
     */
    private $jsonApiHydrator;

    /**
     * ApiController constructor.
     *
     * @param JWTAuth $jwtAuth
     * @param UsoraEntityManager $entityManager
     */
    public function __construct(JWTAuth $jwtAuth, UsoraEntityManager $entityManager)
    {
        $this->jwtAuth = $jwtAuth;
        $this->entityManager = $entityManager;

        $this->userRepository = $this->entityManager->getRepository(User::class);
        $this->jsonApiHydrator = new JsonApiHydrator($this->entityManager);
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
                'tokenType' => 'bearer',
                'expiresIn' => $this->jwtAuth->factory()
                        ->getTTL() * 60,
            ]
        );
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function logout(Request $request)
    {
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

            return response()->json(['token_expired'], 401);

        } catch (TokenInvalidException $e) {

            return response()->json(['token_invalid'], 401);

        } catch (JWTException $e) {

            return response()->json(['token_absent'], 401);

        }

        // the token is valid and we have found the user via the sub claim
        return ResponseService::userArray($user);
    }

    /**Send the password reset link to the user
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function forgotPassword(Request $request)
    {
        $request->validate(
            [
                'email' => 'required|email|exists:' .
                    config('usora.database_connection_name') .
                    '.' .
                    config('usora.tables.users') .
                    ',email',
            ]
        );

        $response =
            $this->broker()
                ->sendResetLink(
                    $request->only('email')
                );

        if ($response === Password::RESET_LINK_SENT) {
            return response()->json(
                [
                    'success' => true,
                    'message' => 'Password reset link has been sent to your email.',
                ]
            );
        }

        return response()->json(
            [
                'success' => false,
                'errors' => 'Failed to reset password, please double check your email or contact support.',
            ]
        );
    }

    /**
     * Get the broker to be used during password reset.
     *
     * @return PasswordBroker
     */
    public function broker()
    {
        return Password::broker();
    }

    /**
     * @param UserJsonUpdateRequest $request
     * @return JsonResponse
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \ReflectionException
     */
    public function updateUser(UserJsonUpdateRequest $request)
    {
        $user = $this->userRepository->find(auth()->id());

        $oldUser = clone($user);

        if (empty($user)) {
            return response()->json(['user_not_found'], 404);
        }

        $newAttributes = $request->onlyAllowed();

        $this->jsonApiHydrator->hydrate($user, $newAttributes);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        event(new UserUpdated($user, $oldUser));

        return ResponseService::userJson($user)
            ->respond(200);
    }
}