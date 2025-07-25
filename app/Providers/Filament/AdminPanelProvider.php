<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
     ->colors([

         'background' => [
                    'light' => '#4169E1',  // Dark Green (Slightly muted from original circle color)
                    'dark' => '#77B6E0',       // Very Dark Green (Almost Black, rich base)
                    'semi-transparent' => '#15262180', // Semi-transparent Very Dark Green
                    'custom' => '#7A6E59',     // Darkened Beige (Beige Outline/Text color)
                ],
    'primary' => '#77B6E0', // الأزرق المتوسط كلون أساسي
    'secondary' => '#A7C7E7', // الأزرق الفاتح
    'accent' => '#4169E1', // الأزرق الداكن
    'neutral' => '#F7F7F7', // أبيض فاتح للخلفيات
    'base-100' => '#FFFFFF', // أبيض نقي للسطوح
    'info' => '#1E3A5F', // أزرق داكن مائل للنيلي
    'success' => '#A7C7E7', // لون خفيف للنجاح
    'warning' => '#FAF9F6', // أبيض دافئ كتنبيه
    'danger' => '#D3D3D3', // رمادي ناعم كتحذير
])

            ->favicon(asset("logo.PNG"))
            ->brandLogo(asset("logo.PNG"))
            // ->brandLogoHtml('<img src="' . asset('logo.jpg') . '" style="height: 40px; border-radius: 50%;">')

            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
                // Widgets\FilamentInfoWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
