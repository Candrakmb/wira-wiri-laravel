<?php

namespace Database\Seeders;

use App\Models\Driver;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Faker\Factory as Faker;
use Illuminate\Support\Facades\Hash;

class DriverSeeder extends Seeder
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

        $faker = Faker::create();

        for ($i = 0; $i < 30; $i++) {
            // Create a new user
            $user = User::create([
                'name' =>  fake('id_ID')->firstName() . ' ' . fake('id_ID')->lastName(), // Nama sesuai format Indonesia
                'email' => fake()->unique()->safeEmail(),  // Email yang valid
                'password' => Hash::make('12345678') // Hashing password lebih aman
            ]);

            // Assign role 'driver'
            $user->assignRole('driver');

            // Create a new driver associated with the user
            Driver::create([
                'user_id' => $user->id,
                'no_whatsapp' => '+6282230313377',
                'tanggal_lahir' => fake()->date('Y-m-d'),
                'alamat' => fake('id_ID')->address(),
                'jenis_kelamin' => $faker->randomElement(['Laki-laki', 'Perempuan']),
                'no_plat' => strtoupper($faker->bothify('?? #### ??')),
                'status' => '0'
            ]);
        }

            DB::commit();
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollback();
        }

    }
}
