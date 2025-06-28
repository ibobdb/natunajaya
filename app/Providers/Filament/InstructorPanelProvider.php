<?php

namespace App\Providers\Filament;

use App\Filament\Instructor\Widgets\InstructorStats;
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

class InstructorPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('instructor')
            ->path('instructor')
            ->login(false)
            ->registration(false)
            ->sidebarWidth('w-72')
            ->font('Poppins')
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->colors([
                'primary' => [
                    50 => '224, 231, 255',  // #e0e7ff
                    100 => '199, 210, 254', // #c7d2fe
                    200 => '165, 180, 252', // #a5b4fc
                    300 => '129, 140, 248', // #818cf8
                    400 => '99, 102, 241',  // #6366f1
                    500 => '79, 70, 229',   // #4f46e5
                    600 => '67, 56, 202',   // #4338ca
                    700 => '55, 48, 163',   // #3730a3
                    800 => '49, 46, 129',   // #312e81
                    900 => '30, 27, 75',    // #1e1b4b
                ],

            ])
            ->discoverResources(in: app_path('Filament/Instructor/Resources'), for: 'App\\Filament\\Instructor\\Resources')
            ->discoverPages(in: app_path('Filament/Instructor/Pages'), for: 'App\\Filament\\Instructor\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Instructor/Widgets'), for: 'App\\Filament\\Instructor\\Widgets')
            ->widgets([
                InstructorStats::class,
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
