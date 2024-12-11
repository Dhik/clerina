<?php

namespace App\Domain\Product\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;

class ProductPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @return boolean
     */
    public function index()
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     *
     * @return boolean
     */
    public function view()
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     *
     * @return boolean
     */
    public function create()
    {
         return true;
    }

    /**
     * Determine whether the user can update the model.
     *
     * @return boolean
     */
    public function update()
    {
        return true;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @return boolean
     */
    public function delete()
    {
        return true;
    }
}
