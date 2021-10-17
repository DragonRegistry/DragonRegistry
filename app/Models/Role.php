<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    const ROLE_NORMAL_ID = 1;
    const ROLE_VERIFIED_ID = 2;
    const ROLE_ADMIN_ID = 3;

    public function users(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(User::class);
    }

    public static function Normal(): self
    {
        return self::find(self::ROLE_NORMAL_ID);
    }

    public static function Verified(): self
    {
        return self::find(self::ROLE_VERIFIED_ID);
    }

    public static function Admin(): self
    {
        return self::find(self::ROLE_ADMIN_ID);
    }
}
