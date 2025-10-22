<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
       $this->call([
           RoleSeeder::class,
           UserSeeder::class,
           ChannelSeeder::class,
       ]);
    }
}

