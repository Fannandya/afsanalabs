<?php

use App\Http\Controllers\Api\Admin\AboutTimelineController;
use App\Http\Controllers\Api\Admin\AuthController;
use App\Http\Controllers\Api\Admin\BusinessSettingController;
use App\Http\Controllers\Api\Admin\CategoryController;
use App\Http\Controllers\Api\Admin\ClientLogoController;
use App\Http\Controllers\Api\Admin\FaqController;
use App\Http\Controllers\Api\Admin\FooterLegalLinkController;
use App\Http\Controllers\Api\Admin\HeroController;
use App\Http\Controllers\Api\Admin\MessageController;
use App\Http\Controllers\Api\Admin\MockupOfferController;
use App\Http\Controllers\Api\Admin\NavLinkController;
use App\Http\Controllers\Api\Admin\NotificationController;
use App\Http\Controllers\Api\Admin\ObjectionQuestionController;
use App\Http\Controllers\Api\Admin\OrderController;
use App\Http\Controllers\Api\Admin\PackageController;
use App\Http\Controllers\Api\Admin\PasswordController;
use App\Http\Controllers\Api\Admin\PortfolioController as AdminPortfolioController;
use App\Http\Controllers\Api\Admin\ProcessStepController;
use App\Http\Controllers\Api\Admin\ReferencePriceCardController;
use App\Http\Controllers\Api\Admin\SectionHeaderController;
use App\Http\Controllers\Api\Admin\SeoSettingController;
use App\Http\Controllers\Api\Admin\ServiceController;
use App\Http\Controllers\Api\Admin\TeamMemberController;
use App\Http\Controllers\Api\Admin\TestimonialController;
use App\Http\Controllers\Api\Admin\UploadController;
use App\Http\Controllers\Api\Admin\ValuePropController;
use App\Http\Controllers\Api\Public\FormController;
use App\Http\Controllers\Api\Public\HomeController;
use App\Http\Controllers\Api\Public\OrderTrackController;
use App\Http\Controllers\Api\Public\PortfolioController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // Fallback bernama agar guard API tanpa sesi balas 401 JSON, bukan redirect error.
    Route::get('/admin/unauthenticated', fn () => response()->json(['error' => ['message' => 'Belum masuk. Silakan login dulu.']], 401))->name('login');

    // Publik (tanpa login)
    Route::get('/home', [HomeController::class, 'index']);
    Route::get('/portfolios', [PortfolioController::class, 'index']);
    Route::get('/categories', [PortfolioController::class, 'categories']);
    Route::get('/testimonials', [PortfolioController::class, 'testimonials']);
    Route::middleware('throttle:10,1')->group(function () {
        Route::post('/contact', [FormController::class, 'storeContact']);
        Route::post('/consultations', [FormController::class, 'storeConsultation']);
        Route::post('/orders', [FormController::class, 'storeOrder']);
        Route::post('/mockup-requests', [FormController::class, 'storeMockup']);
    });
    Route::get('/orders/track/{trackingCode}', [OrderTrackController::class, 'show']);

    // Auth + admin butuh sesi cookie Sanctum → bungkus middleware web.
    Route::middleware('web')->group(function () {
        // Auth (throttle 10/menit)
        Route::middleware('throttle:10,1')->group(function () {
            Route::post('/admin/login', [AuthController::class, 'login']);
            Route::post('/admin/forgot-password', [AuthController::class, 'forgot']);
            Route::post('/admin/reset-password', [AuthController::class, 'reset']);
        });
        Route::post('/admin/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

        // Admin (wajib auth:sanctum)
        Route::prefix('admin')->middleware('auth:sanctum')->group(function () {
            Route::apiResource('value-props', ValuePropController::class)->except(['edit', 'create'])->parameters(['value-props' => 'id']);
            Route::apiResource('process-steps', ProcessStepController::class)->except(['edit', 'create'])->parameters(['process-steps' => 'id']);
            Route::apiResource('objection-questions', ObjectionQuestionController::class)->except(['edit', 'create'])->parameters(['objection-questions' => 'id']);
            Route::apiResource('reference-price-cards', ReferencePriceCardController::class)->except(['edit', 'create'])->parameters(['reference-price-cards' => 'id']);
            Route::apiResource('services', ServiceController::class)->except(['edit', 'create'])->parameters(['services' => 'id']);
            Route::apiResource('categories', CategoryController::class)->except(['edit', 'create'])->parameters(['categories' => 'id']);
            Route::apiResource('portfolios', AdminPortfolioController::class)->except(['edit', 'create'])->parameters(['portfolios' => 'id']);
            Route::apiResource('testimonials', TestimonialController::class)->except(['edit', 'create'])->parameters(['testimonials' => 'id']);
            Route::apiResource('packages', PackageController::class)->except(['edit', 'create'])->parameters(['packages' => 'id']);
            Route::apiResource('team-members', TeamMemberController::class)->except(['edit', 'create'])->parameters(['team-members' => 'id']);
            Route::apiResource('client-logos', ClientLogoController::class)->except(['edit', 'create'])->parameters(['client-logos' => 'id']);
            Route::apiResource('about-timeline', AboutTimelineController::class)->except(['edit', 'create'])->parameters(['about-timeline' => 'id']);
            Route::apiResource('faqs', FaqController::class)->except(['edit', 'create'])->parameters(['faqs' => 'id']);
            Route::apiResource('nav-links', NavLinkController::class)->except(['edit', 'create'])->parameters(['nav-links' => 'id']);
            Route::apiResource('footer-legal-links', FooterLegalLinkController::class)->except(['edit', 'create'])->parameters(['footer-legal-links' => 'id']);

            Route::get('/hero', [HeroController::class, 'show']);
            Route::patch('/hero', [HeroController::class, 'update']);
            Route::get('/mockup-offer', [MockupOfferController::class, 'show']);
            Route::patch('/mockup-offer', [MockupOfferController::class, 'update']);
            Route::get('/business-settings', [BusinessSettingController::class, 'show']);
            Route::patch('/business-settings', [BusinessSettingController::class, 'update']);
            Route::get('/seo-settings', [SeoSettingController::class, 'show']);
            Route::patch('/seo-settings', [SeoSettingController::class, 'update']);

            Route::get('/section-headers', [SectionHeaderController::class, 'index']);
            Route::patch('/section-headers/{sectionKey}', [SectionHeaderController::class, 'update']);

            Route::get('/contact-messages', [MessageController::class, 'contactIndex']);
            Route::patch('/contact-messages/{id}/status', [MessageController::class, 'contactStatus']);
            Route::get('/consultations', [MessageController::class, 'consultationIndex']);
            Route::patch('/consultations/{id}/status', [MessageController::class, 'consultationStatus']);

            Route::get('/orders', [OrderController::class, 'index']);
            Route::get('/orders/{id}', [OrderController::class, 'show']);
            Route::patch('/orders/{id}/status', [OrderController::class, 'updateStatus']);

            Route::get('/notifications', [NotificationController::class, 'index']);
            Route::patch('/notifications/{id}/read', [NotificationController::class, 'markRead']);
            Route::get('/notifications/preferences', [NotificationController::class, 'showPreferences']);
            Route::patch('/notifications/preferences', [NotificationController::class, 'updatePreferences']);

            Route::patch('/password', [PasswordController::class, 'update']);
            Route::post('/uploads', [UploadController::class, 'store']);
        });
    });
});
