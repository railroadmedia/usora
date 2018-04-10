<?php

namespace Railroad\Usora\Entities;

use ArrayAccess;
use Illuminate\Contracts\Auth\Authenticatable;

class User implements Authenticatable, ArrayAccess
{
    private $data = [];

    /**
     * @return string
     */
    public function getAuthIdentifierName()
    {
        return 'id';
    }

    /**
     * @return mixed|null
     */
    public function getAuthIdentifier()
    {
        return $this[$this->getAuthIdentifierName()];
    }

    /**
     * @return mixed|null|string
     */
    public function getAuthPassword()
    {
        return $this['password'];
    }

    /**
     * @return mixed|null|string
     */
    public function getRememberToken()
    {
        return $this[$this->getRememberTokenName()];
    }

    /**
     * @param string $value
     */
    public function setRememberToken($value)
    {
        $this['remember_token'] = $value;
    }

    /**
     * @return string
     */
    public function getRememberTokenName()
    {
        return 'remember_token';
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->data[] = $value;
        } else {
            $this->data[$offset] = $value;
        }
    }

    /**
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return isset($this->data[$offset]);
    }

    /**
     * @param mixed $offset
     */
    public function offsetUnset($offset)
    {
        unset($this->data[$offset]);
    }

    /**
     * @param mixed $offset
     * @return mixed|null
     */
    public function offsetGet($offset)
    {
        return isset($this->data[$offset]) ? $this->data[$offset] : null;
    }
}