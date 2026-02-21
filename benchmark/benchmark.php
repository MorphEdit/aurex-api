<?php

/**
 * Aurex API Benchmark
 *
 * Usage:
 *   php benchmark/benchmark.php [base_url] [requests] [concurrency]
 *
 * Examples:
 *   php benchmark/benchmark.php
 *   php benchmark/benchmark.php http://localhost/aurex-api/public 200 10
 */

declare(strict_types=1);

// ── Config ────────────────────────────────────────────────────────────────────

$baseUrl     = $argv[1] ?? 'http://localhost/aurex-api/public';
$totalReqs   = (int) ($argv[2] ?? 100);
$concurrency = (int) ($argv[3] ?? 10);

$loginEmail    = $argv[4] ?? 'superadmin@aurex.local';
$loginPassword = $argv[5] ?? 'password';

// ── Helpers ───────────────────────────────────────────────────────────────────

function request(string $url, string $method = 'GET', array $body = [], array $headers = []): array
{
    $ch = curl_init();

    curl_setopt_array($ch, [
        CURLOPT_URL            => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 10,
        CURLOPT_CUSTOMREQUEST  => $method,
        CURLOPT_HTTPHEADER     => array_merge(['Content-Type: application/json'], $headers),
    ]);

    if (!empty($body)) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
    }

    $start    = hrtime(true);
    $response = curl_exec($ch);
    $elapsed  = (hrtime(true) - $start) / 1_000_000; // ms

    $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return [
        'status'  => $statusCode,
        'ms'      => round($elapsed, 2),
        'body'    => json_decode($response, true),
    ];
}

function stats(array $times): array
{
    sort($times);
    $count = count($times);

    return [
        'min'   => round(min($times), 2),
        'max'   => round(max($times), 2),
        'avg'   => round(array_sum($times) / $count, 2),
        'p95'   => round($times[(int) ($count * 0.95)], 2),
        'p99'   => round($times[(int) ($count * 0.99)], 2),
        'count' => $count,
    ];
}

function bench(string $label, string $url, string $method = 'GET', array $body = [], array $headers = [], int $n = 100): void
{
    $times   = [];
    $errors  = 0;

    echo "  Benchmarking: {$label} ... ";

    for ($i = 0; $i < $n; $i++) {
        $result = request($url, $method, $body, $headers);

        if ($result['status'] >= 200 && $result['status'] < 300) {
            $times[] = $result['ms'];
        } else {
            $errors++;
        }
    }

    if (empty($times)) {
        echo "FAILED (all {$errors} requests errored — status {$result['status']})\n";
        return;
    }

    $s       = stats($times);
    $elapsed = array_sum($times) / 1000; // seconds (wall time approx)
    $rps     = $elapsed > 0 ? round(count($times) / $elapsed, 1) : 0;

    echo "done\n";
    printf(
        "  %-12s %-12s %-12s %-12s %-12s %-12s %-12s\n",
        'min(ms)', 'avg(ms)', 'p95(ms)', 'p99(ms)', 'max(ms)', 'errors', 'req/s'
    );
    printf(
        "  %-12s %-12s %-12s %-12s %-12s %-12s %-12s\n",
        $s['min'], $s['avg'], $s['p95'], $s['p99'], $s['max'],
        $errors, $rps
    );
    echo "\n";
}

function section(string $title): void
{
    echo "\n" . str_repeat('─', 60) . "\n";
    echo "  {$title}\n";
    echo str_repeat('─', 60) . "\n";
}

// ── Run ───────────────────────────────────────────────────────────────────────

echo "\n";
echo "  ╔══════════════════════════════════════╗\n";
echo "  ║       Aurex API Benchmark            ║\n";
echo "  ╚══════════════════════════════════════╝\n";
echo "\n";
echo "  Base URL    : {$baseUrl}\n";
echo "  Requests    : {$totalReqs} per endpoint\n";
echo "  PHP version : " . PHP_VERSION . "\n";
echo "  Date        : " . date('Y-m-d H:i:s') . "\n";

// Step 1: Login to get token
section('Step 1 — Login');
echo "  Authenticating as {$loginEmail} ...\n";

$loginResult = request("{$baseUrl}/api/v1/auth/login", 'POST', [
    'email'    => $loginEmail,
    'password' => $loginPassword,
]);

if ($loginResult['status'] !== 200 || empty($loginResult['body']['data']['token'])) {
    echo "\n  ERROR: Login failed (HTTP {$loginResult['status']})\n";
    echo "  Make sure:\n";
    echo "    1. Web server is running\n";
    echo "    2. Database is migrated\n";
    echo "    3. .env is configured correctly\n";
    echo "    4. Base URL is correct: {$baseUrl}\n\n";
    exit(1);
}

$token   = $loginResult['body']['data']['token'];
$authHdr = ["Authorization: Bearer {$token}"];

echo "  Login successful. Token acquired.\n";

// Step 2: Benchmark public endpoint (login)
section('Step 2 — POST /api/v1/auth/login  [public]');
bench(
    'login',
    "{$baseUrl}/api/v1/auth/login",
    'POST',
    ['email' => $loginEmail, 'password' => $loginPassword],
    [],
    $totalReqs
);

// Step 3: Benchmark authenticated endpoint
section('Step 3 — GET /api/v1/auth/me  [authenticated]');
bench(
    'auth/me',
    "{$baseUrl}/api/v1/auth/me",
    'GET',
    [],
    $authHdr,
    $totalReqs
);

// Step 4: Benchmark list endpoint (paginated, DB query)
section('Step 4 — GET /api/v1/employees  [DB read, paginated]');
bench(
    'employees list',
    "{$baseUrl}/api/v1/employees?page=1&per_page=15",
    'GET',
    [],
    $authHdr,
    $totalReqs
);

// Step 5: Benchmark list with search filter
section('Step 5 — GET /api/v1/employees?search=a  [DB read + filter]');
bench(
    'employees search',
    "{$baseUrl}/api/v1/employees?search=a&page=1&per_page=15",
    'GET',
    [],
    $authHdr,
    $totalReqs
);

// Step 6: Memory snapshot
section('Step 6 — Memory');
echo "  Peak memory (this script) : " . round(memory_get_peak_usage(true) / 1024 / 1024, 2) . " MB\n\n";

echo str_repeat('─', 60) . "\n";
echo "  Benchmark complete.\n";
echo "  Copy these results into README.md under a ## Benchmark section.\n";
echo str_repeat('─', 60) . "\n\n";
