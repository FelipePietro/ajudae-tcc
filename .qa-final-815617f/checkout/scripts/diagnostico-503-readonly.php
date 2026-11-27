<?php

// Diagnostic helper only. No upload, DB query, S3 request, log or config write.
// config mode bootstraps Laravel configuration; memory mode does not boot Laravel.
$mode = $argv[1] ?? 'memory';
$root = $argv[2] ?? dirname(__DIR__);
try {
    require $root.'/vendor/autoload.php';
    if (! in_array($mode, ['memory', 'config'], true)) {
        throw new InvalidArgumentException;
    }
    if ($mode === 'config') {
        $app = require $root.'/bootstrap/app.php';
        $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
        $disk = config('filesystems.image_storage_disk');
        $knownDisk = in_array($disk, ['railway_images', 'image_local'], true);
        $configuration = $knownDisk ? config('filesystems.disks.'.$disk, []) : [];
        $presence = [];
        foreach (['key', 'secret', 'region', 'bucket'] as $field) {
            $presence[$field] = is_string($configuration[$field] ?? null) && trim($configuration[$field]) !== '';
        }
        $endpoint = $configuration['endpoint'] ?? null;
        $parts = is_string($endpoint) ? parse_url($endpoint) : false;
        $validEndpoint = is_array($parts) && ($parts['scheme'] ?? null) === 'https'
            && ! empty($parts['host']) && ! isset($parts['user']) && ! isset($parts['pass'])
            && ! isset($parts['query']) && ! isset($parts['fragment']);
        $validator = 'not_applicable';
        if ($disk === 'railway_images') {
            try {
                (new App\Services\RailwayStorageConfiguration)->assertConfigured($disk);
                $validator = 'passed';
            } catch (Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException) {
                $validator = 'rejected';
            }
        }
        echo json_encode([
            'scope' => 'CLI_config_not_HTTP',
            'disk' => $knownDisk ? $disk : 'unexpected_value_redacted',
            'is_railway_images' => $disk === 'railway_images',
            'driver_is_s3' => ($configuration['driver'] ?? null) === 's3',
            'visibility_is_private' => ($configuration['visibility'] ?? null) === 'private',
            'required_fields_present' => $presence,
            'endpoint_valid_https' => $validEndpoint,
            'existing_validator' => $validator,
            's3_network_attempted' => false,
        ], JSON_THROW_ON_ERROR).PHP_EOL;
    }

    $limit = ini_get('memory_limit');
    $multiplier = match (strtolower(substr((string) $limit, -1))) {
        'g' => 1024 ** 3, 'm' => 1024 ** 2, 'k' => 1024, default => 1,
    };
    $limitBytes = (int) $limit * $multiplier;
    $estimate = 64 * 64 * 16 + 291 * 3 + 32 * 1024 * 1024;
    $used = memory_get_usage(true);
    $guard = 'passed';
    try {
        $method = new ReflectionMethod(App\Services\ManagedImageStorage::class, 'assertProcessingCapacity');
        $method->invoke(new App\Services\ManagedImageStorage, 64, 64, 291);
    } catch (Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException $exception) {
        if ($exception->getStatusCode() !== 503) {
            throw $exception;
        }
        $guard = 'rejected_503';
    }
    echo json_encode([
        'scope' => 'CLI_not_HTTP', 'php' => PHP_VERSION, 'sapi' => PHP_SAPI,
        'memory_limit' => $limit, 'memory_limit_bytes' => $limitBytes,
        'current_usage_bytes' => $used, 'fixture_estimate_bytes' => $estimate,
        'fixture_estimate_mib' => $estimate / (1024 ** 2),
        'existing_capacity_guard' => $guard,
        'guard_stage' => 'before_decode_and_before_storage',
        's3_network_attempted' => false,
    ], JSON_THROW_ON_ERROR).PHP_EOL;
    exit(0);
} catch (Throwable) {
    // Never print raw exceptions, config values, stack traces or filesystem paths.
    fwrite(STDERR, "Read-only diagnostic could not complete; details suppressed.\n");
    exit(1);
}
