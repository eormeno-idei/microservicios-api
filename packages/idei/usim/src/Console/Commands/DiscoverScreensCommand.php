<?php

namespace Idei\Usim\Console\Commands;

use Illuminate\Console\Command;
use Idei\Usim\Services\Support\ScreenDiscoveryService;

class DiscoverScreensCommand extends Command
{
    protected $signature = 'usim:discover';
    protected $description = 'Discover UI Screens and cache their metadata';

    public function handle(ScreenDiscoveryService $discoveryService)
    {
        $this->info('Discovering USIM Screens...');

        $screens = $discoveryService->discover();

        $count = count($screens);
        $this->info("Found {$count} screens.");

        $this->writeManifest($screens);

        $this->info('USIM manifest generated successfully!');
    }

    private function writeManifest(array $screens): void
    {
        $path = $this->getManifestPath();

        $content = "<?php\n\nreturn " . var_export($screens, true) . ";\n";

        file_put_contents($path, $content);

        // Invalidate PHP opcache for this file if applicable
        if (function_exists('opcache_invalidate')) {
            opcache_invalidate($path, true);
        }
    }

    private function getManifestPath(): string
    {
        return app()->bootstrapPath('cache/usim_screens.php');
    }
}
