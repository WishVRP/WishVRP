<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StagingPasswordTest extends TestCase
{
    use RefreshDatabase;

    public function test_site_is_open_without_staging_password(): void
    {
        $this->get('/')->assertOk();
    }

    public function test_staging_password_protects_every_page(): void
    {
        config(['ulbrich_admin.staging_password' => 'geheim123', 'ulbrich_admin.staging_user' => 'ulbrich']);

        $this->get('/')->assertStatus(401)->assertHeader('WWW-Authenticate');
        $this->get('/weihnachtsaktion/')->assertStatus(401);
        $this->get('/', ['PHP_AUTH_USER' => 'ulbrich', 'PHP_AUTH_PW' => 'falsch'])->assertStatus(401);
        $this->get('/', ['PHP_AUTH_USER' => 'ulbrich', 'PHP_AUTH_PW' => 'geheim123'])->assertOk()
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow, noarchive');
    }

    public function test_stripe_webhook_stays_reachable(): void
    {
        config(['ulbrich_admin.staging_password' => 'geheim123']);

        $this->assertNotSame(401, $this->post('/stripe/webhook/')->getStatusCode());
    }

    public function test_never_active_in_production(): void
    {
        config(['ulbrich_admin.staging_password' => 'geheim123']);
        $this->app->detectEnvironment(fn () => 'production');

        $this->assertNotSame(401, $this->get('/')->getStatusCode());
    }

    public function test_staging_without_password_stays_closed(): void
    {
        config(['ulbrich_admin.staging_password' => '']);
        $this->app->detectEnvironment(fn () => 'staging');

        $this->get('/')->assertStatus(503);
    }
}
