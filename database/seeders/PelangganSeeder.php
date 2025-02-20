<?php

namespace Database\Seeders;

use App\Models\Pelanggan;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;
use Illuminate\Support\Facades\Hash;

class PelangganSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::beginTransaction();

        try {

        $faker = Faker::create();

        for ($i = 0; $i < 10; $i++) {
            // Create a new user
            $user = User::create([
                'name' =>  fake('id_ID')->firstName() . ' ' . fake('id_ID')->lastName(), // Nama sesuai format Indonesia
                'email' => fake()->unique()->safeEmail(),  // Email yang valid
                'password' => Hash::make('12345678') // Hashing password lebih aman
            ]);

            // Assign role 'driver'
            $user->assignRole('user');

            // Create a new driver associated with the user
            Pelanggan::create([
                'user_id' => $user->id,
                'no_whatsapp' => '+6282230313388',
            ]);
        }

            DB::commit();
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollback();
        }
    }
}
