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
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LearnerLoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Api\CollectionController;
use App\Http\Controllers\Api\PreferenceController;
use App\Http\Controllers\Api\SavedSenseController;
use App\Http\Controllers\Api\SavedWordController;
use App\Http\Controllers\Api\WorkshopController;
use App\Http\Controllers\CollectionTestController;
use App\Http\Controllers\ExploreController;
use App\Http\Controllers\MyWordsController;
use App\Http\Controllers\MyWritingsController;
use Illuminate\Support\Facades\Route;

// ── Public ────────────────────────────────────────────────────────────────────

Route::get('/', fn () => redirect()->route('lexicon.index'));

// ── Learner auth ─────────────────────────────────────────────────────────────

Route::get('/register', [RegisterController::class, 'showForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register']);
Route::get('/login', [LearnerLoginController::class, 'showForm'])->name('login');
Route::post('/login', [LearnerLoginController::class, 'login']);
Route::post('/logout', [LearnerLoginController::class, 'logout'])->name('logout');

// Password reset
Route::get('/forgot-password', [ForgotPasswordController::class, 'showForm'])->name('password.request');
Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLink'])->name('password.email');
Route::get('/reset-password/{token}', [ResetPasswordController::class, 'showForm'])->name('password.reset');
Route::post('/reset-password', [ResetPasswordController::class, 'reset'])->name('password.update');

// ── Learner pages (auth required) ────────────────────────────────────────────

Route::get('/my-words', [MyWordsController::class, 'index'])->name('my-words')->middleware('auth');
Route::get('/my-writings', [MyWritingsController::class, 'index'])->name('my-writings')->middleware('auth');
Route::get('/my-words/test/{collection}', [CollectionTestController::class, 'show'])->name('my-words.test')->middleware('auth');

// ── Learner API (auth required) ──────────────────────────────────────────────

Route::middleware('auth')->prefix('api')->group(function () {
    Route::get('/preferences', [PreferenceController::class, 'index']);
    Route::patch('/preferences', [PreferenceController::class, 'update']);

    // Word-level saves (replaces sense-level)
    Route::get('/saved-words', [SavedWordController::class, 'index']);
    Route::post('/saved-words/{wordObjectId}', [SavedWordController::class, 'toggle']);
    Route::delete('/saved-words/{wordObjectId}', [SavedWordController::class, 'destroy']);
    Route::patch('/saved-words/{wordObjectId}/note', [SavedWordController::class, 'updateNote']);

    // Collections (word-level)
    Route::get('/collections', [CollectionController::class, 'index']);
    Route::post('/collections', [CollectionController::class, 'store']);
    Route::patch('/collections/{collection}', [CollectionController::class, 'update']);
    Route::delete('/collections/{collection}', [CollectionController::class, 'destroy']);
    Route::post('/collections/{collection}/words/{wordObjectId}', [CollectionController::class, 'addWord']);
    Route::delete('/collections/{collection}/words/{wordObjectId}', [CollectionController::class, 'removeWord']);

    // Workshop (造句) — save & delete require auth
    Route::post('/workshop/save-example', [WorkshopController::class, 'saveExample']);
    Route::delete('/workshop/saved-example/{id}', [WorkshopController::class, 'deleteExample']);

    // Fluency level (profile setting for 師父)
    Route::put('/user/fluency-level', [WorkshopController::class, 'updateFluencyLevel']);

    // Collection testing
    Route::post('/collection-tests', [CollectionTestController::class, 'store']);
    Route::post('/collection-tests/{test}/answers', [CollectionTestController::class, 'storeAnswer']);
    Route::post('/collection-tests/{test}/complete', [CollectionTestController::class, 'complete']);
    Route::post('/collection-tests/usage-check', [CollectionTestController::class, 'usageCheck']);
});

// Workshop AI proxy — open to guests so they can try before signing up
// Rate-limited: 10 requests/minute per IP to prevent abuse
Route::prefix('api')->middleware('throttle:10,1')->group(function () {
    Route::post('/workshop/critique', [WorkshopController::class, 'critique']);
    Route::post('/workshop/generate', [WorkshopController::class, 'generate']);
});

// ── Lexicon explorer (public, no auth) ───────────────────────────────────────

Route::get('/lexicon', [ExploreController::class, 'index'])->name('lexicon.index');
Route::get('/lexicon/{smartId}', [ExploreController::class, 'show'])->name('lexicon.show');
Route::get('/api/lexicon/related-words/{character}', [ExploreController::class, 'relatedWords'])->name('lexicon.relatedWords');

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
