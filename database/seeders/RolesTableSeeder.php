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
        $roles = Role::all()->keyBy('id');
        if(!$roles->get(1))
            Role::query()->create([
                'id' => 1,
                'name' => 'normal'
            ]);
        if(!$roles->get(2))
            Role::query()->create([
                'id' => 2,
                'name' => 'verified'
            ]);
        if(!$roles->get(3))
            Role::query()->create([
                'id' => 3,
                'name' => 'admin'
            ]);
    }
}
