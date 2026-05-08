<?php

namespace Modules\Core\Services;

use Illuminate\Support\Facades\Gate;

class Service
{
    /**
     * @var int $parentResourceId
     */
    protected int $parentResourceId;

    /**
     * Setter untuk resource parent id.
     *
     * @param int $parentResourceId
     * @return void
     */
    public function setParentResourceId($parentResourceId): void
    {
        if (!filter_var($parentResourceId, FILTER_VALIDATE_INT)) {
            abort(404);
        }

        $this->parentResourceId = $parentResourceId;
    }

    /**
     * Determine if the given ability should be granted for the current user.
     *
     * @param  string  $ability
     * @param  array|mixed  $arguments
     * @return \Illuminate\Auth\Access\Response
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    protected function authorize($ability, $arguments = [])
    {
        Gate::forUser(auth()->user())->authorize($ability, $arguments);
    }
}
