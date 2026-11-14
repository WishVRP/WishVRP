<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MaintenanceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['ulbrich_admin.maintenance' => true, 'ulbrich_admin.maintenance_password' => 'esse2027']);
    }

    public function test_visitors_see_maintenance_screen_with_503(): void
    {
        $this->get('/')->assertStatus(503)->assertHeader('Retry-After')
            ->assertSee('Wir bauen gerade um.')->assertSee(config('ulbrich.phone.display'))
            ->assertDontSee('Kurs buchen');
        $this->get('/schmiedekurse/')->assertStatus(503);
    }

    public function test_legal_pages_and_admin_stay_reachable(): void
    {
        $this->get('/impressum/')->assertOk();
        $this->get('/datenschutz/')->assertOk();
        $this->get('/'.config('ulbrich_admin.path').'/login')->assertOk();
        $this->get('/up')->assertOk();
    }

    public function test_preview_password_sets_cookie(): void
    {
        $this->get('/?vorschau=falsch')->assertStatus(503);
        $res = $this->get('/?vorschau=esse2027')->assertRedirect();
        $cookie = collect($res->headers->getCookies())->first(fn ($c) => $c->getName() === 'ulbrich_preview');
        $this->withUnencryptedCookie('ulbrich_preview', $cookie->getValue())->get('/')->assertOk()->assertHeader('X-Robots-Tag', 'noindex, nofollow');
    }

    public function test_no_password_means_no_preview_link(): void
    {
        config(['ulbrich_admin.maintenance_password' => '']);
        $this->get('/?vorschau=')->assertStatus(503);
    }

    public function test_off_by_default(): void
    {
        config(['ulbrich_admin.maintenance' => false]);
        $this->get('/')->assertOk();
    }
}
