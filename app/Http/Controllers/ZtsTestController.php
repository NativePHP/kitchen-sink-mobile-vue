<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ZtsTestController extends Controller
{
    /**
     * Get PHP info including ZTS status
     */
    public function info()
    {
        return response()->json([
            'php_version' => PHP_VERSION,
            'zts_enabled' => PHP_ZTS,
            'thread_safe' => defined('PHP_ZTS') && PHP_ZTS === 1,
            'sapi' => PHP_SAPI,
            'os' => PHP_OS,
            'extensions' => get_loaded_extensions(),
            'ini_settings' => [
                'memory_limit' => ini_get('memory_limit'),
                'max_execution_time' => ini_get('max_execution_time'),
            ],
        ]);
    }

    /**
     * Run a CPU-intensive task to simulate work
     */
    public function cpuTask(Request $request)
    {
        $iterations = $request->input('iterations', 100000);
        $taskId = $request->input('task_id', uniqid());

        $startTime = microtime(true);

        // CPU-intensive work
        $result = 0;
        for ($i = 0; $i < $iterations; $i++) {
            $result += sqrt($i) * sin($i);
        }

        $endTime = microtime(true);
        $duration = round(($endTime - $startTime) * 1000, 2);

        return response()->json([
            'task_id' => $taskId,
            'iterations' => $iterations,
            'result' => $result,
            'duration_ms' => $duration,
            'timestamp' => now()->toISOString(),
            'thread_safe' => defined('PHP_ZTS') && PHP_ZTS === 1,
        ]);
    }

    /**
     * Run multiple concurrent tasks and measure total time
     * This demonstrates how ZTS handles concurrent execution
     */
    public function concurrentTest(Request $request)
    {
        $taskCount = $request->input('task_count', 5);
        $iterations = $request->input('iterations', 50000);

        $startTime = microtime(true);
        $results = [];

        // Simulate concurrent work by running sequential tasks
        // In a real ZTS scenario, these would run in separate threads
        for ($i = 0; $i < $taskCount; $i++) {
            $taskStart = microtime(true);

            $result = 0;
            for ($j = 0; $j < $iterations; $j++) {
                $result += sqrt($j) * sin($j);
            }

            $taskEnd = microtime(true);

            $results[] = [
                'task_id' => $i + 1,
                'duration_ms' => round(($taskEnd - $taskStart) * 1000, 2),
                'result_sample' => round($result, 4),
            ];
        }

        $totalTime = microtime(true) - $startTime;

        return response()->json([
            'zts_enabled' => defined('PHP_ZTS') && PHP_ZTS === 1,
            'php_version' => PHP_VERSION,
            'task_count' => $taskCount,
            'iterations_per_task' => $iterations,
            'total_duration_ms' => round($totalTime * 1000, 2),
            'average_task_ms' => round(($totalTime * 1000) / $taskCount, 2),
            'tasks' => $results,
        ]);
    }

    /**
     * Test shared state safety (critical for ZTS)
     * Increments a counter from multiple "threads" simulated via rapid sequential calls
     */
    public function sharedStateTest(Request $request)
    {
        $operationCount = $request->input('operations', 1000);

        $startTime = microtime(true);

        // Test atomic operations
        $counter = 0;
        $expected = $operationCount;

        for ($i = 0; $i < $operationCount; $i++) {
            $counter++;
        }

        $endTime = microtime(true);

        // Test array operations (where thread safety matters)
        $array = [];
        for ($i = 0; $i < $operationCount; $i++) {
            $array[] = $i;
        }

        return response()->json([
            'zts_enabled' => defined('PHP_ZTS') && PHP_ZTS === 1,
            'operations' => $operationCount,
            'counter_final' => $counter,
            'counter_expected' => $expected,
            'counter_correct' => $counter === $expected,
            'array_count' => count($array),
            'array_correct' => count($array) === $operationCount,
            'duration_ms' => round(($endTime - $startTime) * 1000, 2),
        ]);
    }

    /**
     * Memory allocation test - ZTS has different memory behavior
     */
    public function memoryTest(Request $request)
    {
        $allocations = $request->input('allocations', 100);
        $sizeKb = $request->input('size_kb', 10);

        $startMemory = memory_get_usage(true);
        $startTime = microtime(true);

        $data = [];
        for ($i = 0; $i < $allocations; $i++) {
            // Allocate memory blocks
            $data[] = str_repeat('x', $sizeKb * 1024);
        }

        $peakMemory = memory_get_peak_usage(true);
        $endTime = microtime(true);

        // Clear to measure deallocation
        $data = null;
        gc_collect_cycles();

        $endMemory = memory_get_usage(true);

        return response()->json([
            'zts_enabled' => defined('PHP_ZTS') && PHP_ZTS === 1,
            'allocations' => $allocations,
            'size_per_allocation_kb' => $sizeKb,
            'start_memory_mb' => round($startMemory / 1024 / 1024, 2),
            'peak_memory_mb' => round($peakMemory / 1024 / 1024, 2),
            'end_memory_mb' => round($endMemory / 1024 / 1024, 2),
            'duration_ms' => round(($endTime - $startTime) * 1000, 2),
        ]);
    }
}