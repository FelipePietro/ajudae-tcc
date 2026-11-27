<?php

namespace App\Services;

use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;

class RailwayStorageConfiguration
{
    public function assertConfigured(string $disk): void
    {
        if (! in_array($disk, ['railway_private', 'railway_images'], true)) {
            return;
        }

        $config = config("filesystems.disks.{$disk}", []);
        foreach (['key', 'secret', 'region', 'bucket'] as $field) {
            if (! is_string($config[$field] ?? null) || trim($config[$field]) === '') {
                throw new ServiceUnavailableHttpException(null, 'Armazenamento privado S3 não configurado.');
            }
        }

        $endpoint = $config['endpoint'] ?? null;
        $parts = is_string($endpoint) ? parse_url($endpoint) : false;
        if (! is_array($parts) || ($parts['scheme'] ?? null) !== 'https'
            || empty($parts['host']) || isset($parts['user']) || isset($parts['pass'])
            || isset($parts['query']) || isset($parts['fragment'])) {
            throw new ServiceUnavailableHttpException(null, 'Endpoint HTTPS do armazenamento privado S3 inválido.');
        }
    }
}
