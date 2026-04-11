<?php

use App\Http\Controllers\Admin\AttributeSettingController;
use App\Http\Controllers\Admin\BadgeController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\LoginController;
use App\Http\Controllers\Admin\PreferencesController;
use App\Http\Controllers\Admin\SearchLogController;
use App\Http\Controllers\Admin\SearchNotFoundController;
use App\Http\Controllers\Admin\ShifuEngagementController;
use App\Http\Controllers\Admin\CsvImportController;
use App\Http\Controllers\Admin\WordObjectController;
use App\Http\Controllers\Admin\WordPronunciationController;
use App\Http\Controllers\Admin\WordSenseController;
use App\Http\Controllers\Admin\GrammarPatternController;
use App\Http\Controllers\Admin\GrammarPatternExampleController;
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
use App\Http\Controllers\Api\ChineseNameController;
use App\Http\Controllers\ExploreController;
use App\Http\Controllers\LearnerDashboardController;
use App\Http\Controllers\MyActivityController;
use App\Http\Controllers\MyWordsController;
use App\Http\Controllers\MyWritingsController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ProfileController;
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

// ── Public feature pages ─────────────────────────────────────────────────────

Route::get('/chinese-names', [PageController::class, 'chineseNames'])->name('chinese-names');
Route::get('/translation', [PageController::class, 'translation'])->name('translation');
Route::get('/idioms', [PageController::class, 'idioms'])->name('idioms');
Route::get('/help', [PageController::class, 'help'])->name('help');

// ── Learner pages (auth required) ────────────────────────────────────────────

Route::get('/dashboard', [LearnerDashboardController::class, 'index'])->name('dashboard')->middleware('auth');
Route::get('/my-words', [MyWordsController::class, 'index'])->name('my-words')->middleware('auth');
Route::get('/my-writings', [MyWritingsController::class, 'index'])->name('my-writings')->middleware('auth');
Route::patch('/my-writings/{id}/visibility', [MyWritingsController::class, 'toggleVisibility'])->middleware('auth')->name('my-writings.visibility');
Route::get('/my-activity', [MyActivityController::class, 'index'])->name('my-activity')->middleware('auth');
Route::get('/my-words/test/{collection}', [CollectionTestController::class, 'show'])->name('my-words.test')->middleware('auth');
Route::get('/profile', [ProfileController::class, 'show'])->name('profile')->middleware('auth');
Route::patch('/profile/pll-name', [ProfileController::class, 'updatePllName'])->middleware('auth');
Route::patch('/profile/chinese-name', [ProfileController::class, 'updateChineseName'])->middleware('auth');
Route::patch('/profile/shifu-persona', [ProfileController::class, 'updateShifuPersona'])->middleware('auth')->name('profile.shifu-persona');
Route::patch('/profile/community-privacy', [ProfileController::class, 'updateCommunityPrivacy'])->middleware('auth')->name('profile.community-privacy');

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
    Route::post('/collections/{collection}/import', [CollectionController::class, 'importWords']);
    Route::post('/collections/{collection}/build', [CollectionController::class, 'build']);
    Route::delete('/collections/{collection}/words/{wordObjectId}', [CollectionController::class, 'removeWord']);

    // Workshop (造句) — save & delete require auth
    Route::post('/workshop/save-example', [WorkshopController::class, 'saveExample']);
    Route::delete('/workshop/saved-example/{id}', [WorkshopController::class, 'deleteExample']);

    // Fluency level (profile setting for 師父)
    Route::put('/user/fluency-level', [WorkshopController::class, 'updateFluencyLevel']);

    // Dashboard daily message
    Route::post('/dashboard/daily-message', [LearnerDashboardController::class, 'generateDailyMessage']);
    Route::patch('/dashboard/daily-message/feedback', [LearnerDashboardController::class, 'feedbackDailyMessage']);

    // Word learning progress
    Route::post('/word-progress/{wordObjectId}/learned', [CollectionTestController::class, 'markLearned']);

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
    Route::post('/workshop/analyze', [WorkshopController::class, 'analyze']);
    Route::post('/chinese-names/generate', [ChineseNameController::class, 'generate']);
});

// Chinese Names — choose requires auth
Route::post('/api/chinese-names/choose', [ChineseNameController::class, 'choose'])->middleware('auth');

// ── Lexicon explorer (public, no auth) ───────────────────────────────────────

