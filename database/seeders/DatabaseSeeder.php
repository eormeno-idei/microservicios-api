<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * Este seeder principal orquesta la ejecución de todos los seeders
     * en el orden correcto para mantener la integridad referencial.
     */
    public function run(): void
    {
        $this->command->info('🌱 Starting database seeding...');
        $this->command->newLine();

        // 1. Crear roles (necesarios para usuarios)
        $this->command->info('📋 Creating roles...');
        Role::firstOrCreate(['name' => 'admin']);
        Role::firstOrCreate(['name' => 'user']);
        $this->command->info('✅ Roles created successfully!');
        $this->command->newLine();

        // 2. Crear usuario administrador
        $this->command->info('👤 Creating admin user...');
        $admin = User::firstOrCreate(
            ['email' => env('ADMIN_EMAIL', 'admin@example.com')],
            [
                'name' => env('ADMIN_NAME', 'Admin User'),
                'first_name' => env('ADMIN_FIRST_NAME', 'Admin'),
                'last_name' => env('ADMIN_LAST_NAME', 'User'),
                'mobile' => env('ADMIN_MOBILE', '+1234567890'),
                'semantic_context' => 'Administrador del sistema con acceso completo',
                'password' => bcrypt(env('ADMIN_PASSWORD', 'password')),
                'email_verified_at' => now(),
            ]
        );
        $admin->assignRole('admin');
        $this->command->info('✅ Admin user created successfully!');
        $this->command->newLine();

        // 3. Crear usuarios regulares
        $this->command->info('👥 Creating regular users...');
        $existingUsersCount = User::role('user')->count();
        $usersToCreate = max(0, 10 - $existingUsersCount);

        if ($usersToCreate > 0) {
            User::factory($usersToCreate)->create()->each(function ($user) {
                $user->assignRole('user');
            });
            $this->command->info("✅ {$usersToCreate} regular users created successfully!");
        } else {
            $this->command->info('ℹ️  Regular users already exist, skipping creation.');
        }
        $this->command->newLine();

        // 4. Ejecutar seeders específicos en orden
        $this->command->info('🔄 Running specific seeders...');
        $this->command->newLine();

        // Channels (independiente)
        $this->command->info('📢 Seeding channels...');
        $this->call(ChannelSeeder::class);
        $this->command->newLine();

        // Medias (independiente)
        $this->command->info('📺 Seeding medias...');
        $this->call(MediaSeeder::class);
        $this->command->newLine();

        // Posts (depende de Users, Channels, Medias)
        $this->command->info('📝 Seeding posts...');
        $this->call(PostSeeder::class);
        $this->command->newLine();

        // Attachments (depende de Posts)
        $this->command->info('📎 Seeding attachments...');
        $this->call(AttachmentSeeder::class);
        $this->command->newLine();

        // User-Channel relationships (depende de Users y Channels)
        $this->command->info('🔗 Seeding user-channel relationships...');
        $this->call(UserChannelSeeder::class);
        $this->command->newLine();

        // Resumen final
        $this->command->info('═══════════════════════════════════════════');
        $this->command->info('✨ Database seeding completed successfully!');
        $this->command->info('═══════════════════════════════════════════');
        $this->command->newLine();

        // Estadísticas
        $this->command->table(
            ['Model', 'Count'],
            [
                ['Users', User::count()],
                ['Roles', Role::count()],
                ['Channels', \App\Models\Channel::count()],
                ['Medias', \App\Models\Media::count()],
                ['Posts', \App\Models\Post::count()],
                ['Attachments', \App\Models\Attachment::count()],
            ]
        );
    }
}
