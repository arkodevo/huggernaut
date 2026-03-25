<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ShifuEngagement;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ShifuEngagementController extends Controller
{
    public function index(Request $request): View
    {
        $query = $this->applyFilters(ShifuEngagement::with('user'), $request);
        $engagements = $query->latest('started_at')->paginate(50)->withQueryString();

        $stats = [
            'total'      => ShifuEngagement::count(),
            'wc'         => ShifuEngagement::where('context', 'writing_conservatory')->count(),
            'test'       => ShifuEngagement::where('context', 'test')->count(),
            'generation' => ShifuEngagement::where('context', 'generation')->count(),
            'saved'      => ShifuEngagement::where('outcome', 'saved')->count(),
            'correct'    => ShifuEngagement::where('outcome', 'correct')->count(),
            'incorrect'  => ShifuEngagement::where('outcome', 'incorrect')->count(),
        ];

        return view('admin.shifu-engagements.index', compact('engagements', 'stats'));
    }

    public function show(string $uuid): View
    {
        $engagement = ShifuEngagement::where('uuid', $uuid)
            ->with(['user', 'wordSense', 'wordObject', 'interactions'])
            ->firstOrFail();

        return view('admin.shifu-engagements.show', compact('engagement'));
    }

    public function export(Request $request): StreamedResponse
    {
        $format = $request->input('format', 'json');
        $query = $this->applyFilters(ShifuEngagement::with(['user', 'interactions']), $request);
        $engagements = $query->latest('started_at')->get();

        $filename = 'shifu-engagements-' . now()->format('Y-m-d') . '.' . $format;

        if ($format === 'csv') {
            return $this->exportCsv($engagements, $filename);
        }

        return $this->exportJson($engagements, $filename);
    }

    public function import(Request $request)
    {
        $request->validate(['file' => 'required|file|mimes:json,txt']);

        $content = file_get_contents($request->file('file')->getPathname());
        $data = json_decode($content, true);

        if (! $data || ! isset($data['engagements'])) {
            return back()->with('error', 'Invalid JSON format. Expected { "engagements": [...] }');
        }

        $updated = 0;
        foreach ($data['engagements'] as $item) {
            if (empty($item['uuid']) || empty($item['audit'])) continue;

            $audit = $item['audit'];
            if (empty($audit['grade']) && empty($audit['feedback'])) continue;

            $engagement = ShifuEngagement::where('uuid', $item['uuid'])->first();
            if (! $engagement) continue;

            $engagement->update([
                'audit_grade'       => $audit['grade'] ?? null,
                'audit_feedback'    => $audit['feedback'] ?? null,
                'audit_reviewed_at' => $audit['reviewed_at'] ?? now(),
            ]);
            $updated++;
        }

        return back()->with('success', "Imported audit data for {$updated} engagements.");
    }

    private function applyFilters($query, Request $request)
    {
        if ($request->filled('context')) {
            $query->where('context', $request->input('context'));
        }
        if ($request->filled('outcome')) {
            $query->where('outcome', $request->input('outcome'));
        }
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->input('user_id'));
        }
        if ($request->filled('word')) {
            $query->where('word_label', 'like', '%' . $request->input('word') . '%');
        }
        if ($request->filled('date_from')) {
            $query->where('started_at', '>=', $request->input('date_from') . ' 00:00:00');
        }
        if ($request->filled('date_to')) {
            $query->where('started_at', '<=', $request->input('date_to') . ' 23:59:59');
        }
        return $query;
    }

    private function exportJson($engagements, string $filename): StreamedResponse
    {
        $data = [
            'exported_at'  => now()->toIso8601String(),
            'count'        => $engagements->count(),
            'engagements'  => $engagements->map(fn ($e) => [
                'uuid'         => $e->uuid,
                'word_label'   => $e->word_label,
                'context'      => $e->context,
                'outcome'      => $e->outcome,
                'user'         => $e->user?->name ?? 'Guest',
                'started_at'   => $e->started_at?->toIso8601String(),
                'completed_at' => $e->completed_at?->toIso8601String(),
                'interactions' => $e->interactions->map(fn ($i) => [
                    'sequence'       => $i->sequence,
                    'learner_input'  => $i->learner_input,
                    'shifu_response' => $i->shifu_response,
                    'is_correct'     => $i->is_correct,
                    'hints_used'     => $i->hints_used,
                    'created_at'     => $i->created_at?->toIso8601String(),
                ])->all(),
                'audit' => [
                    'grade'       => $e->audit_grade,
                    'feedback'    => $e->audit_feedback,
                    'reviewed_at' => $e->audit_reviewed_at?->toIso8601String(),
                ],
            ])->all(),
        ];

        return response()->streamDownload(function () use ($data) {
            echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        }, $filename, ['Content-Type' => 'application/json']);
    }

    private function exportCsv($engagements, string $filename): StreamedResponse
    {
        return response()->streamDownload(function () use ($engagements) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, ['UUID', 'Word', 'Context', 'Outcome', 'User', 'Sequence', 'Learner Input', 'Response', 'Correct', 'Started At', 'Interaction At']);

            foreach ($engagements as $e) {
                foreach ($e->interactions as $i) {
                    fputcsv($handle, [
                        $e->uuid,
                        $e->word_label,
                        $e->context,
                        $e->outcome ?? '',
                        $e->user?->name ?? 'Guest',
                        $i->sequence,
                        $i->learner_input,
                        $i->shifu_response,
                        $i->is_correct === null ? '' : ($i->is_correct ? 'yes' : 'no'),
                        $e->started_at?->toIso8601String(),
                        $i->created_at?->toIso8601String(),
                    ]);
                }
            }
            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }
}
