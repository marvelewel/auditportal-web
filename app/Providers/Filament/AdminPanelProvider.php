<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Dashboard;
use App\Filament\Pages\Auth\CustomLogin;
use App\Filament\Widgets\AuditKpiOverview;
use App\Filament\Widgets\AuditStatsOverview;
use App\Filament\Widgets\AuditStatusOverview;
use App\Filament\Widgets\DepartmentRiskChart;
use App\Filament\Widgets\FindingsAgingChart;
use App\Filament\Widgets\FindingsCategoryChart;
use App\Filament\Widgets\FindingsMonitoringTable;
use App\Filament\Widgets\FindingsTrendChart;
use App\Filament\Widgets\LatestFollowupsFeed;
use App\Filament\Widgets\MemoStatsCard;
use App\Filament\Widgets\OverdueFindingsAlert;
use App\Filament\Widgets\QuickLinksWidget;
use App\Filament\Widgets\TopAuditeePerformance;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\MaxWidth;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Illuminate\Support\HtmlString;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('portal')
            ->path('admin')
            ->login(CustomLogin::class)

            // ✅ 1. LOGO BRANDING (FIXED CONTRAST)
            ->brandName('Wismilak Audit')
            ->brandLogo(fn() => new HtmlString('
                <div class="fi-logo flex items-center gap-3">
                    <div style="
                        width: 42px; 
                        height: 42px; 
                        background: linear-gradient(135deg, #D4AF37 0%, #B8860B 100%); 
                        color: white; 
                        display: flex; 
                        align-items: center; 
                        justify-content: center; 
                        border-radius: 8px; 
                        font-family: \'Playfair Display\', serif; 
                        font-weight: 800; 
                        font-size: 24px;
                        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
                        border: 1px solid rgba(255,255,255,0.2);
                    ">
                        W
                    </div>
                    
                    <div class="flex flex-col leading-tight">
                        <span style="
                            font-family: \'Playfair Display\', serif; 
                            font-size: 20px; 
                            font-weight: 700; 
                            letter-spacing: 0.5px;
                            /* ✅ FIX: Gunakan class Tailwind untuk adaptasi Dark/Light Mode */
                            color: var(--c-brand-text); 
                        " class="brand-text-dynamic">
                            WISMILAK
                        </span>
                        <span style="
                            font-size: 11px; 
                            color: #D4AF37; 
                            text-transform: uppercase; 
                            letter-spacing: 2px; 
                            font-weight: 600;
                            margin-top: 2px;
                        ">
                            Audit Portal
                        </span>
                    </div>
                </div>
                
                <style>
                    /* ✅ CSS TRICK: Paksa warna teks menyesuaikan background sidebar */
                    /* Karena Sidebar kita Hijau (#1B4D3E), maka teks HARUS Putih di semua mode */
                    .brand-text-dynamic {
                        color: #ffffff !important;
                        text-shadow: 0 1px 2px rgba(0,0,0,0.3);
                    }
                </style>
            '))
            ->brandLogoHeight('4rem')

            // ✅ COLORS
            ->colors([
                'primary' => Color::hex('#1B4D3E'), // Hijau Wismilak
                'gold' => Color::hex('#D4AF37'), // Emas
                'gray' => Color::Slate,
            ])
            ->font('Inter')
            ->sidebarCollapsibleOnDesktop()
            ->databaseNotifications()
            ->maxContentWidth(MaxWidth::Full)

            // ============================================================
            // 2. INJECT HTML SIDEBAR (ONLY LOGIN PAGE)
            // ============================================================
            ->renderHook(
                'panels::body.start',
                function () {
                    if (!request()->routeIs('filament.portal.auth.*'))
                        return '';

                    return new HtmlString('
                        <div class="wismilak-login-sidebar">
                            <div class="sidebar-content">
                                <div class="brand-header">
                                    <div class="logo-box"><span>W</span></div>
                                    <div class="brand-text">
                                        <span class="company">WISMILAK</span>
                                        <span class="division">Internal Audit Portal</span>
                                    </div>
                                </div>
                                <div class="hero-section">
                                    <h1 class="hero-title">Sukses Bersama.</h1>
                                    <p class="hero-desc">Sistem manajemen audit terintegrasi untuk menjaga integritas, kualitas, dan keberlanjutan operasional perusahaan.</p>
                                </div>
                                <div class="sidebar-footer">
                                    <div class="gold-line"></div>
                                    <p>© 2025 PT Wismilak Inti Makmur Tbk.</p>
                                </div>
                            </div>
                            <div class="bg-overlay"></div>
                            <img src="https://images.unsplash.com/photo-1598155523122-38423bb4d6c1?q=80&w=2689&auto=format&fit=crop" class="bg-image">
                        </div>
                    ');
                }
            )

            // ============================================================
            // 3. INJECT CSS LOGIN (ONLY LOGIN PAGE)
            // ============================================================
            ->renderHook(
                'panels::head.end',
                function () {
                    if (!request()->routeIs('filament.portal.auth.*'))
                        return '';

                    return new HtmlString('
                        <style>
                            @import url("https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&display=swap");
                            .wismilak-login-sidebar { position: fixed; top: 0; left: 0; bottom: 0; width: 50vw; background-color: #1B4D3E; z-index: 9999; display: flex; flex-direction: column; overflow: hidden; }
                            .sidebar-content { position: relative; z-index: 10; height: 100%; padding: 4rem; display: flex; flex-direction: column; justify-content: space-between; color: white; }
                            .bg-image { position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: cover; z-index: 1; }
                            .bg-overlay { position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: linear-gradient(135deg, rgba(27, 77, 62, 0.95), rgba(20, 58, 45, 0.85)); z-index: 2; }
                            .logo-box { width: 48px; height: 48px; background: #D4AF37; color: white; font-family: "Playfair Display", serif; font-size: 28px; display: flex; align-items: center; justify-content: center; border-radius: 4px; }
                            .brand-header { display: flex; align-items: center; gap: 1rem; }
                            .brand-text { display: flex; flex-direction: column; }
                            .company { font-family: "Playfair Display", serif; font-size: 1.5rem; line-height: 1; }
                            .division { font-size: 0.75rem; color: #D4AF37; text-transform: uppercase; letter-spacing: 0.1em; margin-top: 4px; }
                            .hero-title { font-family: "Playfair Display", serif; font-size: 3.5rem; margin-bottom: 1rem; line-height: 1.1; }
                            .hero-desc { font-size: 1.1rem; color: #d1fae5; line-height: 1.6; max-width: 80%; }
                            .gold-line { width: 60px; height: 4px; background: #D4AF37; margin-bottom: 1rem; }
                            .fi-simple-layout { margin-left: 50vw !important; width: 50vw !important; min-height: 100vh !important; background-color: white !important; display: flex !important; align-items: center !important; justify-content: center !important; padding: 0 !important; }
                            .fi-simple-main-ctn { width: 100% !important; max-width: 480px !important; padding: 3rem !important; background: transparent !important; box-shadow: none !important; ring: 0 !important; }
                            .fi-simple-layout .fi-logo { display: none !important; }
                            .fi-btn-primary { background-color: #1B4D3E !important; font-weight: 600 !important; }
                            @media (max-width: 1023px) {
                                .wismilak-login-sidebar { display: none !important; }
                                .fi-simple-layout { margin-left: 0 !important; width: 100vw !important; }
                                .fi-simple-layout .fi-logo { display: block !important; color: #1B4D3E !important; margin-bottom: 2rem !important; }
                            }
                        </style>
                    ');
                }
            )

            // ============================================================
            // 4. INJECT CSS DASHBOARD (PREMIUM UI - FIXED VISIBILITY)
            // ============================================================
            ->renderHook(
                'panels::head.end',
                function () {
                    if (request()->routeIs('filament.portal.auth.*'))
                        return '';

                    return new HtmlString('
                        <style>
                            @import url("https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&display=swap");

                            /* A. SIDEBAR PREMIUM */
                            .fi-sidebar { background-color: #1B4D3E !important; border-right: 1px solid #D4AF37 !important; }
                            .fi-sidebar-nav .fi-sidebar-item-label { color: #f1f5f9 !important; font-weight: 400; }
                            .fi-sidebar-nav .fi-sidebar-item-icon { color: #94a3b8 !important; }
                            
                            /* Menu Aktif */
                            .fi-sidebar-item-active { background-color: rgba(212, 175, 55, 0.15) !important; border-left: 4px solid #D4AF37 !important; }
                            .fi-sidebar-item-active .fi-sidebar-item-label { color: #D4AF37 !important; font-weight: 700 !important; }
                            .fi-sidebar-item-active .fi-sidebar-item-icon { color: #D4AF37 !important; }
                            
                            .fi-sidebar-item:hover { background-color: rgba(255, 255, 255, 0.05); }
                            
                            /* Header Logo Area Fix */
                            .fi-sidebar-header {
                                background-color: #1B4D3E !important; /* Pastikan background selalu hijau */
                                border-bottom: 1px solid rgba(212, 175, 55, 0.3);
                                padding-top: 1.5rem;
                                padding-bottom: 1.5rem;
                                height: auto !important;
                            }
                            
                            /* Typography Headers */
                            .fi-header-heading { font-family: "Playfair Display", serif !important; color: #1B4D3E !important; font-size: 1.8rem !important; }
                            
                            /* Cards */
                            .fi-wi-stats-overview-stat-ctn { border-top: 3px solid #D4AF37 !important; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); }
                        </style>
                    ');
                }
            )

            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([Dashboard::class])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
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
            ->authMiddleware([Authenticate::class]);
    }
}