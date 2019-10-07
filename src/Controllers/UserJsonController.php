<?php

namespace Railroad\Usora\Controllers;

use Doctrine\DBAL\DBALException;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Illuminate\Contracts\Hashing\Hasher;
use Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Railroad\DoctrineArrayHydrator\JsonApiHydrator;
use Railroad\Permissions\Services\PermissionService;
use Railroad\Usora\Entities\User;
use Railroad\Usora\Events\User\UserCreated;
use Railroad\Usora\Events\User\UserDeleted;
use Railroad\Usora\Events\User\UserUpdated;
use Railroad\Usora\Managers\UsoraEntityManager;
use Railroad\Usora\Repositories\UserRepository;
use Railroad\Usora\Requests\UserJsonCreateRequest;
use Railroad\Usora\Requests\UserJsonUpdateRequest;
use Railroad\Usora\Services\ResponseService;
use ReflectionException;
use Spatie\Fractal\Fractal;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class UserJsonController
 *
 * @package Railroad\Usora\Controllers
 * @group Users-JSON-endpoints
 */
class UserJsonController extends Controller
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * @var PermissionService
     */
    private $permissionService;

    /**
     * @var Hasher
     */
    private $hasher;
    /**
     * @var JsonApiHydrator
     */
    private $jsonApiHydrator;

    /**
     * UserController constructor.
     *
     * @param EntityManager $entityManager
     * @param PermissionService $permissionService
     * @param Hasher $hasher
     * @param JsonApiHydrator $jsonApiHydrator
     */
    public function __construct(
        UsoraEntityManager $entityManager,
        PermissionService $permissionService,
        Hasher $hasher
    ) {
        $this->entityManager = $entityManager;
        $this->permissionService = $permissionService;
        $this->hasher = $hasher;
        $this->jsonApiHydrator = new JsonApiHydrator($this->entityManager);

        $this->userRepository = $this->entityManager->getRepository(User::class);

        $this->middleware([ConvertEmptyStringsToNull::class]);
    }

    /**
     * Pull users
     *
     * @permission Must be logged in
     * @permission Only users with index-users ability
     * @bodyParam search_term string
     * @bodyParam per_page integer Default:25 Example:2
     * @bodyParam page integer Default:1 Example:1
     * @bodyParam sort string Default:createdAt Example:createdAt
     * @param Request $request
     * @return Fractal
     * @response {
     * "data": [
     * {
     * "type": "user",
     * "id": "154105",
     * "attributes": {
     * "email": "03elijah.brown@gmail.com",
     * "display_name": "03elijah.brown8837",
     * "created_at": "2019-04-30 18:41:13",
     * "updated_at": "2019-05-23 15:56:21",
     * "first_name": null,
     * "last_name": null,
     * "gender": "",
     * "country": null,
     * "region": null,
     * "city": null,
     * "birthday": null,
     * "phone_number": "",
     * "biography": null,
     * "profile_picture_url": "",
     * "timezone": "",
     * "permission_level": null,
     * "drums_playing_since_year": null,
     * "drums_gear_photo": "",
     * "drums_gear_cymbal_brands": null,
     * "drums_gear_set_brands": null,
     * "drums_gear_hardware_brands": null,
     * "drums_gear_stick_brands": null,
     * "guitar_playing_since_year": null,
     * "guitar_gear_photo": "",
     * "guitar_gear_guitar_brands": null,
     * "guitar_gear_amp_brands": null,
     * "guitar_gear_pedal_brands": null,
     * "guitar_gear_string_brands": null,
     * "piano_playing_since_year": null,
     * "piano_gear_photo": "",
     * "piano_gear_piano_brands": null,
     * "piano_gear_keyboard_brands": null,
     * "notify_on_lesson_comment_reply": true,
     * "notify_weekly_update": true,
     * "notify_on_forum_post_like": true,
     * "notify_on_forum_followed_thread_reply": true,
     * "notify_on_forum_post_reply": true,
     * "notify_on_lesson_comment_like": true,
     * "notifications_summary_frequency_minutes": null
     * "support_note": 'Text'
     * }
     * },
     * {
     * "type": "user",
     * "id": "151248",
     * "attributes": {
     * "email": "08borda25@gmail.com",
     * "display_name": "borda91",
     * "created_at": "2017-05-27 23:46:12",
     * "updated_at": "2019-04-01 00:41:14",
     * "first_name": null,
     * "last_name": null,
     * "gender": "",
     * "country": null,
     * "region": null,
     * "city": null,
     * "birthday": null,
     * "phone_number": "",
     * "biography": null,
     * "profile_picture_url": "",
     * "timezone": "",
     * "permission_level": null,
     * "drums_playing_since_year": null,
     * "drums_gear_photo": "",
     * "drums_gear_cymbal_brands": null,
     * "drums_gear_set_brands": null,
     * "drums_gear_hardware_brands": null,
     * "drums_gear_stick_brands": null,
     * "guitar_playing_since_year": null,
     * "guitar_gear_photo": "",
     * "guitar_gear_guitar_brands": null,
     * "guitar_gear_amp_brands": null,
     * "guitar_gear_pedal_brands": null,
     * "guitar_gear_string_brands": null,
     * "piano_playing_since_year": null,
     * "piano_gear_photo": "",
     * "piano_gear_piano_brands": null,
     * "piano_gear_keyboard_brands": null,
     * "notify_on_lesson_comment_reply": true,
     * "notify_weekly_update": true,
     * "notify_on_forum_post_like": true,
     * "notify_on_forum_followed_thread_reply": true,
     * "notify_on_forum_post_reply": true,
     * "notify_on_lesson_comment_like": true,
     * "notifications_summary_frequency_minutes": null
     * "support_note": 'Text'
     * }
     * }
     * ],
     * "meta": {
     * "pagination": {
     * "total": 5825,
     * "count": 2,
     * "per_page": 2,
     * "current_page": 1,
     * "total_pages": 2913
     * }
     * },
     * "links": {
     * "self": "https:\/\/dev.musora.com\/usora\/json-api\/user\/index?limit=2&page=1&sort=email&per_page=2",
     * "first": "https:\/\/dev.musora.com\/usora\/json-api\/user\/index?limit=2&page=1&sort=email&per_page=2",
     * "next": "https:\/\/dev.musora.com\/usora\/json-api\/user\/index?limit=2&page=2&sort=email&per_page=2",
     * "last": "https:\/\/dev.musora.com\/usora\/json-api\/user\/index?limit=2&page=2913&sort=email&per_page=2"
     * }
     * }
     */
    public function index(Request $request)
    {
        if (!$this->permissionService->can(auth()->id(), 'index-users')) {
            throw new NotFoundHttpException();
        }

        $queryBuilder = $this->userRepository->createQueryBuilder('user');

        if (!empty($request->get('search_term'))) {
            $queryBuilder->where(
                $queryBuilder->expr()
                    ->orX(
                        $queryBuilder->expr()
                            ->like('user.email', ':term'),
                        $queryBuilder->expr()
                            ->like('user.displayName', ':term'),
                        $queryBuilder->expr()
                            ->like('user.firstName', ':term'),
                        $queryBuilder->expr()
                            ->like('user.lastName', ':term'),
                        $queryBuilder->expr()
                            ->like('user.phoneNumber', ':term')
                    )
            )
                ->setParameter('term', '%' . $request->get('search_term') . '%');
        }

        $queryBuilder->setMaxResults($request->get('per_page', 25))
            ->setFirstResult(($request->get('page', 1) - 1) * $request->get('per_page', 25))
            ->orderBy(
                'user.' . trim($request->get('sort', 'createdAt'), '-'),
                substr($request->get('sort', 'createdAt'), 0, 1) === '-' ? 'desc' : 'asc'
            );

        $users =
            $queryBuilder->getQuery()
                ->getResult();

        return ResponseService::userJson($users, $queryBuilder);
    }

    /**
     * Pull user
     *
     * @queryParam id required Example:151248
     *
     * @permission Must be logged in
     * @permission Only user with show-users ability
     * @response {
     * "data": {
     * "type": "user",
     * "id": "151248",
     * "attributes": {
     * "email": "08borda25@gmail.com",
     * "display_name": "borda91",
     * "created_at": "2017-05-27 23:46:12",
     * "updated_at": "2019-04-01 00:41:14",
     * "first_name": null,
     * "last_name": null,
     * "gender": "",
     * "country": null,
     * "region": null,
     * "city": null,
     * "birthday": null,
     * "phone_number": "",
     * "biography": null,
     * "profile_picture_url": "",
     * "timezone": "",
     * "permission_level": null,
     * "drums_playing_since_year": null,
     * "drums_gear_photo": "",
     * "drums_gear_cymbal_brands": null,
     * "drums_gear_set_brands": null,
     * "drums_gear_hardware_brands": null,
     * "drums_gear_stick_brands": null,
     * "guitar_playing_since_year": null,
     * "guitar_gear_photo": "",
     * "guitar_gear_guitar_brands": null,
     * "guitar_gear_amp_brands": null,
     * "guitar_gear_pedal_brands": null,
     * "guitar_gear_string_brands": null,
     * "piano_playing_since_year": null,
     * "piano_gear_photo": "",
     * "piano_gear_piano_brands": null,
     * "piano_gear_keyboard_brands": null,
     * "notify_on_lesson_comment_reply": true,
     * "notify_weekly_update": true,
     * "notify_on_forum_post_like": true,
     * "notify_on_forum_followed_thread_reply": true,
     * "notify_on_forum_post_reply": true,
     * "notify_on_lesson_comment_like": true,
     * "notifications_summary_frequency_minutes": null,
     * "support_note": 'Text'
     * }
     * }
     * }
     * @param integer $id
     * @return Fractal
     */
    public function show($id)
    {
        if (!$this->permissionService->can(auth()->id(), 'show-users')) {
            throw new NotFoundHttpException();
        }

        $user = $this->userRepository->find($id);

        return ResponseService::userJson($user);
    }

    /** Create new user
     *
     * @param UserJsonCreateRequest $request
     * @return JsonResponse
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws DBALException
     * @throws ReflectionException
     *
     * @permission Must be logged in
     * @permission Only user with create-users ability
     * @response {
     * "data": {
     * "type": "user",
     * "id": "151248",
     * "attributes": {
     * "email": "08borda25@gmail.com",
     * "display_name": "borda91",
     * "created_at": "2017-05-27 23:46:12",
     * "updated_at": "2019-04-01 00:41:14",
     * "first_name": null,
     * "last_name": null,
     * "gender": "",
     * "country": null,
     * "region": null,
     * "city": null,
     * "birthday": null,
     * "phone_number": "",
     * "biography": null,
     * "profile_picture_url": "",
     * "timezone": "",
     * "permission_level": null,
     * "drums_playing_since_year": null,
     * "drums_gear_photo": "",
     * "drums_gear_cymbal_brands": null,
     * "drums_gear_set_brands": null,
     * "drums_gear_hardware_brands": null,
     * "drums_gear_stick_brands": null,
     * "guitar_playing_since_year": null,
     * "guitar_gear_photo": "",
     * "guitar_gear_guitar_brands": null,
     * "guitar_gear_amp_brands": null,
     * "guitar_gear_pedal_brands": null,
     * "guitar_gear_string_brands": null,
     * "piano_playing_since_year": null,
     * "piano_gear_photo": "",
     * "piano_gear_piano_brands": null,
     * "piano_gear_keyboard_brands": null,
     * "notify_on_lesson_comment_reply": true,
     * "notify_weekly_update": true,
     * "notify_on_forum_post_like": true,
     * "notify_on_forum_followed_thread_reply": true,
     * "notify_on_forum_post_reply": true,
     * "notify_on_lesson_comment_like": true,
     * "notifications_summary_frequency_minutes": null,
     * "support_note": 'Text'
     * }
     * }
     * }
     */
    public function store(UserJsonCreateRequest $request)
    {
        if (!$this->permissionService->can(auth()->id(), 'create-users')) {
            throw new NotFoundHttpException();
        }

        $user = new User();

        $this->jsonApiHydrator->hydrate($user, $request->onlyAllowed());

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        event(new UserCreated($user));

        return ResponseService::userJson($user)
            ->respond(201);
    }

    /**  Update an existing user.
     *
     * @param UserJsonUpdateRequest $request
     * @param integer $id
     * @return JsonResponse
     * @throws DBALException
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws ReflectionException
     *
     * @permission Must be logged in
     * @permission Must have the update-users permission to update
     *
     * @queryParam user_id required Example:1
     *
     * @response {
     * "data": {
     * "type": "user",
     * "id": "151248",
     * "attributes": {
     * "email": "08borda25@gmail.com",
     * "display_name": "borda91",
     * "created_at": "2017-05-27 23:46:12",
     * "updated_at": "2019-04-01 00:41:14",
     * "first_name": null,
     * "last_name": null,
     * "gender": "",
     * "country": null,
     * "region": null,
     * "city": null,
     * "birthday": null,
     * "phone_number": "",
     * "biography": null,
     * "profile_picture_url": "",
     * "timezone": "",
     * "permission_level": null,
     * "drums_playing_since_year": null,
     * "drums_gear_photo": "",
     * "drums_gear_cymbal_brands": null,
     * "drums_gear_set_brands": null,
     * "drums_gear_hardware_brands": null,
     * "drums_gear_stick_brands": null,
     * "guitar_playing_since_year": null,
     * "guitar_gear_photo": "",
     * "guitar_gear_guitar_brands": null,
     * "guitar_gear_amp_brands": null,
     * "guitar_gear_pedal_brands": null,
     * "guitar_gear_string_brands": null,
     * "piano_playing_since_year": null,
     * "piano_gear_photo": "",
     * "piano_gear_piano_brands": null,
     * "piano_gear_keyboard_brands": null,
     * "notify_on_lesson_comment_reply": true,
     * "notify_weekly_update": true,
     * "notify_on_forum_post_like": true,
     * "notify_on_forum_followed_thread_reply": true,
     * "notify_on_forum_post_reply": true,
     * "notify_on_lesson_comment_like": true,
     * "notifications_summary_frequency_minutes": null,
     * "support_note": null
     * }
     * }
     * }
     */
    public function update(UserJsonUpdateRequest $request, $id)
    {
        if (!$this->permissionService->can(auth()->id(), 'update-users') && auth()->id() != $id) {
            throw new NotFoundHttpException();
        }

        $user = $this->userRepository->find($id);
        $oldUser = clone($user);

        if (empty($user)) {
            throw new NotFoundHttpException();
        }

        $newAttributes = $request->onlyAllowed();

        $this->jsonApiHydrator->hydrate($user, $newAttributes);

        // regular users are not allowed to change their emails here
        if ($this->permissionService->can(auth()->id(), 'update-users-email-without-confirmation') &&
            !empty($request->input('data.attributes.email'))) {

            $user->setEmail($request->input('data.attributes.email'));
        }

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        event(new UserUpdated($user, $oldUser));

        return ResponseService::userJson($user)
            ->respond(200);
    }

    /** Delete an user
     *
     * @param integer $id
     * @return JsonResponse
     * @throws ORMException
     * @throws OptimisticLockException
     *
     * @permission Must be logged in
     * @permission Must have the delete-users permission to delete
     *
     * @queryParam user_id required Example:1
     */
    public function delete($id)
    {
        if (!$this->permissionService->can(auth()->id(), 'delete-users')) {
            throw new NotFoundHttpException();
        }

        $user = $this->userRepository->find($id);

        if (!is_null($user)) {
            $this->entityManager->remove($user);
            $this->entityManager->flush();

            event(new UserDeleted($user));

            return ResponseService::empty(204);
        }

        return ResponseService::empty(404);
    }
}