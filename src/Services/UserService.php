<?php

namespace Railroad\Usora\Services;

use Railroad\Usora\Repositories\UserRepository;

class UserService
{
    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * UserService constructor.
     * @param UserRepository $userRepository
     */
    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * @param $id
     * @return array
     */
    public function getById($id)
    {
        return $this->userRepository->getById($id);
    }

    /**
     * @param array $ids
     * @return array
     */
    public function getByIds(array $ids)
    {
        return $this->userRepository->getByIds($ids);
    }

    /**
     * @param array $attributes
     * @return array|null
     */
    public function getFirstBy(array $attributes)
    {
        return $this->userRepository->getFirstBy($attributes);
    }

    /**
     * @param array $credentials
     * @return array|null
     */
    public function getByCredentials(array $credentials)
    {
        return $this->userRepository->getFirstBy($credentials);
    }

    /**
     * @param array $attributes
     * @param array $values
     * @param string $getterColumn
     * @return int|null
     */
    public function updateOrCreate(array $attributes, array $values = [], $getterColumn = 'id')
    {
        return $this->userRepository->updateOrCreate($attributes, $values, $getterColumn);
    }
}