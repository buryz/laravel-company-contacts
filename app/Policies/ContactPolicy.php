<?php

namespace App\Policies;

use App\Models\Contact;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ContactPolicy
{
    /**
     * Determine whether the user can view any models.
     * All users (including guests) can view contacts list.
     */
    public function viewAny(?User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     * All users (including guests) can view individual contacts and QR codes.
     */
    public function view(?User $user, Contact $contact): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     * Only authenticated users can create contacts.
     */
    public function create(User $user): bool
    {
        return $user !== null;
    }

    /**
     * Determine whether the user can update the model.
     * Only authenticated users can update contacts.
     */
    public function update(User $user, Contact $contact): bool
    {
        return $user !== null;
    }

    /**
     * Determine whether the user can delete the model.
     * Only authenticated users can delete contacts.
     */
    public function delete(User $user, Contact $contact): bool
    {
        return $user !== null;
    }

    /**
     * Determine whether the user can export contacts.
     * Only authenticated users can export contacts.
     */
    public function export(User $user): bool
    {
        return $user !== null;
    }

    /**
     * Determine whether the user can generate QR codes.
     * All users (including guests) can generate QR codes.
     */
    public function generateQR(?User $user, Contact $contact): bool
    {
        return true;
    }

    /**
     * Determine whether the user can restore the model.
     * Only authenticated users can restore contacts.
     */
    public function restore(User $user, Contact $contact): bool
    {
        return $user !== null;
    }

    /**
     * Determine whether the user can permanently delete the model.
     * Only authenticated users can permanently delete contacts.
     */
    public function forceDelete(User $user, Contact $contact): bool
    {
        return $user !== null;
    }
}
