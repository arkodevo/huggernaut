<?php

namespace App\Http\Controllers;

use App\Models\Disputation;
use App\Models\WordSense;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DisputationController extends Controller
{
    /**
     * Composer page: learner picks which fields of a sense they want to
     * flag, writes a rationale, and toggles per-row anonymity.
     *
     * URL: GET /disputations/create?senseId=N
     */
    public function create(Request $request): View
    {
        $senseId = (int) $request->query('senseId');
        if (! $senseId) {
            abort(404);
        }

        $sense = WordSense::with([
                'wordObject',
                'pronunciation',
                'definitions' => fn ($q) => $q
                    ->where('language_id', 1)
                    ->orderBy('sort_order')
                    ->with('posLabel'),
                'examples' => fn ($q) => $q
                    ->where('is_suppressed', false)
                    ->orderBy('id'),
                'channel',
                'connotation',
                'tocflLevel',
                'hskLevel',
                'domains' => fn ($q) => $q->with([
                    'labels' => fn ($q) => $q->whereIn('language_id', [1, 2]),
                ]),
                'designations' => fn ($q) => $q->with([
                    'labels' => fn ($q) => $q->where('language_id', 1),
                ]),
            ])
            ->findOrFail($senseId);

        $payload = $this->shapeSenseForComposer($sense);

        return view('disputations.create', [
            'sense'    => $payload,
            'user'     => Auth::user(),
            'authUser' => (new ExploreController())->authUserPayload(),
        ]);
    }

    /**
     * Persist a new disputation.
     *
     * URL: POST /disputations
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'word_sense_id'     => 'required|integer|exists:word_senses,id',
            'fields_disputed'   => 'required|array|min:1',
            'fields_disputed.*' => 'required|string|max:64',
            'rationale'         => 'required|string|min:10|max:4000',
            'is_anonymous'      => 'nullable|boolean',
        ]);

        Disputation::create([
            'user_id'         => Auth::id(),
            'word_sense_id'   => $data['word_sense_id'],
            'fields_disputed' => array_values(array_unique($data['fields_disputed'])),
            'rationale'       => trim($data['rationale']),
            'is_anonymous'    => (bool) ($data['is_anonymous'] ?? false),
            'status'          => Disputation::STATUS_PENDING,
        ]);

        return redirect()
            ->route('my-activity', ['tab' => 'disputations'])
            ->with('status', 'Your disputation has been filed and is pending review.');
    }

    /**
     * Learner removes their own pending disputation. Resolved disputations
     * cannot be deleted — they are part of the editorial record.
     *
     * URL: DELETE /disputations/{id}
     */
    public function destroy(int $id): JsonResponse
    {
        $dispute = Disputation::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        if ($dispute->status !== Disputation::STATUS_PENDING) {
            return response()->json([
                'ok'      => false,
                'message' => 'Cannot delete a disputation once review has begun.',
            ], 422);
        }

        $dispute->delete();

        return response()->json(['ok' => true]);
    }

    /**
     * Shape a word_sense into the minimal payload the composer needs to
     * render every dispute-able field with a checkbox. Keys here are the
     * SAME strings that will land in fields_disputed[] when the learner
     * submits, so the adjudicator can later match them back to positions.
     */
    private function shapeSenseForComposer(WordSense $sense): array
    {
        $wo  = $sense->wordObject;
        $def = $sense->definitions->first();

        // Examples: each gets a unique key "example:{id}"
        $examples = $sense->examples->map(fn ($ex) => [
            'id'      => $ex->id,
            'key'     => 'example:' . $ex->id,
            'chinese' => $ex->chinese_text ?? '',
            'english' => $ex->english_text ?? '',
        ])->values()->all();

        // Attributes: each set single-select designation becomes a dispute target.
        // Keys are "attribute:{slug}" matching the category (e.g. attribute:register).
        $attributes = [];
        $addAttribute = function (?string $category, $designation) use (&$attributes) {
            if (! $designation || ! $category) {
                return;
            }
            $label = $designation->labels?->first()?->label ?? $designation->slug;
            $attributes[] = [
                'key'   => 'attribute:' . $category,
                'label' => ucfirst($category),
                'value' => $label,
            ];
        };

        // Single-select spectrum FKs
        if ($sense->channel) {
            $addAttribute('channel', $sense->channel);
        }
        if ($sense->connotation) {
            $addAttribute('connotation', $sense->connotation);
        }
        if ($sense->tocflLevel) {
            $addAttribute('tocfl_level', $sense->tocflLevel);
        }
        if ($sense->hskLevel) {
            $addAttribute('hsk_level', $sense->hskLevel);
        }

        // Multi-select designations (register, dimension, etc.) — group by category slug
        foreach ($sense->designations as $d) {
            // category slug lives on the designation's parent category; we
            // don't have an eager load for that here, so fall back to the
            // designation slug itself as a unique key per chip.
            $label = $d->labels?->first()?->label ?? $d->slug;
            $attributes[] = [
                'key'   => 'attribute:designation:' . $d->slug,
                'label' => 'Attribute',
                'value' => $label,
            ];
        }

        // Domains: each domain is dispute-able individually
        $domains = $sense->domains->map(function ($d) {
            $label = $d->labels?->first()?->label ?? $d->slug;
            return [
                'key'   => 'domain:' . $d->slug,
                'label' => $label,
            ];
        })->values()->all();

        return [
            'id'            => $sense->id,
            'traditional'   => $wo?->traditional ?? '',
            'smartId'       => $wo?->smart_id ?? '',
            'pinyin'        => $sense->pronunciation?->pronunciation_text ?? '',
            'pos'           => ExploreController::POS_FULL_NAMES[$def?->posLabel?->slug ?? ''] ?? '',
            'posAbbr'       => ExploreController::POS_DISPLAY_ABBR[$def?->posLabel?->slug ?? ''] ?? ($def?->posLabel?->slug ?? ''),
            'definition'    => $def?->definition_text ?? '',
            'formula'       => $sense->formula,
            'usage_note'    => $sense->usage_note,
            'learner_traps' => $sense->learner_traps,
            'examples'      => $examples,
            'attributes'    => $attributes,
            'domains'       => $domains,
        ];
    }
}
