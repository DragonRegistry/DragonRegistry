<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RolesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $roles = Role::query()->pluck('id')->all();
        foreach ([
                     Role::ROLE_NORMAL_ID   => 'normal',
                     Role::ROLE_VERIFIED_ID => 'verified',
                     Role::ROLE_ADMIN_ID    => 'admin',
                 ] as $id => $name)
            if (!$roles->get($id))
                Role::query()->create([
                    'id'    => $id,
                    'name'  => $name
                ]);
    }
}
