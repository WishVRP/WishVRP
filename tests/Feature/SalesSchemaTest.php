<?php

namespace Tests\Feature;

use Database\Seeders\CourseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SalesSchemaTest extends TestCase
{
    use RefreshDatabase;

    /** @return array<int, array<string, mixed>> */
    private function graph(string $path): array
    {
        $this->seed(CourseSeeder::class);
        $html = $this->get($path)->assertOk()->getContent();
        preg_match('/<script type="application\/ld\+json"[^>]*>(.*?)<\/script>/s', $html, $m);

        return json_decode($m[1], true, flags: JSON_THROW_ON_ERROR)['@graph'];
    }

    public function test_business_names_sales_area_from_config(): void
    {
        $business = collect($this->graph('/'))->firstWhere('@type', 'LocalBusiness');

        $this->assertSame(config('ulbrich.sales.area_served'), $business['areaServed']);
    }

    public function test_edition_offer_has_parcel_and_pickup_and_only_german_shipping_rate(): void
    {
        $product = collect($this->graph('/klappmesser/'))->firstWhere('@type', 'Product');
        $offer = $product['offers'];

        $this->assertContains('http://purl.org/goodrelations/v1#DeliveryModePickUp', $offer['availableDeliveryMethod']);
        $this->assertContains('http://purl.org/goodrelations/v1#DeliveryModeParcelService', $offer['availableDeliveryMethod']);
        $this->assertSame('DE', $offer['shippingDetails']['shippingDestination']['addressCountry']);
        $this->assertSame('7.99', $offer['shippingDetails']['shippingRate']['value']);
    }
}
