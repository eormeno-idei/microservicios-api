<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (User::count() > 1) {
            return;
        }

        $this->createConfigUser('ADMIN', 'admin');
        $this->createConfigUser('USER', 'user');

        User::factory(107)->create()->each(function ($user) {
            $user->assignRole('user');
        });
    }

    private function createConfigUser(string $prefix, string $role)
    {
        $firstName = env("{$prefix}_FIRST_NAME", 'User');
        $lastName =  env("{$prefix}_LAST_NAME", $role);
        $fullName = trim($firstName . ' ' . $lastName);
        $email = env("{$prefix}_EMAIL");
        $password = env("{$prefix}_PASSWORD");

        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => $fullName,
                'email_verified_at' => now(),
                'remember_token' => Str::random(10),
                'password' => bcrypt($password)
            ]
        );

        $user->assignRole($role);
    }
}
