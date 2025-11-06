<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;

class LogViewerController extends Controller
{
    private const MAX_FILE_SIZE = 10 * 1024 * 1024; // 10MB
    private const DEFAULT_LINES = 1000;

    /**
     * Display the log viewer interface
     */
    public function index()
    {
        $logFiles = $this->getLogFiles();
        return view('logs.viewer', [
            'logFiles' => $logFiles,
            'selectedFile' => request('file', 'laravel.log')
        ]);
    }

    /**
     * Get log content via API
     */
    public function content(Request $request)
    {
        $fileName = $request->input('file', 'laravel.log');
        $lines = (int) $request->input('lines', self::DEFAULT_LINES);
        $search = $request->input('search', '');
        
        $logPath = storage_path('logs/' . basename($fileName));

        if (!File::exists($logPath)) {
            return response()->json([
                'error' => 'Log file not found'
            ], 404);
        }

        $fileSize = File::size($logPath);
        
        if ($fileSize > self::MAX_FILE_SIZE) {
            return response()->json([
                'warning' => 'File is too large. Showing last ' . $lines . ' lines.',
                'content' => $this->getLastLines($logPath, $lines, $search),
                'fileSize' => $this->formatBytes($fileSize)
            ]);
        }

        $content = File::get($logPath);
        
        if ($search) {
            $content = $this->filterContent($content, $search);
        }

        $logLines = explode("\n", $content);
        $logLines = array_slice($logLines, -$lines);
        
        return response()->json([
            'content' => implode("\n", $logLines),
            'fileSize' => $this->formatBytes($fileSize),
            'totalLines' => count(explode("\n", $content))
        ]);
    }

    /**
     * Download log file
     */
    public function download(Request $request)
    {
        $fileName = $request->input('file', 'laravel.log');
        $logPath = storage_path('logs/' . basename($fileName));

        if (!File::exists($logPath)) {
            abort(404, 'Log file not found');
        }

        return Response::download($logPath, $fileName);
    }

    /**
     * Clear log file
     */
    public function clear(Request $request)
    {
        try {
            // Accept both JSON and form data
            $fileName = $request->input('file') ?? $request->get('file', 'laravel.log');
            $logPath = storage_path('logs/' . basename($fileName));

            if (!File::exists($logPath)) {
                if ($request->input('redirect')) {
                    return redirect()->route('logs.index')->with('error', 'Log file not found');
                }
                return response()->json([
                    'success' => false,
                    'error' => 'Log file not found'
                ], 404);
            }

            File::put($logPath, '');

            // If redirect is requested, redirect back to logs page
            if ($request->input('redirect')) {
                return redirect()->route('logs.index')->with('success', 'Log file cleared successfully');
            }

            return response()->json([
                'success' => true,
                'message' => 'Log file cleared successfully'
            ]);
        } catch (\Exception $e) {
            if ($request->input('redirect')) {
                return redirect()->route('logs.index')->with('error', 'Error clearing log: ' . $e->getMessage());
            }
            return response()->json([
                'success' => false,
                'error' => 'Error clearing log: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get list of log files
     */
    private function getLogFiles(): array
    {
        $logPath = storage_path('logs');
        $files = File::files($logPath);
        
        $logFiles = [];
        foreach ($files as $file) {
            if ($file->getExtension() === 'log' || str_ends_with($file->getFilename(), '.log')) {
                $logFiles[] = [
                    'name' => $file->getFilename(),
                    'size' => $this->formatBytes($file->getSize()),
                    'modified' => date('Y-m-d H:i:s', $file->getMTime())
                ];
            }
        }

        return $logFiles;
    }

    /**
     * Get last N lines from file
     */
    private function getLastLines(string $filePath, int $lines, string $search = ''): string
    {
        $file = new \SplFileObject($filePath, 'r');
        $file->seek(PHP_INT_MAX);
        $lastLine = $file->key();
        
        $startLine = max(0, $lastLine - $lines);
        $file->seek($startLine);
        
        $content = [];
        while (!$file->eof()) {
            $line = $file->current();
            if ($search === '' || stripos($line, $search) !== false) {
                $content[] = $line;
            }
            $file->next();
        }
        
        return implode('', $content);
    }

    /**
     * Filter content by search term
     */
    private function filterContent(string $content, string $search): string
    {
        $lines = explode("\n", $content);
        $filtered = array_filter($lines, function($line) use ($search) {
            return stripos($line, $search) !== false;
        });
        
        return implode("\n", $filtered);
    }

    /**
     * Format bytes to human readable format
     */
    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }
}
