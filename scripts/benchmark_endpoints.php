<?php

declare(strict_types=1);

$baseUrl = rtrim(getenv('BENCH_BASE') ?: 'http://127.0.0.1:8000', '/');
$routesJson = shell_exec('php artisan route:list --path=api --json');

if (!$routesJson) {
    fwrite(STDERR, "Gagal membaca route list.\n");
    exit(1);
}

$routes = json_decode($routesJson, true);
if (!is_array($routes)) {
    fwrite(STDERR, "Format route list tidak valid.\n");
    exit(1);
}

$results = [];

foreach ($routes as $route) {
    $methodRaw = (string) ($route['method'] ?? 'GET');
    $methods = array_values(array_filter(explode('|', $methodRaw), static fn ($m) => $m !== 'HEAD'));
    $method = $methods[0] ?? 'GET';

    $uri = (string) ($route['uri'] ?? '');
    if ($uri === '') {
        continue;
    }

    $sanitizedUri = preg_replace('/\{[^}]+\}/', '1', $uri);
    $url = $baseUrl . '/' . ltrim((string) $sanitizedUri, '/');

    if (str_contains($uri, 'bmkg/prakiraan-cuaca')) {
        $url .= '?wilayah_id=3171';
    }

    $body = null;
    if (in_array($method, ['POST', 'PUT', 'PATCH'], true)) {
        if (str_contains($uri, 'auth/login')) {
            $body = json_encode(['username' => 'bench', 'password' => 'bench']);
        } elseif (str_contains($uri, 'auth/register')) {
            $body = json_encode([]);
        } else {
            $body = json_encode([]);
        }
    }

    $ch = curl_init($url);
    $headers = ['Accept: application/json', 'X-Request-Id: bench-' . uniqid()];

    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 20);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);

    if ($body !== null) {
        $headers[] = 'Content-Type: application/json';
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
    }

    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $start = microtime(true);
    curl_exec($ch);
    $elapsedMs = round((microtime(true) - $start) * 1000, 2);
    $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    $results[] = [
        'method' => $method,
        'uri' => '/' . ltrim($uri, '/'),
        'status' => $status,
        'ms' => $elapsedMs,
        'error' => $error,
    ];
}

$durations = array_map(static fn ($r) => $r['ms'], $results);
sort($durations);

$count = count($durations);
$avg = $count ? round(array_sum($durations) / $count, 2) : 0;
$median = $count ? $durations[(int) floor(($count - 1) / 2)] : 0;
$p95Index = $count ? max(0, (int) ceil($count * 0.95) - 1) : 0;
$p95 = $count ? $durations[$p95Index] : 0;

usort($results, static fn ($a, $b) => $b['ms'] <=> $a['ms']);

$csvPath = __DIR__ . '/../storage/logs/api-benchmark-latency.csv';
$fp = fopen($csvPath, 'w');
fputcsv($fp, ['method', 'uri', 'status', 'ms', 'error']);
foreach ($results as $row) {
    fputcsv($fp, [$row['method'], $row['uri'], $row['status'], $row['ms'], $row['error']]);
}
fclose($fp);

echo "TOTAL_ENDPOINTS={$count}\n";
echo "AVG_MS={$avg}\n";
echo "MEDIAN_MS={$median}\n";
echo "P95_MS={$p95}\n";
echo "CSV_PATH={$csvPath}\n";
echo "---TOP_SLOWEST---\n";

$top = array_slice($results, 0, 20);
foreach ($top as $row) {
    echo sprintf("%s %s status=%d ms=%.2f%s\n", $row['method'], $row['uri'], $row['status'], $row['ms'], $row['error'] ? ' error=' . $row['error'] : '');
}
