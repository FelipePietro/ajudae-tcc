<?php

namespace Tests\Feature;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class TrustedProxyHttpsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Route::get('/api/proxy-audit', function () {
            $pages = new LengthAwarePaginator([], 0, 20, 1, ['path' => request()->url()]);

            return response()->json([
                'asset' => asset('build/assets/app.css'),
                'page' => $pages->url(1),
                'ip' => request()->ip(),
            ]);
        });
    }

    public function test_trusted_proxy_generates_https_assets_and_pagination_without_trusting_host_or_port(): void
    {
        config()->set('trustedproxy.proxies', ['10.0.0.2']);

        $this->withServerVariables(['REMOTE_ADDR' => '10.0.0.2'])
            ->withHeaders([
                'X-Forwarded-Proto' => 'https',
                'X-Forwarded-For' => '203.0.113.10',
                'X-Forwarded-Host' => 'attacker.example',
                'X-Forwarded-Port' => '81',
            ])
            ->get('http://app.example/api/proxy-audit')
            ->assertOk()
            ->assertJsonPath('asset', 'https://app.example/build/assets/app.css')
            ->assertJsonPath('page', 'https://app.example/api/proxy-audit?page=1')
            ->assertJsonPath('ip', '10.0.0.2');
    }

    public function test_explicit_wildcard_supports_dynamic_edge_addresses(): void
    {
        config()->set('trustedproxy.proxies', '*');

        $this->withServerVariables(['REMOTE_ADDR' => '10.0.0.3'])
            ->withHeader('X-Forwarded-Proto', 'https')
            ->get('http://app.example/api/proxy-audit')
            ->assertOk()
            ->assertJsonPath('asset', 'https://app.example/build/assets/app.css');
    }

    public function test_untrusted_client_cannot_spoof_scheme_or_ip(): void
    {
        config()->set('trustedproxy.proxies', ['10.0.0.2']);

        $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.20'])
            ->withHeaders(['X-Forwarded-Proto' => 'https', 'X-Forwarded-For' => '203.0.113.10'])
            ->get('http://app.example/api/proxy-audit')
            ->assertOk()
            ->assertJsonPath('asset', 'http://app.example/build/assets/app.css')
            ->assertJsonPath('ip', '203.0.113.20');
    }

    public function test_local_http_remains_http_without_a_proxy(): void
    {
        config()->set('trustedproxy.proxies', []);

        $this->get('http://localhost/api/proxy-audit')
            ->assertOk()
            ->assertJsonPath('asset', 'http://localhost/build/assets/app.css')
            ->assertJsonPath('page', 'http://localhost/api/proxy-audit?page=1');
    }

    public function test_public_pages_render_built_vite_assets_over_https(): void
    {
        if (! is_file(public_path('build/manifest.json'))) {
            $this->markTestSkipped('Run npm run build to verify production Vite entries.');
        }

        config()->set('trustedproxy.proxies', ['10.0.0.2']);

        foreach (['/', '/cadastro/ong'] as $path) {
            $response = $this->withServerVariables(['REMOTE_ADDR' => '10.0.0.2'])
                ->withHeader('X-Forwarded-Proto', 'https')
                ->get('http://app.example'.$path)
                ->assertOk();

            $response->assertSee('https://app.example/build/assets/', false);
            $response->assertDontSee('http://app.example/build/assets/', false);
        }

        $manifest = json_decode(file_get_contents(public_path('build/manifest.json')), true, flags: JSON_THROW_ON_ERROR);
        foreach (glob(resource_path('css/*.css')) as $source) {
            $entry = 'resources/css/'.basename($source);
            $this->assertArrayHasKey($entry, $manifest);
            $this->assertFileExists(public_path('build/'.$manifest[$entry]['file']));
        }
    }
}
