<?php

namespace Database\Seeders;

use App\Models\Attribute;
use App\Models\AttributeIcon;
use App\Models\Designation;
use App\Models\DesignationIcon;
use App\Models\IconTheme;
use App\Models\Language;
use App\Models\IconThemeLabel;
use Illuminate\Database\Seeder;

// Seeds the "Nature" system emoji theme — the default shipped icon set.
//
// Icon philosophy: Chinese is a symbolic language. Icons serve as
// mnemonic scaffolding, encoding linguistic concepts as vivid natural images
// so learners internalize them as symbols, not just labels.
//
// Attribute groups:
//   Channel     → Creatures  🦎🐍🦜🦚🐉  (oral ↔ written)
//   Register    → Insects    🦋🐝🐛🐞🦗🕷️ (refined ↔ edgy)
//   Connotation → Weather    ☀️🌤️⛅🌧️⛈️   (positive ↔ negative)
//   Semantic    → (abstract) 📖📝⚖️🌙🌌    (literal ↔ metaphorical)
//   Sensitivity → Traffic    🟢🟡🟠🔴⛔    (general ↔ taboo)
//   Dimension   → Sea life   🐢🐙🦀🐟🦂   (temporal·aspectual·resultative·pragmatic·figurative)
//   Intensity   → Flowers    🌸→🌺         (header only; value is scalar)
//   TOCFL Level → Moon       🌑🌒🌓🌔🌕🌝  (prep → fluency)
//   HSK Level   → Growth     🌰🌱🌿🌲🌳🎋  (hsk-1 → hsk-6)
//   Domain      → Contexts   👤📚✈️🍜🛍️🏥👨‍👩‍👧🎭💼🌍
class DefaultIconThemeSeeder extends Seeder
{
    public function run(): void
    {
        $theme = IconTheme::firstOrCreate(
            ['slug' => 'nature'],
            [
                'user_id'       => null,
                'source_theme_id'=> null,
                'name'          => 'Nature',
                'description'   => 'The default emoji icon set. Nature metaphors encode linguistic concepts as vivid symbols.',
                'icon_type'     => 'emoji',
                'is_active'     => true,
                'is_default'    => true,
                'sort_order'    => 1,
            ]
        );

        // i18n labels for the system theme
        $en   = Language::where('code', 'en')->value('id');
        $zhTW = Language::where('code', 'zh-TW')->value('id');

        IconThemeLabel::updateOrCreate(
            ['icon_theme_id' => $theme->id, 'language_id' => $en],
            ['label' => 'Nature']
        );
        IconThemeLabel::updateOrCreate(
            ['icon_theme_id' => $theme->id, 'language_id' => $zhTW],
            ['label' => '自然']
        );

        // ── Attribute header icons ────────────────────────────────────────────

        $attrIcons = [
            'channel'       => ['icon' => '🦜', 'alt' => 'Channel — oral/written spectrum'],
            'register'      => ['icon' => '🦋', 'alt' => 'Register — formality level'],
            'domain'        => ['icon' => '🗂️', 'alt' => 'Domain — subject area'],
            'sensitivity'   => ['icon' => '🛡️', 'alt' => 'Sensitivity — content rating'],
            'connotation'   => ['icon' => '⛅',  'alt' => 'Connotation — emotional tone'],
            'semantic-mode' => ['icon' => '⚖️',  'alt' => 'Semantic Mode — literal vs metaphorical'],
            'intensity'     => ['icon' => '🌸',  'alt' => 'Intensity — expressive strength (1–5)'],
            'dimension'     => ['icon' => '🐙',  'alt' => 'Dimension — usage dimension'],
            'tocfl-level'   => ['icon' => '🌕',  'alt' => 'TOCFL Level — Taiwan curriculum'],
            'hsk-level'     => ['icon' => '🌲',  'alt' => 'HSK Level — mainland curriculum'],
        ];

        foreach ($attrIcons as $attrSlug => $iconData) {
            $attrId = Attribute::where('slug', $attrSlug)->value('id');
            if (! $attrId) continue;

            AttributeIcon::updateOrCreate(
                ['attribute_id' => $attrId, 'icon_theme_id' => $theme->id],
                ['icon_value' => $iconData['icon'], 'icon_alt' => $iconData['alt']]
            );
        }

        // ── Designation icons ─────────────────────────────────────────────────

        $designationIcons = [

            // Channel — Creatures (oral 🦎 → written 🐉)
            'spoken-only'      => ['icon' => '🦎', 'alt' => 'Spoken only — no written usage'],
            'spoken-dominant'  => ['icon' => '🐍', 'alt' => 'Spoken-dominant — primarily oral'],
            'fluid'            => ['icon' => '🦜', 'alt' => 'Fluid — comfortable in both modes'],
            'written-dominant' => ['icon' => '🦚', 'alt' => 'Written-dominant — primarily written'],
            'written-only'     => ['icon' => '🐉', 'alt' => 'Written only — classical/formal text'],

            // Register — Insects (refined 🦋 → edgy 🕷️)
            'literary'   => ['icon' => '🦋', 'alt' => 'Literary — elegant, refined usage'],
            'formal'     => ['icon' => '🐝', 'alt' => 'Formal — structured, official contexts'],
            'standard'   => ['icon' => '🐛', 'alt' => 'Standard — everyday, neutral usage'],
            'informal'   => ['icon' => '🐞', 'alt' => 'Informal — relaxed, friendly register'],
            'colloquial' => ['icon' => '🦗', 'alt' => 'Colloquial — casual spoken style'],
            'slang'      => ['icon' => '🕷️', 'alt' => 'Slang — informal, edgy, non-standard'],

            // Connotation — Weather (positive ☀️ → negative ⛈️)
            'positive'          => ['icon' => '☀️',  'alt' => 'Positive — favourable connotation'],
            'positive-dominant' => ['icon' => '🌤️', 'alt' => 'Mostly positive — leans favourable'],
            'context-dependent' => ['icon' => '⛅',  'alt' => 'Context-dependent — tone varies'],
            'negative-dominant' => ['icon' => '🌧️', 'alt' => 'Mostly negative — leans unfavourable'],
            'negative'          => ['icon' => '⛈️',  'alt' => 'Negative — unfavourable connotation'],

            // Semantic Mode (literal 📖 → metaphorical 🌌)
            'literal-only'          => ['icon' => '📖', 'alt' => 'Literal only — concrete, dictionary meaning'],
            'literal-dominant'      => ['icon' => '📝', 'alt' => 'Mostly literal — primary concrete sense'],
            'balanced'              => ['icon' => '⚖️', 'alt' => 'Balanced — literal and metaphorical both active'],
            'metaphorical-dominant' => ['icon' => '🌙', 'alt' => 'Mostly metaphorical — figurative sense leads'],
            'metaphorical-only'     => ['icon' => '🌌', 'alt' => 'Metaphorical only — purely figurative'],

            // Sensitivity — Traffic lights (safe 🟢 → taboo ⛔)
            'general'   => ['icon' => '🟢', 'alt' => 'General — suitable for all audiences'],
            'mature'    => ['icon' => '🟡', 'alt' => 'Mature — adult themes, not graphic'],
            'profanity' => ['icon' => '🟠', 'alt' => 'Profanity — strong language'],
            'sexual'    => ['icon' => '🔴', 'alt' => 'Sexual — explicit sexual content'],
            'taboo'     => ['icon' => '⛔', 'alt' => 'Taboo — culturally prohibited'],

            // Dimension — Sea life (🐢🐙🦀🐟🦂)
            'temporal'    => ['icon' => '🐢', 'alt' => 'Temporal — relates to time expressions'],
            'aspectual'   => ['icon' => '🐙', 'alt' => 'Aspectual — aspect-related usage patterns'],
            'resultative' => ['icon' => '🦀', 'alt' => 'Resultative — result/complement patterns'],
            'pragmatic'   => ['icon' => '🐟', 'alt' => 'Pragmatic — discourse and pragmatic function'],
            'figurative'  => ['icon' => '🦂', 'alt' => 'Figurative — rhetorical and figurative usage'],

            // TOCFL Level — Moon phases (🌑 prep → 🌝 fluency)
            'tocfl-prep'     => ['icon' => '🌑', 'alt' => 'TOCFL Prep — total beginner'],
            'tocfl-entry'    => ['icon' => '🌒', 'alt' => 'TOCFL Entry — early learner'],
            'tocfl-basic'    => ['icon' => '🌓', 'alt' => 'TOCFL Basic — foundational'],
            'tocfl-advanced' => ['icon' => '🌔', 'alt' => 'TOCFL Advanced — upper intermediate'],
            'tocfl-high'     => ['icon' => '🌕', 'alt' => 'TOCFL High — near-fluent'],
            'tocfl-fluency'  => ['icon' => '🌝', 'alt' => 'TOCFL Fluency — native-like competence'],

            // HSK Level — Growth (🌰 seed → 🎋 bamboo master)
            'hsk-1' => ['icon' => '🌰', 'alt' => 'HSK 1 — seed stage'],
            'hsk-2' => ['icon' => '🌱', 'alt' => 'HSK 2 — seedling'],
            'hsk-3' => ['icon' => '🌿', 'alt' => 'HSK 3 — growing'],
            'hsk-4' => ['icon' => '🌲', 'alt' => 'HSK 4 — established'],
            'hsk-5' => ['icon' => '🌳', 'alt' => 'HSK 5 — mature tree'],
            'hsk-6' => ['icon' => '🎋', 'alt' => 'HSK 6 — bamboo master'],

            // Domain — Context icons
            'personal'      => ['icon' => '👤',  'alt' => 'Personal — self, identity, biography'],
            'education'     => ['icon' => '📚',  'alt' => 'Education — learning, study, school'],
            'travel'        => ['icon' => '✈️',  'alt' => 'Travel — transport, tourism, geography'],
            'food'          => ['icon' => '🍜',  'alt' => 'Food & Drink — cuisine, cooking, dining'],
            'shopping'      => ['icon' => '🛍️', 'alt' => 'Shopping — commerce, purchases, retail'],
            'health'        => ['icon' => '🏥',  'alt' => 'Health — medicine, body, wellness'],
            'family'        => ['icon' => '👨‍👩‍👧', 'alt' => 'Family — relationships, home, kinship'],
            'entertainment' => ['icon' => '🎭',  'alt' => 'Entertainment — arts, media, leisure'],
            'work'          => ['icon' => '💼',  'alt' => 'Work — career, business, professional'],
            'environment'   => ['icon' => '🌍',  'alt' => 'Environment — nature, ecology, weather'],
        ];

        foreach ($designationIcons as $slug => $iconData) {
            $desId = Designation::where('slug', $slug)->value('id');
            if (! $desId) continue;

            DesignationIcon::updateOrCreate(
                ['designation_id' => $desId, 'icon_theme_id' => $theme->id],
                ['icon_value' => $iconData['icon'], 'icon_alt' => $iconData['alt']]
            );
        }
    }
}
