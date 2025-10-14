<?php
// app/Policies/PreinscriptionPolicy.php

namespace App\Policies;

use App\Models\Preinscription;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PreinscriptionPolicy
{
    use HandlesAuthorization;

    public function view(User $user, Preinscription $preinscription)
    {
        // Admin peut tout voir, agent seulement ses préinscriptions
        return $user->isAdmin() || $preinscription->agent_id === $user->id;
    }

    public function update(User $user, Preinscription $preinscription)
    {
        // Admin peut tout modifier, agent seulement ses préinscriptions
        return $user->isAdmin() || $preinscription->agent_id === $user->id;
    }

    public function delete(User $user, Preinscription $preinscription)
    {
        // Seul l'admin peut supprimer
        return $user->isAdmin();
    }
}