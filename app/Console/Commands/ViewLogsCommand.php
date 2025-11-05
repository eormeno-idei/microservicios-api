<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ViewLogsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'logs:view 
                            {--lines=50 : Number of lines to display}
                            {--tail : Follow log file in real-time}
                            {--clear : Clear the log file}
                            {--file=laravel.log : Log file name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'View, tail or clear application logs';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $logFile = storage_path('logs/' . $this->option('file'));

        if ($this->option('clear')) {
            return $this->clearLogs($logFile);
        }

        if (!File::exists($logFile)) {
            $this->error("Log file not found: {$logFile}");
            return 1;
        }

        if ($this->option('tail')) {
            return $this->tailLogs($logFile);
        }

        return $this->viewLogs($logFile, $this->option('lines'));
    }

    /**
     * View log file
     */
    private function viewLogs(string $logFile, int $lines): int
    {
        $this->info("Showing last {$lines} lines from: {$logFile}");
        $this->newLine();

        $command = "tail -n {$lines} " . escapeshellarg($logFile);
        passthru($command);

        return 0;
    }

    /**
     * Tail log file in real-time
     */
    private function tailLogs(string $logFile): int
    {
        $this->info("Following log file: {$logFile}");
        $this->info("Press Ctrl+C to stop");
        $this->newLine();

        $command = "tail -f " . escapeshellarg($logFile);
        passthru($command);

        return 0;
    }

    /**
     * Clear log file
     */
    private function clearLogs(string $logFile): int
    {
        if (File::exists($logFile)) {
            File::put($logFile, '');
            $this->info("Log file cleared: {$logFile}");
        } else {
            $this->warn("Log file not found: {$logFile}");
        }

        return 0;
    }
}
