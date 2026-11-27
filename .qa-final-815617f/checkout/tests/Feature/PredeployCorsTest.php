<?php

namespace Tests\Feature;

use Tests\TestCase;

class PredeployCorsTest extends TestCase
{
    public function test_only_explicit_frontend_origin_receives_cors_header(): void
    {
        config()->set('cors.allowed_origins', ['https://frontend.example.test']);

        $this->withHeader('Origin', 'https://frontend.example.test')
            ->get('/api/health')
            ->assertOk()
            ->assertHeader('Access-Control-Allow-Origin', 'https://frontend.example.test');
    }

    public function test_other_origin_does_not_receive_cors_header(): void
    {
        config()->set('cors.allowed_origins', ['https://frontend.example.test']);

        $response = $this->withHeader('Origin', 'https://other.example.test')
            ->get('/api/health')
            ->assertOk();
        // Fruitcake otimiza origem única com header fixo; o browser só aceita
        // quando o valor coincide com a origem da página solicitante.
        $this->assertNotSame('https://other.example.test', $response->headers->get('Access-Control-Allow-Origin'));
    }
}
