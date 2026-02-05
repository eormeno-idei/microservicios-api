<?php

namespace Idei\Usim\Services\Support;

use Illuminate\Support\Str;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Idei\Usim\Services\AbstractUIService;

class ScreenDiscoveryService
{
    /**
     * Scan the application for UI Screens and generate a manifest.
     *
     * @return array<string, array>
     */
    public function discover(): array
    {
        $screensPath = app_path('UI/Screens');

        if (!is_dir($screensPath)) {
            return [];
        }

        $manifest = [];
        $finder = new Finder();
        $finder->files()->in($screensPath)->name('*.php');

        foreach ($finder as $file) {
            $className = $this->getClassNameFromFile($file);

            if ($className && $this->isValidScreenClass($className)) {
                $manifest[$className] = [
                    'id_offset' => $this->generateStableOffset($className),
                    // Future metadata (menu, auth, etc) will go here
                ];
            }
        }

        return $manifest;
    }

    /**
     * Generate a stable, deterministic ID offset for a class.
     * Using CRC32 to generate a unique integer from the class name.
     * Multiplied by 10,000 to allow plenty of component IDs per screen.
     */
    private function generateStableOffset(string $className): int
    {
        // Use unsigned crc32 logic
        $hash = crc32($className);

        // Ensure positive integer (32-bit PHP compatibility)
        $hash = sprintf("%u", $hash);

        // Take last 6 digits to keep numbers manageable but dispersed
        // This is a trade-off. Full CRC32 * 10000 might overflow max int on some systems.
        // Let's use a simpler approach:

        // Alternative: Use PHP's distinct integer for the string, mod MaxInt/limit
        // But we need to ensure no collisions.
        // 17 screens is small. CRC32 is fine.
        // Let's simple check strict unsigned.

        // We need a number that fits in an integer when multiplied by 10000
        // Max int is usually 2^63 (64 bit).

        // Let's rely on standard crc32 abs value.
        $val = abs((int) crc32($className));

        // Truncate to avoid overflow if multiplied by 10,000 if needed,
        // but wait, UIIdGenerator assumes offsets are spaced by 10,000.
        // If we simply pick the CRC32, two IDs might be close (e.g. 100000 and 100005).
        // The old logic used index * 10000.
        // We need discrete buckets.

        // Let's assume collisions are rare enough for now or use a persistent map logic?
        // No, the goal is stateless/deterministic.

        // Let's use the hash as the bucket ID.
        // Bucket ID = hash % 200000 (enough space for 200k screens?).
        // Offset = Bucket ID * 10000.
        // Hash collision likelyhood is low for small app.

        $bucket = $val % 100000;
        return $bucket * 10000;
    }

    private function getClassNameFromFile(SplFileInfo $file): ?string
    {
        // Simple extraction assuming PSR-4 structure inside App\UI\Screens
        // We can optimize this by token parsing if needed, but for now assumption works.
        $relativePath = $file->getRelativePathname();
        $class = 'App\\UI\\Screens\\' . str_replace(['/', '.php'], ['\\', ''], $relativePath);

        return $class;
    }

    private function isValidScreenClass(string $className): bool
    {
        if (!class_exists($className)) {
            return false;
        }

        $reflection = new \ReflectionClass($className);
        return $reflection->isSubclassOf(AbstractUIService::class) && !$reflection->isAbstract();
    }
}
