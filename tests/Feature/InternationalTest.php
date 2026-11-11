<?php

namespace Tests\Feature;

use Database\Seeders\CourseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InternationalTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CourseSeeder::class);
    }

    public function test_english_pages_have_lang_hreflang_pairs_and_existing_german_counterparts(): void
    {
        $pages = config('pages');
        foreach ($pages as $path => $page) {
            if (! str_starts_with($path, '/en/')) {
                continue;
            }
            $this->assertArrayHasKey('alternate', $page, "{$path} braucht ein deutsches Gegenstück");
            $this->assertArrayHasKey($page['alternate'], $pages, "Gegenstück von {$path} existiert nicht");

            $en = $this->get($path)->assertOk()->getContent();
            $this->assertStringContainsString('<html lang="en"', $en);
            $this->assertStringContainsString('hreflang="de" href="https://messermanufaktur-ulbrich.de'.$page['alternate'].'"', $en);
            $this->assertStringContainsString('hreflang="x-default"', $en);
            $this->assertStringContainsString('"inLanguage": "en"', $en);
            $this->assertStringNotContainsString('Kurs buchen', $en, "Deutsche Navigation auf {$path}");

            $de = $this->get($page['alternate'])->getContent();
            $this->assertStringContainsString('hreflang="en" href="https://messermanufaktur-ulbrich.de'.$path.'"', $de, "Rückverweis fehlt auf {$page['alternate']}");
        }
    }

    public function test_shipping_page_shows_sales_facts_from_config(): void
    {
        $html = $this->get('/versand/')->assertOk()->getContent();

        $this->assertStringContainsString('7,99 € pro Artikel', $html);
        $this->assertStringContainsString('berechnet das Bestellformular nach Land und Gewicht', $html);
        $this->assertStringContainsString('Zoll', $html);
        $this->assertStringContainsString('"@type": "FAQPage"', $html);

        $shop = $this->get('/shop/')->getContent();
        $this->assertStringContainsString(e(config('ulbrich.sales.pickup_note')), $shop);
    }

    public function test_service_pages_have_service_schema_and_business_lists_payment(): void
    {
        foreach (['/messer-schaerfen-lassen/', '/messer-reparieren-lassen/', '/en/custom-knives/'] as $path) {
            $this->assertStringContainsString('"@type": "Service"', $this->get($path)->getContent(), $path);
        }
        $this->assertStringContainsString('"paymentAccepted": "Überweisung, Barzahlung (vor Ort), Kartenzahlung (vor Ort)"', $this->get('/')->getContent());
    }

    public function test_warranty_comes_from_config_and_keeps_statutory_rights(): void
    {
        $html = $this->get('/garantie/')->assertOk()->getContent();
        $this->assertStringContainsString('2 Jahre Garantie', $html);
        $this->assertStringContainsString('Spülmaschine', $html);
        $this->assertStringContainsString('gesetzlichen Gewährleistungsrechte bleiben davon unberührt', $html);

        $edition = $this->get('/klappmesser/')->getContent();
        $this->assertStringContainsString('"@type": "WarrantyPromise"', $edition);
        $this->assertStringContainsString('Pflegemittel, kostenlos', $edition);
    }

    public function test_llms_txt_names_shipping_rules(): void
    {
        $txt = $this->get('/llms.txt')->getContent();
        $this->assertStringContainsString(\App\Support\Shipping::domesticLabel(), $txt);
        $this->assertStringContainsString(config('ulbrich.sales.international'), $txt);
    }
}
