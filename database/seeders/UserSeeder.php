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
        if (User::count() > 1) {
            return;
        }

        $this->createConfigUser('ADMIN', 'admin');
        $this->createConfigUser('REGISTERED', 'user');

        User::factory(7)->create()->each(function ($user) {
            $user->assignRole('user');
        });

        $this->command->info('Usuarios sembrados correctamente.');
    }

    private function createConfigUser(string $prefix, string $role)
    {
        $firstName = env("{$prefix}_FIRST_NAME", 'Usuario');
        $lastName =  env("{$prefix}_LAST_NAME", $role);
        $fullName = trim($firstName . ' ' . $lastName);
        $email = env("{$prefix}_EMAIL");
        $password = env("{$prefix}_PASSWORD");

        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => $fullName,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'password' => bcrypt($password)
            ]
        );

        $user->assignRole($role);
    }
}
