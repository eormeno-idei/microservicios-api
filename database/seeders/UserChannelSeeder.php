<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Channel;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserChannelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Este seeder asigna usuarios a canales para establecer
     * las relaciones de pertenencia/responsabilidad.
     */
    public function run(): void
    {
        $users = User::all();
        $channels = Channel::all();

        if ($users->isEmpty() || $channels->isEmpty()) {
            $this->command->warn('No users or channels found. Please run previous seeders first.');
            return;
        }

        // Limpiar la tabla si ya tiene datos (opcional)
        // DB::table('user_channels')->truncate();

        // Asignar el admin a todos los canales
        $adminUser = User::role('admin')->first();
        if ($adminUser) {
            foreach ($channels as $channel) {
                DB::table('user_channels')->insertOrIgnore([
                    'user_id' => $adminUser->id,
                    'channel_id' => $channel->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            $this->command->info("Admin user assigned to all channels.");
        }

        // Asignar usuarios regulares a canales aleatorios (2-4 canales por usuario)
        $regularUsers = User::role('user')->get();
        foreach ($regularUsers as $user) {
            $randomChannels = $channels->random(rand(2, min(4, $channels->count())));

            foreach ($randomChannels as $channel) {
                DB::table('user_channels')->insertOrIgnore([
                    'user_id' => $user->id,
                    'channel_id' => $channel->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        $this->command->info('User-Channel relationships seeded successfully!');
    }
}
