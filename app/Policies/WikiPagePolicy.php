<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WikiPage;
use Illuminate\Auth\Access\HandlesAuthorization;

class WikiPagePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny(User $user)
    {
        return $user->can('List wiki pages');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\WikiPage  $wikiPage
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, WikiPage $wikiPage)
    {
        return $user->can('View wiki page');
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        return $user->can('Create wiki page');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\WikiPage  $wikiPage
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, WikiPage $wikiPage)
    {
        return $user->can('Update wiki page');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\WikiPage  $wikiPage
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, WikiPage $wikiPage)
    {
        return $user->can('Delete wiki page');
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\WikiPage  $wikiPage
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, WikiPage $wikiPage)
    {
        return $user->can('Update wiki page');
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\WikiPage  $wikiPage
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, WikiPage $wikiPage)
    {
        return $user->can('Delete wiki page');
    }
}
