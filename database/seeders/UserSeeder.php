<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (User::count() > 10) {
            return;
        }

        $admin = User::firstOrCreate(
            ['email' => env('ADMIN_EMAIL')],
            [
                'name' => env('ADMIN_NAME'),
                'first_name' => env('ADMIN_FIRST_NAME'),
                'last_name' => env('ADMIN_LAST_NAME'),
                'password' => bcrypt(env('ADMIN_PASSWORD'))
            ]
        );

        $admin->assignRole('admin');

        User::factory(10)->create()->each(function ($user) {
            $user->assignRole('user');
        });

        $this->command->info('Usuarios sembrados correctamente.');
    }
}
