<?php

use App\Http\Controllers\Admin\AttributeSettingController;
use App\Http\Controllers\Admin\BadgeController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\LoginController;
use App\Http\Controllers\Admin\PreferencesController;
use App\Http\Controllers\Admin\WordObjectController;
use App\Http\Controllers\Admin\WordPronunciationController;
use App\Http\Controllers\Admin\WordSenseController;
use App\Http\Controllers\Admin\WordSenseExampleController;
use App\Http\Controllers\ExploreController;
use Illuminate\Support\Facades\Route;

// ── Public ────────────────────────────────────────────────────────────────────

Route::get('/', fn () => redirect()->route('admin.login'));

// ── Lexicon explorer (public, no auth) ───────────────────────────────────────

Route::get('/lexicon', [ExploreController::class, 'index'])->name('lexicon.index');
Route::get('/lexicon/{smartId}', [ExploreController::class, 'show'])->name('lexicon.show');

// ── Admin auth ────────────────────────────────────────────────────────────────

Route::get('/admin/login', [LoginController::class, 'showLogin'])->name('admin.login');
Route::post('/admin/login', [LoginController::class, 'login'])->name('admin.login.post');
Route::post('/admin/logout', [LoginController::class, 'logout'])->name('admin.logout');

// ── Admin panel (auth + role check) ──────────────────────────────────────────

Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {

    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Preferences
    Route::get('preferences', [PreferencesController::class, 'show'])->name('preferences');
    Route::post('preferences', [PreferencesController::class, 'update'])->name('preferences.update');

    // Attribute Settings
    Route::get('attribute-settings', [AttributeSettingController::class, 'index'])->name('attribute-settings.index');
    Route::patch('attribute-settings/{attribute}', [AttributeSettingController::class, 'update'])->name('attribute-settings.update');

    // Badges -------------------------------------------------------------------
    Route::get('badges', [BadgeController::class, 'index'])->name('badges.index');
    Route::get('badges/create', [BadgeController::class, 'create'])->name('badges.create');
    Route::post('badges', [BadgeController::class, 'store'])->name('badges.store');
    Route::get('badges/{badge}/edit', [BadgeController::class, 'edit'])->name('badges.edit');
    Route::put('badges/{badge}', [BadgeController::class, 'update'])->name('badges.update');
    Route::patch('badges/{badge}/toggle', [BadgeController::class, 'toggleActive'])->name('badges.toggle');

    // Words (WordObject) -------------------------------------------------------
    Route::get('words', [WordObjectController::class, 'index'])->name('words.index');
    Route::get('words/export', [WordObjectController::class, 'export'])->name('words.export');
    Route::get('words/create', [WordObjectController::class, 'create'])->name('words.create');
    Route::post('words', [WordObjectController::class, 'store'])->name('words.store');
    Route::get('words/{word}', [WordObjectController::class, 'show'])->name('words.show');
    Route::get('words/{word}/edit', [WordObjectController::class, 'edit'])->name('words.edit');
    Route::put('words/{word}', [WordObjectController::class, 'update'])->name('words.update');
    Route::patch('words/{word}/status', [WordObjectController::class, 'updateStatus'])->name('words.status');

    // Pronunciations (nested under word) ---------------------------------------
    Route::post('words/{word}/pronunciations', [WordPronunciationController::class, 'store'])
        ->name('words.pronunciations.store');
    Route::delete('words/{word}/pronunciations/{pronunciation}', [WordPronunciationController::class, 'destroy'])
        ->name('words.pronunciations.destroy');

    // Senses (nested under word) -----------------------------------------------
    Route::get('words/{word}/senses/create', [WordSenseController::class, 'create'])
        ->name('words.senses.create');
    Route::post('words/{word}/senses', [WordSenseController::class, 'store'])
        ->name('words.senses.store');
    Route::get('words/{word}/senses/{sense}/edit', [WordSenseController::class, 'edit'])
        ->name('words.senses.edit');
    Route::put('words/{word}/senses/{sense}', [WordSenseController::class, 'update'])
        ->name('words.senses.update');
    Route::patch('senses/{sense}/status', [WordSenseController::class, 'updateStatus'])
        ->name('senses.status');

    // Examples (nested under sense) --------------------------------------------
    Route::post('senses/{sense}/examples', [WordSenseExampleController::class, 'store'])
        ->name('senses.examples.store');
    Route::put('examples/{example}', [WordSenseExampleController::class, 'update'])
        ->name('examples.update');
    Route::delete('examples/{example}', [WordSenseExampleController::class, 'destroy'])
        ->name('examples.destroy');
});
