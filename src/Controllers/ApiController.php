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

/**
 * Class ApiController
 *
 * @package Railroad\Usora\Controllers
 * @group APP endpoints
 */
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
     * User login
     *
     * @param Request $request
     *
     * @permission Without restrictions
     * @bodyParam email string required Example:email@email.ro
     * @bodyParam password string required Example: password
     * @response {
     * "success": true,
     * "token":
     *     "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwczpcL1wvZGV2LmRydW1lby5jb21cL2xhcmF2ZWxcL3B1YmxpY1wvYXBpXC9sb2dpbiIsImlhdCI6MTU2NTcwNDczNiwiZXhwIjoxNTY1NzA4MzM2LCJuYmYiOjE1NjU3MDQ3MzYsImp0aSI6Im8yMWJFaVU3WUcyS3VCa0wiLCJzdWIiOjE0OTYyOCwicHJ2IjoiOWY4YTIzODlhMjBjYTA3NTJhYTllOTUwOTM1MTU1MTdlOTBlMTk0YyJ9.ayJrvjNMrfDg78Aedglp6sEEoz6jzMLbHl7Gcy6Cygg",
     * "isEdge": true,
     * "isEdgeExpired": false,
     * "edgeExpirationDate": null,
     * "isPackOwner": true,
     * "tokenType": "bearer",
     * "expiresIn": 3600,
     * "userId": 149628
     * }
     *
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
     * Logout the authenticated user and invalidate the jwt token
     *
     * @permission Only authenticated user
     * @param Request $request
     * @return JsonResponse
     *
     * @response {
     * "success": true,
     * "message": "Successfully logged out"
     * }
     *
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

    /** Get authenticated user
     *
     * @permission Only authenticated user
     *
     * @param Request $request
     * @return Fractal
     * @throws JWTException
     *
     * @response {
     * "id": 149628,
     * "wordpressId": 152167,
     * "ipbId": 150228,
     * "email": "roxana.riza@artsoft-consult.ro",
     * "permission_level": "administrator",
     * "login_username": "roxana.riza@artsoft-consult.ro",
     * "display_name": "Roxana",
     * "first_name": "Roxana",
     * "last_name": "",
     * "gender": "",
     * "country": "Romania",
     * "region": "",
     * "city": "",
     * "birthday": "2017-07-04 00:00:00",
     * "phone_number": "",
     * "bio": "",
     * "created_at": "2017-07-31 22:54:41",
     * "updated_at": "2019-08-01 07:05:50",
     * "avatarUrl": "https:\/\/drumeo-profile-images.s3.us-west-2.amazonaws.com\/149628_avatar_url_1563362703.jpeg",
     * "totalXp": 54280,
     * "xpRank": "Master II"
     * }
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

    /**
     * Send the password reset link to the user
     *
     * @permission Without restrictions
     * @bodyParam email string required Example:email@email.ro
     * @response {
     * "success": true,
     * "title": "Please check your email",
     * "message": "Follow the instructions sent to your email address to reset your password."
     * }
     *
     * @param Request $request
     * @return JsonResponse
     *
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
     *
     * @permission Only authenticated user
     * @response
     * {
     * "id": 149628,
     * "wordpressId": 152167,
     * "ipbId": 150228,
     * "email": "roxana.riza@artsoft-consult.ro",
     * "permission_level": "administrator",
     * "login_username": "roxana.riza@artsoft-consult.ro",
     * "display_name": "Roxana",
     * "first_name": "Roxana",
     * "last_name": "",
     * "gender": "",
     * "country": "Romania",
     * "region": "",
     * "city": "",
     * "birthday": "2017-07-04 00:00:00",
     * "phone_number": "",
     * "bio": "",
     * "created_at": "2017-07-31 22:54:41",
     * "updated_at": "2019-08-01 07:05:50",
     * "avatarUrl": "https:\/\/drumeo-profile-images.s3.us-west-2.amazonaws.com\/149628_avatar_url_1563362703.jpeg",
     * "totalXp": 54280,
     * "xpRank": "Master II"
     * }
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

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function isEmailUnique(Request $request)
    {
        $validator = validator(
            $request->all(),
            [
                'email' => 'required|email',
            ]
        );

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->getMessageBag()], 422);
        }

        $user = $this->userRepository->findOneBy(['email' => $request->get('email')]);

        if ($user) {
            return response()->json(['exists' => true]);
        }

        return response()->json(['exists' => false]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function isDisplayNameUnique(Request $request)
    {
        $validator = validator(
            $request->all(),
            [
                'display_name' => 'required',
            ]
        );

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->getMessageBag()], 422);
        }

        $qb = $this->userRepository->createQueryBuilder('u')->where('u.displayName = :name')->setParameter('name', $request->get('display_name'));

        if(auth()->id()){
            $qb->andWhere('u.id != :id')->setParameter('id', auth()->id());
        }

        $user = $qb->getQuery()->getOneOrNullResult();

        if ($user) {
            return response()->json(['unique' => false]);
        }

        return response()->json(['unique' => true]);
    }
}
