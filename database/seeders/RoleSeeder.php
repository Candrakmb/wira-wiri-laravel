<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::beginTransaction();
        
        try {
        
        Role::create(['name' => 'user']);
        Role::create(['name' => 'driver']);
        Role::create(['name' => 'kedai']);
        Role::create(['name' => 'admin']);

        $user = User::create([   
            'name' => 'Admin',
            'email' => 'admin@gmail.com',
            'password' => bcrypt('admin123')
        ]);
        $user->assignRole('admin');

            DB::commit();
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollback();
        }

    }
}
