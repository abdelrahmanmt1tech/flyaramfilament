<?php

namespace App\Providers\Filament;

use App\Http\Middleware\SetLocale;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Filament\View\PanelsRenderHook;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Illuminate\Support\Facades\Blade;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->databaseNotifications()
            ->default()
            ->id('admin')
            ->path('admin')
            ->profile()
            ->spa()
            ->registration()
            ->login()
            ->colors([
                'primary' => Color::Amber,
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
                SetLocale::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->globalSearch(false)
            ->renderHook(
                PanelsRenderHook::GLOBAL_SEARCH_AFTER,
                fn (): string => Blade::render(<<<'HTML'
                    <div style="display: flex; align-items: center; gap: 4px; margin-left: 1rem; background: rgba(0,0,0,0.05); border-radius: 12px; padding: 4px;">
                        <a href="{{ url('lang/ar') }}" 
                           style="display: flex; align-items: center; gap: 8px; padding: 10px 20px; font-size: 14px; font-weight: 600; border-radius: 8px; text-decoration: none; transition: all 0.2s;
                                  {{ app()->getLocale() == 'ar' 
                                     ? 'background: white; color: #f59e0b; box-shadow: 0 1px 3px rgba(0,0,0,0.1);' 
                                     : 'color: #6b7280;' }}"
                           onmouseover="if('{{ app()->getLocale() }}' != 'ar') this.style.color='#111827'"
                           onmouseout="if('{{ app()->getLocale() }}' != 'ar') this.style.color='#6b7280'">
                            <span style="font-size: 18px;">🇸🇦</span>
                            <span>عربي</span>
                        </a>
                        
                        <a href="{{ url('lang/en') }}" 
                           style="display: flex; align-items: center; gap: 8px; padding: 10px 20px; font-size: 14px; font-weight: 600; border-radius: 8px; text-decoration: none; transition: all 0.2s;
                                  {{ app()->getLocale() == 'en' 
                                     ? 'background: white; color: #f59e0b; box-shadow: 0 1px 3px rgba(0,0,0,0.1);' 
                                     : 'color: #6b7280;' }}"
                           onmouseover="if('{{ app()->getLocale() }}' != 'en') this.style.color='#111827'"
                           onmouseout="if('{{ app()->getLocale() }}' != 'en') this.style.color='#6b7280'">
                            <span style="font-size: 18px;">🇬🇧</span>
                            <span>English</span>
                        </a>
                    </div>
                HTML)
            );
    }
}