<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Support\Foto;
use Database\Seeders\CourseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ImageSeoTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_photo_has_seo_filename_alt_and_all_files(): void
    {
        foreach (config('fotos') as $key => $f) {
            $this->assertMatchesRegularExpression('/^[a-z0-9]+(-[a-z0-9]+)+$/', $f['datei'] ?? '', "{$key}: sprechender Dateiname fehlt");
            $this->assertStringContainsString('ulbrich', $f['datei'], "{$key}: Marke im Dateinamen");
            $this->assertGreaterThanOrEqual(40, mb_strlen($f['alt']), "{$key}: Alttext zu kurz");
            $this->assertLessThanOrEqual(125, mb_strlen($f['alt']), "{$key}: Alttext zu lang");
            $this->assertStringContainsString('Ulbrich', $f['alt'], "{$key}: Marke im Alttext");
            foreach ($f['widths'] as $w) {
                foreach (['jpg', 'webp'] as $ext) {
                    $this->assertFileExists(public_path(ltrim(Foto::url($key, $w, $ext), '/')));
                }
            }
        }
    }

    public function test_rendered_pages_have_no_camera_filenames_or_empty_alts_on_content_images(): void
    {
        $this->seed(CourseSeeder::class);
        foreach (['/', '/ueber-uns/', '/schmiedekurse/', '/klappmesser/sonderedition/'] as $path) {
            $res = $this->get($path);
            if ($res->status() !== 200) {
                continue;
            }
            $html = $res->getContent();
            $this->assertDoesNotMatchRegularExpression('/(IMG|DSC|PXL|WhatsApp)[_-]?\d+/i', $html, $path);
            preg_match_all('/<img[^>]+src="\/images\/fotos\/[^"]+"[^>]*>/', $html, $m);
            foreach ($m[0] as $tag) {
                $this->assertMatchesRegularExpression('/alt="[^"]{20,}"/', $tag, "{$path}: Alttext fehlt: {$tag}");
            }
        }
    }

    public function test_image_sitemap_lists_photos(): void
    {
        $xml = $this->get('/sitemap.xml')->assertOk()->getContent();
        $this->assertStringContainsString('xmlns:image=', $xml);
        $this->assertStringContainsString('<image:loc>https://messermanufaktur-ulbrich.de/images/fotos/handgeschmiedetes-damast-klappmesser-offen-messermanufaktur-ulbrich-1200.jpg</image:loc>', $xml);
        $this->assertNotFalse(simplexml_load_string($xml));
    }

    public function test_uploaded_product_photo_gets_safe_speaking_name(): void
    {
        $name = Product::imageFileName('Damast-Kochmesser „Sakura“ 21 cm', 'image/jpeg');
        $this->assertMatchesRegularExpression('/^damast-kochmesser-sakura-21-cm-messermanufaktur-ulbrich-[a-z0-9]{6}\.jpg$/', $name);
        $this->assertStringEndsWith('.webp', Product::imageFileName('x', 'image/webp'));
        $this->assertStringEndsWith('.jpg', Product::imageFileName('x', 'application/x-php'));
    }
}
