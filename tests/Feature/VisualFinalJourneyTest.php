<?php

namespace Tests\Feature;

use App\Support\PublicProfileFixtures;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class VisualFinalJourneyTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['session.driver' => 'array', 'cache.default' => 'array']);
        DB::shouldReceive('connection')->andThrow(new \LogicException('Database outside visual scope.'));
        Http::preventStrayRequests();
    }

    public function test_landing_uses_approved_first_level_and_next_threshold(): void
    {
        $this->get('/?contexto=visitante')->assertOk()
            ->assertSee('Iniciante · 450 / 500 XP')->assertSee('w-[90%]', false)
            ->assertDontSee('Aprendiz')->assertDontSee('600 XP');
        Http::assertNothingSent();
    }

    public function test_public_profiles_use_selected_xp_and_have_no_dead_certificate_links(): void
    {
        foreach ([1 => ['Colaborador', '500 XP'], 2 => ['Colaborador', '900 XP'], 3 => ['Iniciante', '150 XP']] as $id => [$level, $remaining]) {
            $this->get('/perfil?area=publica&contexto=visitante&pessoa='.$id)
                ->assertOk()->assertSee($level)->assertSee($remaining)
                ->assertDontSee('Aprendiz')->assertDontSee('href="#"', false)
                ->assertSee('Verificar código')->assertDontSee('Meu cadastro');
        }
        Http::assertNothingSent();
    }

    public function test_public_ranking_preview_does_not_place_lower_xp_above_selected_person(): void
    {
        $profile = PublicProfileFixtures::selectedPerson(1);
        $previous = PHP_INT_MAX;
        foreach ($profile['ranking'] as $entry) {
            $this->assertGreaterThan($profile['xp_user'], $entry['xp']);
            $this->assertLessThanOrEqual($previous, $entry['xp']);
            $previous = $entry['xp'];
        }
    }
}
