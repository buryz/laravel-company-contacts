<?php

namespace App\Policies;

use App\Models\Tag;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TagPolicy
{
    /**
     * Determine whether the user can view any models.
     * Only authenticated users can view tags management.
     */
    public function viewAny(User $user): bool
    {
        return $user !== null;
    }

    /**
     * Determine whether the user can view the model.
     * Only authenticated users can view individual tags.
     */
    public function view(User $user, Tag $tag): bool
    {
        return $user !== null;
    }

    /**
     * Determine whether the user can create models.
     * Only authenticated users can create tags.
     */
    public function create(User $user): bool
    {
        return $user !== null;
    }

    /**
     * Determine whether the user can update the model.
     * Only authenticated users can update tags.
     */
    public function update(User $user, Tag $tag): bool
    {
        return $user !== null;
    }

    /**
     * Determine whether the user can delete the model.
     * Only authenticated users can delete tags.
     */
    public function delete(User $user, Tag $tag): bool
    {
        return $user !== null;
    }

    /**
     * Determine whether the user can access tags API.
     * Only authenticated users can access tags API.
     */
    public function api(User $user): bool
    {
        return $user !== null;
    }

    /**
     * Determine whether the user can restore the model.
     * Only authenticated users can restore tags.
     */
    public function restore(User $user, Tag $tag): bool
    {
        return $user !== null;
    }

    /**
     * Determine whether the user can permanently delete the model.
     * Only authenticated users can permanently delete tags.
     */
    public function forceDelete(User $user, Tag $tag): bool
    {
        return $user !== null;
    }
}
