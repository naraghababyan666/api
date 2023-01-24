<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $roles = ["superadmin", "moderator", "Trainer", "Training Center", "Student"];
        foreach ($roles as $role) {
            Role::firstOrCreate(
                [
                    'title' => $role,
                ],
                [
                    'title' => $role,
                ]
            );
        };
    }
}
