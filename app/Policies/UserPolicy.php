<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny(User $user)
    {
        // Super-admin, admin, and manager can view users
        return $user->hasRole(['super-admin', 'admin', 'manager']);
    }

    /**
     * Determine whether the user can view the model.
     *
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, User $model)
    {
        // Super-admin can view any user
        if ($user->hasRole('super-admin')) {
            return true;
        }

        // Admin and manager can view users in their tenant
        if ($user->hasRole(['admin', 'manager']) && $user->tenant_id === $model->tenant_id) {
            return true;
        }

        // Users can view their own profile
        return $user->id === $model->id;
    }

    /**
     * Determine whether the user can create models.
     *
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        // Only super-admin and admin can create users
        return $user->hasRole(['super-admin', 'admin']);
    }

    /**
     * Determine whether the user can update the model.
     *
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, User $model)
    {
        // Super-admin can update any user
        if ($user->hasRole('super-admin')) {
            return true;
        }

        // Admin can update users in their tenant except other admins
        if ($user->hasRole('admin') && $user->tenant_id === $model->tenant_id) {
            return ! $model->hasRole(['super-admin', 'admin']) || $user->id === $model->id;
        }

        // Users can update their own profile
        return $user->id === $model->id;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, User $model)
    {
        // Super-admin can delete any user
        if ($user->hasRole('super-admin')) {
            return true;
        }

        // Admin can delete users in their tenant except other admins or themselves
        if ($user->hasRole('admin') && $user->tenant_id === $model->tenant_id) {
            return ! $model->hasRole(['super-admin', 'admin']) && $user->id !== $model->id;
        }

        return false;
    }
}
