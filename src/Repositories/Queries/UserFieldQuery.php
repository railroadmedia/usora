<?php

namespace Railroad\Usora\Repositories\Queries;

use Railroad\Resora\Queries\CachedQuery;
use Railroad\Usora\Events\UserEvent;

class UserFieldQuery extends CachedQuery
{
    /**
     * Insert a new record and get the value of the primary key.
     *
     * @param  array   $values
     * @param  string|null  $sequence
     * @return int
     */
    public function insertGetId(array $values, $sequence = null)
    {
        $id = parent::insertGetId($values, $sequence);

        event(new UserEvent($id, 'updated'));

        return $id;
    }

    public function update(array $values)
    {
        $queryClone = $this->cloneWithout([]);

        $idsToBeUpdated = $queryClone->get(['user_id'])->pluck('user_id');

        $return = parent::update($values);

        foreach ($idsToBeUpdated as $idToBeUpdated) {
            event(new UserEvent($idToBeUpdated, 'updated'));
        }

        return $return;
    }

    public function delete($id = null)
    {
        $user = $this->where('id', $id)->first(['user_id']);

        $deleted = parent::delete($id);

        if ($deleted > 0) {
            event(new UserEvent($user['user_id'], 'updated'));
        }

        return $deleted;
    }
}