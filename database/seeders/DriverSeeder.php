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
                'name' => 'driver'.$i,
                'email' => 'driver'.$i.'@gmail.com',
                'password' => bcrypt('driverpassword')
            ]);

            // Assign role 'driver'
            $user->assignRole('driver');

            // Create a new driver associated with the user
            Driver::create([
                'user_id' => $user->id,
                'no_whatsapp' => '081928918291'.$i,
                'tanggal_lahir' => Carbon::now(),
                'alamat' => 'jalan basukirahmad no'.$i,
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
