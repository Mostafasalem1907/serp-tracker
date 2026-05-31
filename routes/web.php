<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\AiAnalysisController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InstallController;
use App\Http\Controllers\KeywordController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\SettingsController;
use Illuminate\Support\Facades\Route;

// الصفحة الرئيسية → redirect للـ dashboard
Route::get('/', fn() => redirect()->route('dashboard'));

// ==============================
// مسارات First-Run Wizard
// ==============================
Route::prefix('install')->name('install.')->group(function () {
    Route::get('/', fn() => redirect()->route('install.step1'));
    Route::get('/step1', [InstallController::class, 'step1'])->name('step1');
    Route::get('/step2', [InstallController::class, 'step2'])->name('step2');
    Route::post('/test-database', [InstallController::class, 'testDatabase'])->name('test-database');
    Route::post('/step1', [InstallController::class, 'saveStep1'])->name('save-step1');
    Route::post('/complete', [InstallController::class, 'complete'])->name('complete');
});

// ==============================
// مسارات المصادقة (Breeze)
// ==============================
require __DIR__.'/auth.php';

// ==============================
// مسارات التطبيق الرئيسية
// ==============================
Route::middleware(['auth', 'active'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // الملف الشخصي
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // ==============================
    // العملاء — Admin و Member فقط
    // ==============================
    Route::middleware('admin')->group(function () {
        Route::resource('clients', ClientController::class);
    });

    // ==============================
    // المشاريع
    // ==============================
    Route::resource('projects', ProjectController::class);

    // ==============================
    // الكلمات المفتاحية
    // ==============================
    Route::prefix('projects/{project}/keywords')->name('keywords.')->group(function () {
        Route::post('/', [KeywordController::class, 'store'])->name('store');
        Route::post('/check-all', [KeywordController::class, 'checkProjectNow'])->name('check-all');
    });
    Route::delete('/keywords/{keyword}', [KeywordController::class, 'destroy'])->name('keywords.destroy');
    Route::post('/keywords/{keyword}/check', [KeywordController::class, 'checkNow'])->name('keywords.check');
    Route::get('/keywords/{keyword}/history', [KeywordController::class, 'history'])->name('keywords.history');

    // ==============================
    // الذكاء الاصطناعي
    // ==============================
    Route::post('/projects/{project}/ai-analyze', [AiAnalysisController::class, 'analyze'])->name('ai.analyze');
    Route::get('/projects/{project}/ai-history', [AiAnalysisController::class, 'history'])->name('ai.history');

    // ==============================
    // الإعدادات — Admin فقط
    // ==============================
    Route::middleware('admin')->prefix('settings')->name('settings.')->group(function () {
        Route::get('/', [SettingsController::class, 'index'])->name('index');
        Route::post('/general', [SettingsController::class, 'updateGeneral'])->name('general');
        Route::post('/apis', [SettingsController::class, 'updateApis'])->name('apis');
        Route::post('/test-dataforseo', [SettingsController::class, 'testDataForSeo'])->name('test-dataforseo');
        Route::post('/test-anthropic', [SettingsController::class, 'testAnthropic'])->name('test-anthropic');
        Route::post('/users', [SettingsController::class, 'storeUser'])->name('users.store');
        Route::put('/users/{user}', [SettingsController::class, 'updateUser'])->name('users.update');
        Route::post('/countries', [SettingsController::class, 'storeCountry'])->name('countries.store');
    });

    // ==============================
    // سجل الأنشطة — Admin فقط
    // ==============================
    Route::middleware('admin')->get('/activity-log', [ActivityLogController::class, 'index'])->name('activity-log.index');
});
