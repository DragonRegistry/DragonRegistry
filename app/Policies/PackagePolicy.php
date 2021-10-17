<?php

namespace App\Policies;

use App\Models\Package;
use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Support\Str;

class PackagePolicy
{
    use HandlesAuthorization;

    public function create(User $user, string $name): bool
    {
        // Package managers sometimes use %2f when passing a "/" in scoped packages, so for stability check that also
        if(Str::contains($name, ["/", "%2f"]))
            return $user->role_id >= config('permissions.role_scoped_package_create');
        return $user->role_id >= config('permissions.role_package_create');
    }

    public function update(User $user, Package $package): bool
    {
        // @TODO: Implement advanced permissions
        return $package->creator_user_id == $user->id;
    }
}
