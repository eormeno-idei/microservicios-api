<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Post;
use App\Models\Channel;
use App\Models\Media;
use App\Models\Attachment;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ShowDatabaseStats extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:stats {--detailed : Show detailed statistics}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Display database statistics and seeded data overview';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('═══════════════════════════════════════════════════════');
        $this->info('           📊 DATABASE STATISTICS REPORT              ');
        $this->info('═══════════════════════════════════════════════════════');
        $this->newLine();

        // Resumen general
        $this->showGeneralStats();
        $this->newLine();

        // Posts por estado y tipo
        $this->showPostsStats();
        $this->newLine();

        // Canales y Medios
        $this->showChannelsAndMediasStats();
        $this->newLine();

        if ($this->option('detailed')) {
            $this->showDetailedStats();
        } else {
            $this->info('💡 Usa --detailed para ver estadísticas detalladas');
        }

        $this->newLine();
        $this->info('═══════════════════════════════════════════════════════');
    }

    protected function showGeneralStats()
    {
        $this->info('📋 GENERAL OVERVIEW');
        $this->line('─────────────────────────────────────────────────────');

        $stats = [
            ['Metric', 'Count', 'Details'],
            ['Users', User::count(), User::role('admin')->count() . ' admins, ' . User::role('user')->count() . ' users'],
            ['Posts', Post::count(), Post::whereNotNull('published_at')->count() . ' published'],
            ['Channels', Channel::count(), 'All types'],
            ['Medias', Media::count(), Media::where('is_active', true)->count() . ' active'],
            ['Attachments', Attachment::count(), 'Various types'],
        ];

        $this->table($stats[0], array_slice($stats, 1));
    }

    protected function showPostsStats()
    {
        $this->info('📝 POSTS BREAKDOWN');
        $this->line('─────────────────────────────────────────────────────');

        // Por estado
        $this->line('By Status:');
        $postsByStatus = Post::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->get();

        $statusData = $postsByStatus->map(function($item) {
            return [
                'Status' => $item->status->label(),
                'Count' => $item->total,
                'Percentage' => round(($item->total / Post::count()) * 100, 1) . '%'
            ];
        })->toArray();

        $this->table(['Status', 'Count', 'Percentage'], $statusData);

        $this->newLine();

        // Por tipo
        $this->line('By Type:');
        $postsByType = Post::select('type', DB::raw('count(*) as total'))
            ->groupBy('type')
            ->get();

        $typeData = $postsByType->map(function($item) {
            return [
                'Type' => $item->type->label(),
                'Count' => $item->total,
                'Percentage' => round(($item->total / Post::count()) * 100, 1) . '%'
            ];
        })->toArray();

        $this->table(['Type', 'Count', 'Percentage'], $typeData);
    }

    protected function showChannelsAndMediasStats()
    {
        $this->info('📢 CHANNELS & 📺 MEDIAS');
        $this->line('─────────────────────────────────────────────────────');

        // Canales por tipo
        $channelsByType = Channel::select('type', DB::raw('count(*) as total'))
            ->groupBy('type')
            ->get()
            ->map(function($item) {
                return [
                    'Type' => $item->type->label(),
                    'Count' => $item->total
                ];
            })->toArray();

        $this->table(['Channel Type', 'Count'], $channelsByType);
        $this->newLine();

        // Medios por tipo
        $mediasByType = Media::select('type', DB::raw('count(*) as total'))
            ->groupBy('type')
            ->get()
            ->map(function($item) {
                return [
                    'Type' => $item->type->label(),
                    'Count' => $item->total,
                    'Active' => Media::where('type', $item->type)->where('is_active', true)->count()
                ];
            })->toArray();

        $this->table(['Media Type', 'Count', 'Active'], $mediasByType);
    }

    protected function showDetailedStats()
    {
        $this->newLine();
        $this->info('🔍 DETAILED STATISTICS');
        $this->line('─────────────────────────────────────────────────────');

        // Top contributors
        $this->line('Top 5 Contributors:');
        $topUsers = User::withCount('posts')
            ->orderBy('posts_count', 'desc')
            ->limit(5)
            ->get()
            ->map(function($user) {
                return [
                    'Name' => $user->name,
                    'Posts' => $user->posts_count,
                    'Role' => $user->roles->first()->name ?? 'N/A'
                ];
            })->toArray();

        $this->table(['Name', 'Posts', 'Role'], $topUsers);
        $this->newLine();

        // Top channels
        $this->line('Top 5 Channels by Posts:');
        $topChannels = Channel::withCount('posts')
            ->orderBy('posts_count', 'desc')
            ->limit(5)
            ->get()
            ->map(function($channel) {
                return [
                    'Name' => $channel->name,
                    'Type' => $channel->type->label(),
                    'Posts' => $channel->posts_count
                ];
            })->toArray();

        $this->table(['Name', 'Type', 'Posts'], $topChannels);
        $this->newLine();

        // Relaciones stats
        $this->line('Relationships:');
        $relationStats = [
            ['Relation', 'Average', 'Total'],
            ['Channels per Post', round(DB::table('post_channels')->count() / Post::count(), 2), DB::table('post_channels')->count()],
            ['Medias per Post', round(DB::table('post_medias')->count() / Post::count(), 2), DB::table('post_medias')->count()],
            ['Attachments per Post', round(Attachment::count() / Post::count(), 2), Attachment::count()],
            ['Channels per User', round(DB::table('user_channels')->count() / User::count(), 2), DB::table('user_channels')->count()],
        ];

        $this->table($relationStats[0], array_slice($relationStats, 1));
    }
}

