<?php

namespace Tests\Feature;

use Database\Seeders\CourseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FotosTest extends TestCase
{
    use RefreshDatabase;

    public function test_alle_konfigurierten_fotos_existieren_in_allen_breiten(): void
    {
        foreach (config('fotos') as $key => $foto) {
            $this->assertNotEmpty($foto['alt'], "Alt-Text fehlt: {$key}");
            foreach ($foto['widths'] as $w) {
                foreach (['jpg', 'webp'] as $ext) {
                    $this->assertFileExists(public_path(ltrim(\App\Support\Foto::url($key, $w, $ext), '/')));
                }
            }
        }
        $this->assertFileExists(public_path(ltrim(config('seo.default_og_image'), '/')));
    }

    public function test_startseite_bindet_fotos_mit_massen_und_ohne_interne_hinweise_ein(): void
    {
        $this->seed(CourseSeeder::class);
        $html = $this->get('/')->assertOk()->getContent();

        preg_match_all('/<img [^>]*src="\/images\/fotos\/[^>]*>/', $html, $m);
        $this->assertGreaterThanOrEqual(20, count($m[0]));
        $this->assertStringContainsString('id="galerie"', $html);
        $this->assertStringContainsString('data-sparks', $html);
        foreach ($m[0] as $tag) {
            $this->assertMatchesRegularExpression('/ width="\d+"/', $tag);
            $this->assertMatchesRegularExpression('/ height="\d+"/', $tag);
            $this->assertStringContainsString(' alt="', $tag);
        }
        // Nur das Hero-Bild wird priorisiert, alle anderen laden verzögert
        $this->assertSame(1, substr_count($html, 'fetchpriority="high"'));
        // Interne Prüfhinweise (config fotos.pruefen) gehören nicht ins öffentliche HTML
        $this->assertStringNotContainsString('Zigarette', $html);
        $this->assertStringNotContainsString('Einwilligung', $html);
        $this->assertStringNotContainsString('One Piece', $html);
    }
}
