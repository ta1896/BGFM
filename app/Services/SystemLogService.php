<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Collection;

class SystemLogService
{
    private string $logPath;

    public function __construct()
    {
        $this->logPath = storage_path('logs/laravel.log');
    }

    public function getRecentLogs(int $limit = 100): Collection
    {
        if (!File::exists($this->logPath)) {
            return collect();
        }

        $fileSize = File::size($this->logPath);
        $readSize = min($fileSize, 512 * 1024); // Read last 512KB

        $handle = fopen($this->logPath, 'r');
        fseek($handle, -$readSize, SEEK_END);
        $content = fread($handle, $readSize);
        fclose($handle);

        $pattern = '/\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\] (\w+)\.(\w+): (.*?) (\{.*?\})? (?=\[|$)/s';

        preg_match_all($pattern, $content, $matches, PREG_SET_ORDER);

        return collect($matches)->map(function ($match) {
            return [
                'timestamp' => $match[1],
                'environment' => $match[2],
                'level' => strtoupper($match[3]),
                'message' => $match[4],
                'context' => $match[5] ?? null,
            ];
        })->reverse()->take($limit)->values();
    }

    /**
     * Get statistics about logs.
     */
    public function getLogStats(): array
    {
        $logs = $this->getRecentLogs(500);

        return [
            'total' => $logs->count(),
            'errors' => $logs->where('level', 'ERROR')->count(),
            'warnings' => $logs->where('level', 'WARNING')->count(),
            'critical' => $logs->where('level', 'CRITICAL')->count(),
            'latest_error' => $logs->where('level', 'ERROR')->first()['timestamp'] ?? null,
        ];
    }

    /**
     * Clear the log file.
     */
    public function clearLogs(): bool
    {
        if (File::exists($this->logPath)) {
            return File::put($this->logPath, '') !== false;
        }
        return false;
    }
}
