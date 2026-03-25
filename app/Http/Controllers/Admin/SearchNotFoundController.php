<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LexiconGap;
use App\Models\SearchLog;
use App\Models\SearchNotFound;
use App\Models\WordObject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SearchNotFoundController extends Controller
{
    public function index(Request $request): View
    {
        $query = SearchNotFound::select(
                'search_not_found.character',
                DB::raw('COUNT(*) as occurrences'),
                DB::raw('COUNT(DISTINCT search_logs.user_id) as unique_searchers'),
                DB::raw('MIN(search_not_found.created_at) as first_seen'),
                DB::raw('MAX(search_not_found.created_at) as last_seen'),
            )
            ->join('search_logs', 'search_logs.id', '=', 'search_not_found.search_log_id')
            ->groupBy('search_not_found.character');

        // Join lexicon_gaps for status
        $query->leftJoin('lexicon_gaps', 'lexicon_gaps.character', '=', 'search_not_found.character')
              ->addSelect(DB::raw("COALESCE(lexicon_gaps.status, 'pending') as gap_status"))
              ->addSelect('lexicon_gaps.status_updated_at');

        if ($request->filled('status')) {
            $query->where('lexicon_gaps.status', $request->input('status'));
        }
        if ($request->filled('date_from')) {
            $query->where('search_not_found.created_at', '>=', $request->input('date_from') . ' 00:00:00');
        }
        if ($request->filled('date_to')) {
            $query->where('search_not_found.created_at', '<=', $request->input('date_to') . ' 23:59:59');
        }

        $words = $query->orderByDesc('occurrences')->paginate(50)->withQueryString();

        $stats = [
            'unique_chars'      => SearchNotFound::distinct('character')->count('character'),
            'total_occurrences' => SearchNotFound::count(),
            'trending_7d'       => SearchNotFound::where('created_at', '>=', now()->subDays(7))
                                    ->distinct('character')->count('character'),
            'pending'           => LexiconGap::where('status', 'pending')->count(),
            'added'             => LexiconGap::where('status', 'added')->count(),
            'rejected'          => LexiconGap::where('status', 'rejected')->count(),
        ];

        return view('admin.not-found.index', compact('words', 'stats'));
    }

    public function show(string $character): View
    {
        $logs = SearchLog::whereHas('notFoundWords', fn ($q) => $q->where('character', $character))
            ->with('user')
            ->latest('created_at')
            ->paginate(50);

        $gap = LexiconGap::where('character', $character)->first();

        $stats = [
            'total_searches'   => SearchNotFound::where('character', $character)->count(),
            'unique_searchers' => SearchNotFound::where('character', $character)
                                    ->join('search_logs', 'search_logs.id', '=', 'search_not_found.search_log_id')
                                    ->distinct('search_logs.user_id')
                                    ->count('search_logs.user_id'),
            'first_seen'       => SearchNotFound::where('character', $character)->min('created_at'),
            'last_seen'        => SearchNotFound::where('character', $character)->max('created_at'),
            'status'           => $gap?->status ?? 'pending',
            'status_updated_at' => $gap?->status_updated_at,
        ];

        return view('admin.not-found.show', compact('character', 'logs', 'stats', 'gap'));
    }

    public function refresh(): \Illuminate\Http\RedirectResponse
    {
        $pending = LexiconGap::where('status', 'pending')->get();
        $updated = 0;

        foreach ($pending as $gap) {
            $found = WordObject::where('traditional', $gap->character)
                ->orWhere('simplified', $gap->character)
                ->exists();

            if ($found) {
                $gap->update([
                    'status'            => 'added',
                    'status_updated_at' => now(),
                ]);
                $updated++;
            }
        }

        return back()->with('success', "Refreshed status: {$updated} character(s) now found in lexicon.");
    }

    public function reject(string $character): \Illuminate\Http\RedirectResponse
    {
        LexiconGap::updateOrCreate(
            ['character' => $character],
            ['status' => 'rejected', 'status_updated_at' => now()],
        );

        return back()->with('success', "「{$character}」 marked as rejected.");
    }

    public function export(Request $request): StreamedResponse
    {
        $format = $request->input('format', 'json');

        $query = SearchNotFound::select(
                'search_not_found.character',
                DB::raw('COUNT(*) as occurrences'),
                DB::raw('COUNT(DISTINCT search_logs.user_id) as unique_searchers'),
                DB::raw('MIN(search_not_found.created_at) as first_seen'),
                DB::raw('MAX(search_not_found.created_at) as last_seen'),
            )
            ->join('search_logs', 'search_logs.id', '=', 'search_not_found.search_log_id')
            ->leftJoin('lexicon_gaps', 'lexicon_gaps.character', '=', 'search_not_found.character')
            ->addSelect(DB::raw("COALESCE(lexicon_gaps.status, 'pending') as gap_status"))
            ->addSelect('lexicon_gaps.status_updated_at')
            ->groupBy('search_not_found.character');

        if ($request->filled('status')) {
            $query->where('lexicon_gaps.status', $request->input('status'));
        }
        if ($request->filled('date_from')) {
            $query->where('search_not_found.created_at', '>=', $request->input('date_from') . ' 00:00:00');
        }
        if ($request->filled('date_to')) {
            $query->where('search_not_found.created_at', '<=', $request->input('date_to') . ' 23:59:59');
        }

        $words = $query->orderByDesc('occurrences')->get();
        $filename = 'not-found-' . now()->format('Y-m-d') . '.' . $format;

        if ($format === 'csv') {
            return response()->streamDownload(function () use ($words) {
                $handle = fopen('php://output', 'w');
                fwrite($handle, "\xEF\xBB\xBF");
                fputcsv($handle, ['Character', 'Occurrences', 'Unique Searchers', 'Status', 'First Seen', 'Last Seen', 'Status Updated']);
                foreach ($words as $w) {
                    fputcsv($handle, [$w->character, $w->occurrences, $w->unique_searchers, $w->gap_status, $w->first_seen, $w->last_seen, $w->status_updated_at]);
                }
                fclose($handle);
            }, $filename, ['Content-Type' => 'text/csv']);
        }

        $data = [
            'exported_at' => now()->toIso8601String(),
            'count'       => $words->count(),
            'characters'  => $words->map(fn ($w) => [
                'character'          => $w->character,
                'occurrences'        => $w->occurrences,
                'unique_searchers'   => $w->unique_searchers,
                'status'             => $w->gap_status,
                'first_seen'         => $w->first_seen,
                'last_seen'          => $w->last_seen,
                'status_updated_at'  => $w->status_updated_at,
            ])->all(),
        ];

        return response()->streamDownload(function () use ($data) {
            echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        }, $filename, ['Content-Type' => 'application/json']);
    }
}
