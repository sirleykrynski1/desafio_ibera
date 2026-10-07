<?php

namespace App\Policies;

use App\Models\Establecimiento;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class EstablecimientoPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->rol, ['propietario', 'inspector', 'admin_gobierno'], true);
    }

    public function view(User $user, Establecimiento $establecimiento): Response
    {
        return $this->esGobierno($user) || ($user->rol === 'propietario' && $user->id === $establecimiento->user_id)
            ? Response::allow() : Response::denyAsNotFound();
    }

    public function create(User $user): bool
    {
        return $user->rol === 'propietario';
    }

    public function update(User $user, Establecimiento $establecimiento): Response
    {
        return $user->rol === 'propietario' && $user->id === $establecimiento->user_id
            ? Response::allow() : Response::denyAsNotFound();
    }

    public function gestionarPermiso(User $user, Establecimiento $establecimiento): bool
    {
        return $this->esGobierno($user);
    }

    private function esGobierno(User $user): bool
    {
        return in_array($user->rol, ['inspector', 'admin_gobierno'], true);
    }
}