Route::get('/lexicon', [ExploreController::class, 'index'])->name('lexicon.index');
Route::get('/lexicon/{smartId}', [ExploreController::class, 'show'])->name('lexicon.show');
Route::get('/api/lexicon/related-words/{character}', [ExploreController::class, 'relatedWords'])->name('lexicon.relatedWords');
Route::post('/api/lexicon/search-log', [ExploreController::class, 'logSearch'])->name('lexicon.searchLog');

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
    Route::get('words/csv-import', [CsvImportController::class, 'showUpload'])->name('words.csv-import');
    Route::post('words/csv-import/process', [CsvImportController::class, 'process'])->name('words.csv-import.process');
    Route::post('words/csv-import/enrich', [CsvImportController::class, 'enrichWord'])->name('words.csv-import.enrich');
    Route::post('words/csv-import/save-word', [CsvImportController::class, 'saveWord'])->name('words.csv-import.save-word');
    Route::get('words/csv-import/next', [CsvImportController::class, 'processNext'])->name('words.csv-import.next');
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
    Route::delete('words/{word}/senses/{sense}', [WordSenseController::class, 'destroy'])
        ->name('words.senses.destroy');
    Route::patch('senses/{sense}/status', [WordSenseController::class, 'updateStatus'])
        ->name('senses.status');

    // Examples (nested under sense) --------------------------------------------
    Route::post('senses/{sense}/examples', [WordSenseExampleController::class, 'store'])
        ->name('senses.examples.store');
    Route::put('examples/{example}', [WordSenseExampleController::class, 'update'])
        ->name('examples.update');
    Route::delete('examples/{example}', [WordSenseExampleController::class, 'destroy'])
        ->name('examples.destroy');

    // Grammar Patterns -----------------------------------------------------------
    Route::get('grammar', [GrammarPatternController::class, 'index'])->name('grammar.index');
    Route::get('grammar/create', [GrammarPatternController::class, 'create'])->name('grammar.create');
    Route::post('grammar', [GrammarPatternController::class, 'store'])->name('grammar.store');
    Route::get('grammar/{pattern}', [GrammarPatternController::class, 'show'])->name('grammar.show');
    Route::get('grammar/{pattern}/edit', [GrammarPatternController::class, 'edit'])->name('grammar.edit');
    Route::put('grammar/{pattern}', [GrammarPatternController::class, 'update'])->name('grammar.update');
    Route::delete('grammar/{pattern}', [GrammarPatternController::class, 'destroy'])->name('grammar.destroy');
    Route::patch('grammar/{pattern}/status', [GrammarPatternController::class, 'updateStatus'])->name('grammar.status');

    // 師父 enrichment (AJAX — returns JSON preview, nothing persisted)
    Route::post('grammar/{pattern}/enrich', [GrammarPatternController::class, 'enrich'])
        ->name('grammar.enrich');

    // 師父 enrichment from raw seed fields (create form / pre-save)
    Route::post('grammar/enrich-seed', [GrammarPatternController::class, 'enrichSeed'])
        ->name('grammar.enrich-seed');

    // 師父 enrichment applied in one shot (used by draft queue step-through)
    Route::post('grammar/{pattern}/apply-enrichment', [GrammarPatternController::class, 'applyEnrichment'])
        ->name('grammar.apply-enrichment');

    // Draft enrichment queue — step-through all drafts needing notes/examples
    Route::get('grammar-queue', [GrammarPatternController::class, 'queue'])
        ->name('grammar.queue');

    // Grammar Pattern Examples
    Route::post('grammar/{pattern}/examples', [GrammarPatternExampleController::class, 'store'])
        ->name('grammar.examples.store');
    Route::put('grammar-examples/{example}', [GrammarPatternExampleController::class, 'update'])
        ->name('grammar.examples.update');
    Route::delete('grammar-examples/{example}', [GrammarPatternExampleController::class, 'destroy'])
        ->name('grammar.examples.destroy');

    // Grammar Suggestion actions
    Route::post('grammar/suggestions/{suggestion}/accept', [GrammarPatternController::class, 'acceptSuggestion'])
        ->name('grammar.suggestions.accept');
    Route::post('grammar/suggestions/{suggestion}/reject', [GrammarPatternController::class, 'rejectSuggestion'])
        ->name('grammar.suggestions.reject');
    Route::post('grammar/suggestions/{suggestion}/link', [GrammarPatternController::class, 'linkSuggestion'])
        ->name('grammar.suggestions.link');
    Route::post('grammar/suggestions/{suggestion}/enrich', [GrammarPatternController::class, 'enrichSuggestion'])
        ->name('grammar.suggestions.enrich');

    // Activity logs -------------------------------------------------------------
    Route::get('search-logs', [SearchLogController::class, 'index'])->name('search-logs.index');
    Route::get('search-logs/export', [SearchLogController::class, 'export'])->name('search-logs.export');

    Route::get('not-found', [SearchNotFoundController::class, 'index'])->name('not-found.index');
    Route::get('not-found/export', [SearchNotFoundController::class, 'export'])->name('not-found.export');
    Route::post('not-found/refresh', [SearchNotFoundController::class, 'refresh'])->name('not-found.refresh');
    Route::post('not-found/{character}/reject', [SearchNotFoundController::class, 'reject'])->name('not-found.reject');
    Route::post('not-found/{character}/unreject', [SearchNotFoundController::class, 'unreject'])->name('not-found.unreject');
    Route::get('not-found/{character}', [SearchNotFoundController::class, 'show'])->name('not-found.show');

    Route::get('shifu-engagements', [ShifuEngagementController::class, 'index'])->name('shifu-engagements.index');
    Route::get('shifu-engagements/export', [ShifuEngagementController::class, 'export'])->name('shifu-engagements.export');
    Route::post('shifu-engagements/import', [ShifuEngagementController::class, 'import'])->name('shifu-engagements.import');
    Route::get('shifu-engagements/{uuid}', [ShifuEngagementController::class, 'show'])->name('shifu-engagements.show');
});
