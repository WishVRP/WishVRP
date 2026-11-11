<?php

namespace App\Providers\Filament;

use App\Http\Middleware\AdminNoIndex;
use Filament\Auth\MultiFactor\App\AppAuthentication;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            // Eigener, nicht verlinkter Pfad (ADMIN_PATH). Authentifizierung
            // ausschließlich serverseitig über Laravel-Sessions – kein
            // Admin-Markup oder -Skript in öffentlichen Seiten.
            ->path(config('ulbrich_admin.path'))
            ->brandName('Ulbrich Verwaltung')
            ->login()
            // 2FA per Authenticator-App ist Pflicht; ohne Einrichtung kein Zugriff.
            ->multiFactorAuthentication(
                [AppAuthentication::make()->recoverable()],
                isRequired: true,
            )
            ->colors([
                'primary' => Color::hex('#2d5f93'), // Anlassblau
                'gray' => Color::Slate,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                AccountWidget::class,
            ])
            ->databaseNotifications(false)
            ->sidebarCollapsibleOnDesktop()
            ->defaultAvatarProvider(\App\Filament\Support\InitialsAvatar::class)
            ->renderHook(\Filament\View\PanelsRenderHook::HEAD_END, fn () => new \Illuminate\Support\HtmlString('<link rel="stylesheet" href="/verwaltung-widgets.css">'))
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
                AdminNoIndex::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
