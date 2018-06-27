<?php

use Illuminate\Database\Seeder;

use App\Models\Role;
use App\User;

class UserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $role_supervisor = Role::whereName('supervisor')->first();

        $supervisor = User::whereName('Yoov Super')->first();
        $supervisor->roles()->attach($role_supervisor);

    }
}
