<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
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
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Closure;
use App\Http\Middleware\CheckBusinessLicense;


class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->font('poppins')
            ->brandName('JFProducts') //Nombre de la empresa
            ->login()
            ->colors([
                'primary' =>  Color::hex('#165ed3ff'),     
            ])
            ->darkMode(false)
            //Logo principal
            ->brandLogo(asset('img/logoNavbar.jpg'))
            ->brandLogoHeight('4rem')
            // favicon
            ->favicon(asset('img/favicon.ico'))
            //->sidebarFullyCollapsibleOnDesktop()
            ->sidebarCollapsibleOnDesktop()
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverClusters(in: app_path('Filament/Clusters'), for: 'App\\Filament\\Clusters')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                \App\Filament\Widgets\ExpiredProductsWidget::class,
            ])
            ->renderHook(
                'panels::styles.after',
                fn () => view('filament.custom-styles'),
            )
            ->renderHook(
                'panels::user-menu.before',
                fn () => view('filament.custom.business-name-hook'),
            )
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
           // ->default()->id('admin')->path('admin')->login()
            ->authMiddleware([
                Authenticate::class,
                CheckBusinessLicense::class,
            ]);
    }
}
