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

            // ✅ 1. LOGO BRANDING (USING WISMILAK LOGO IMAGE)
            ->brandName('Wismilak Audit')
            ->brandLogo(fn() => new HtmlString('
                <div class="fi-logo flex items-center gap-3">
                    <img 
                        src="/images/wismilak-logo.png" 
                        alt="Wismilak Logo"
                        style="
                            width: 50px; 
                            height: auto;
                        "
                    >
                    
                    <div class="flex flex-col leading-tight">
                        <span style="
                            font-family: \'Playfair Display\', serif; 
                            font-size: 18px; 
                            font-weight: 700; 
                            letter-spacing: 0.5px;
                            color: var(--c-brand-text); 
                        " class="brand-text-dynamic">
                            Audit Portal
                        </span>
                    </div>
                </div>
                
                <style>
                    .brand-text-dynamic {
                        color: #ffffff !important;
                        text-shadow: 0 1px 2px rgba(0,0,0,0.3);
                    }
                </style>
            '))
            ->brandLogoHeight('4rem')

            // ✅ COLORS - WISMILAK OFFICIAL PALETTE
            ->colors([
                'primary' => Color::hex('#1A4D2E'), // --darkest-green-wismilak
                'gold' => Color::hex('#C4A901'),    // --wismilak-gold
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
                                    <img src="/images/wismilak-logo.png" alt="Wismilak Logo" class="login-logo">
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
                        <link rel="preconnect" href="https://fonts.googleapis.com">
                        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
                        <link href="https://fonts.googleapis.com/css2?family=Marcellus&family=Noto+Sans+Display:wght@400;500;600;700&display=swap" rel="stylesheet">
                        <style>
                            .wismilak-login-sidebar { position: fixed; top: 0; left: 0; bottom: 0; width: 50vw; background-color: #1A4D2E; z-index: 9999; display: flex; flex-direction: column; overflow: hidden; }
                            .sidebar-content { position: relative; z-index: 10; height: 100%; padding: 4rem; display: flex; flex-direction: column; justify-content: space-between; color: white; font-family: "Noto Sans Display", sans-serif; }
                            .bg-image { position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: cover; z-index: 1; }
                            .bg-overlay { position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: linear-gradient(135deg, rgba(26, 77, 46, 0.95), rgba(20, 58, 35, 0.85)); z-index: 2; }
                            .login-logo { width: 70px; height: auto; }
                            .brand-header { display: flex; align-items: center; gap: 1rem; }
                            .brand-text { display: flex; flex-direction: column; }
                            .company { font-family: "Marcellus", serif; font-size: 1.5rem; line-height: 1; }
                            .division { font-size: 0.75rem; color: #C4A901; text-transform: uppercase; letter-spacing: 0.1em; margin-top: 4px; font-family: "Noto Sans Display", sans-serif; }
                            .hero-title { font-family: "Marcellus", serif; font-size: 3.5rem; margin-bottom: 1rem; line-height: 1.1; }
                            .hero-desc { font-size: 1.1rem; color: #d1fae5; line-height: 1.6; max-width: 80%; font-family: "Noto Sans Display", sans-serif; }
                            .gold-line { width: 60px; height: 4px; background: #C4A901; margin-bottom: 1rem; }
                            .fi-simple-layout { margin-left: 50vw !important; width: 50vw !important; min-height: 100vh !important; background-color: #FAF9F6 !important; display: flex !important; align-items: center !important; justify-content: center !important; padding: 0 !important; }
                            .fi-simple-main-ctn { width: 100% !important; max-width: 480px !important; padding: 3rem !important; background: transparent !important; box-shadow: none !important; ring: 0 !important; }
                            .fi-simple-layout .fi-logo { display: none !important; }
                            .fi-btn-primary { background-color: #1A4D2E !important; font-weight: 600 !important; transition: all 0.5s ease !important; }
                            .fi-btn-primary:hover { background-color: #153d24 !important; }
                            body { font-family: "Noto Sans Display", sans-serif !important; }
                            @media (max-width: 1023px) {
                                .wismilak-login-sidebar { display: none !important; }
                                .fi-simple-layout { margin-left: 0 !important; width: 100vw !important; }
                                .fi-simple-layout .fi-logo { display: block !important; color: #1A4D2E !important; margin-bottom: 2rem !important; }
                            }
                        </style>
                    ');
                }
            )

            // ============================================================
            // 4. INJECT CSS DASHBOARD (WISMILAK OFFICIAL STYLE)
            // ============================================================
            ->renderHook(
                'panels::head.end',
                function () {
                    if (request()->routeIs('filament.portal.auth.*'))
                        return '';

                    return new HtmlString('
                        <link rel="preconnect" href="https://fonts.googleapis.com">
                        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
                        <link href="https://fonts.googleapis.com/css2?family=Marcellus&family=Noto+Sans+Display:wght@400;500;600;700&display=swap" rel="stylesheet">
                        <style>
                            /* ============================================
                               A. COLOR PALETTE WISMILAK
                               ============================================ */
                            :root {
                                --wismilak-green: #1A4D2E;
                                --wismilak-gold: #C4A901;
                                --wismilak-bg: #FAF9F6;
                                --wismilak-white: #FFFFFF;
                            }

                            /* ============================================
                               B. BACKGROUND UTAMA
                               ============================================ */
                            .fi-body { background-color: var(--wismilak-bg) !important; }
                            .fi-main { background-color: var(--wismilak-bg) !important; }
                            .dark .fi-body { background-color: #111827 !important; }
                            .dark .fi-main { background-color: #111827 !important; }

                            /* ============================================
                               C. TYPOGRAPHY - MARCELLUS & NOTO SANS
                               ============================================ */
                            /* Judul - Marcellus (Serif) */
                            .fi-header-heading,
                            .fi-section-header-heading,
                            h1, h2, h3 {
                                font-family: "Marcellus", serif !important;
                            }

                            /* Body & Data - Noto Sans Display */
                            body,
                            .fi-sidebar-item-label,
                            .fi-wi-stats-overview-stat-value,
                            .fi-wi-stats-overview-stat-description,
                            .fi-ta-text,
                            p, span, td, th, label, input, select, textarea {
                                font-family: "Noto Sans Display", sans-serif !important;
                            }

                            /* ============================================
                               D. SIDEBAR WISMILAK
                               ============================================ */
                            .fi-sidebar { 
                                background-color: var(--wismilak-green) !important; 
                                border-right: 1px solid var(--wismilak-gold) !important; 
                            }
                            .fi-sidebar-nav .fi-sidebar-item-label { 
                                color: #f1f5f9 !important; 
                                font-weight: 400; 
                            }
                            .fi-sidebar-nav .fi-sidebar-item-icon { 
                                color: #94a3b8 !important; 
                            }
                            
                            /* Menu Aktif - Semi Round */
                            .fi-sidebar-item-active { 
                                background-color: rgba(196, 169, 1, 0.15) !important; 
                                border-left: 4px solid var(--wismilak-gold) !important;
                                border-radius: 0 20px 20px 0 !important;
                            }
                            .fi-sidebar-item-active .fi-sidebar-item-label { 
                                color: var(--wismilak-gold) !important; 
                                font-weight: 700 !important; 
                            }
                            .fi-sidebar-item-active .fi-sidebar-item-icon { 
                                color: var(--wismilak-gold) !important; 
                            }
                            
                            /* Hover Effect */
                            .fi-sidebar-item:hover { 
                                background-color: rgba(255, 255, 255, 0.05); 
                            }
                            
                            /* Garis Pemisah Menu */
                            .fi-sidebar-group + .fi-sidebar-group {
                                border-top: 1px solid rgba(255, 255, 255, 0.1);
                                padding-top: 0.75rem;
                                margin-top: 0.75rem;
                            }
                            
                            /* Header Logo Area */
                            .fi-sidebar-header {
                                background-color: var(--wismilak-green) !important;
                                border-bottom: 1px solid rgba(196, 169, 1, 0.3);
                                padding-top: 1.5rem;
                                padding-bottom: 1.5rem;
                                height: auto !important;
                            }

                            /* ============================================
                               E. TYPOGRAPHY HEADERS
                               ============================================ */
                            .fi-header-heading { 
                                font-family: "Marcellus", serif !important; 
                                color: var(--wismilak-green) !important; 
                                font-size: 1.8rem !important; 
                            }
                            
                            /* ============================================
                               F. CARDS & COMPONENTS (RADIUS 20px)
                               ============================================ */
                            /* Stats Cards */
                            .fi-wi-stats-overview-stat { 
                                border-radius: 20px !important;
                                border-top: 3px solid var(--wismilak-gold) !important;
                                box-shadow: 0 2px 8px rgba(50, 50, 50, 0.08) !important;
                                transition: all 0.3s ease !important;
                            }
                            .fi-wi-stats-overview-stat:hover {
                                box-shadow: 0 4px 12px rgba(50, 50, 50, 0.12) !important;
                                transform: translateY(-2px);
                            }

                            /* Section Cards */
                            .fi-section {
                                border-radius: 20px !important;
                                box-shadow: 0 2px 8px rgba(50, 50, 50, 0.08) !important;
                            }

                            /* Stat Labels - Marcellus */
                            .fi-wi-stats-overview-stat-label {
                                font-family: "Marcellus", serif !important;
                                font-size: 0.95rem !important;
                            }

                            /* ============================================
                               G. BUTTONS PRIMARY
                               ============================================ */
                            .fi-btn-primary {
                                background-color: var(--wismilak-green) !important;
                                transition: all 0.5s ease !important;
                            }
                            .fi-btn-primary:hover {
                                background-color: #153d24 !important;
                            }

                            /* ============================================
                               H. QUICK LINKS BUTTONS
                               ============================================ */
                            .quick-link-btn {
                                background-color: var(--wismilak-green) !important;
                                color: white !important;
                                transition: all 0.5s ease !important;
                                border-radius: 12px !important;
                            }
                            .quick-link-btn:hover {
                                background-color: #153d24 !important;
                            }
                            .quick-link-btn .arrow-indicator {
                                opacity: 0;
                                transform: translateX(-10px);
                                transition: all 0.3s ease;
                            }
                            .quick-link-btn:hover .arrow-indicator {
                                opacity: 1;
                                transform: translateX(0);
                            }
                        </style>
                    ');
                }
            )

            // ============================================================
            // 5. FILE VIEWER MODAL (GLOBAL)
            // ============================================================
            ->renderHook(
                'panels::body.end',
                fn() => view('livewire.file-viewer-modal-wrapper')
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