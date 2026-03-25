<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SearchLog;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SearchLogController extends Controller
{
    public function index(Request $request): View
    {
        $query = $this->applyFilters(SearchLog::with(['user', 'notFoundWords']), $request);
        $logs = $query->latest('created_at')->paginate(50)->withQueryString();

        $stats = [
            'total'             => SearchLog::count(),
            'unique_queries'    => SearchLog::distinct('query')->count('query'),
            'zero_results'      => SearchLog::where('results_count', 0)->count(),
            'sentence_searches' => SearchLog::where('search_type', 'sentence')->count(),
            'with_not_found'    => SearchLog::where('unknown_count', '>', 0)->count(),
        ];

        return view('admin.search-logs', compact('logs', 'stats'));
    }

    public function export(Request $request): StreamedResponse
    {
        $format = $request->input('format', 'json');
        $query = $this->applyFilters(SearchLog::with(['user', 'notFoundWords']), $request);
        $logs = $query->latest('created_at')->get();

        $filename = 'search-logs-' . now()->format('Y-m-d') . '.' . $format;

        if ($format === 'csv') {
            return $this->exportCsv($logs, $filename);
        }

        return $this->exportJson($logs, $filename);
    }

    private function applyFilters($query, Request $request)
    {
        if ($request->filled('q')) {
            $query->where('query', 'like', '%' . $request->input('q') . '%');
        }
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->input('user_id'));
        }
        if ($request->filled('zero_results')) {
            $query->where('results_count', 0);
        }
        if ($request->filled('search_type')) {
            $query->where('search_type', $request->input('search_type'));
        }
        if ($request->filled('user_role')) {
            $query->where('user_role', $request->input('user_role'));
        }
        if ($request->filled('session_id')) {
            $query->where('session_id', $request->input('session_id'));
        }
        if ($request->filled('date_from')) {
            $query->where('created_at', '>=', $request->input('date_from') . ' 00:00:00');
        }
        if ($request->filled('date_to')) {
            $query->where('created_at', '<=', $request->input('date_to') . ' 23:59:59');
        }
        return $query;
    }

    private function exportJson($logs, string $filename): StreamedResponse
    {
        $data = [
            'exported_at' => now()->toIso8601String(),
            'count'       => $logs->count(),
            'logs'        => $logs->map(fn ($log) => [
                'id'            => $log->id,
                'query'         => $log->query,
                'user'          => $log->user?->name ?? 'Guest',
                'user_role'     => $log->user_role,
                'session_id'    => $log->session_id,
                'search_type'   => $log->search_type,
                'results_count' => $log->results_count,
                'known_count'   => $log->known_count,
                'unknown_count' => $log->unknown_count,
                'not_found'     => $log->notFoundWords->pluck('character')->all(),
                'created_at'    => $log->created_at?->toIso8601String(),
            ])->all(),
        ];

        return response()->streamDownload(function () use ($data) {
            echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        }, $filename, ['Content-Type' => 'application/json']);
    }

    private function exportCsv($logs, string $filename): StreamedResponse
    {
        return response()->streamDownload(function () use ($logs) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF"); // UTF-8 BOM
            fputcsv($handle, ['ID', 'Query', 'User', 'Role', 'Session', 'Type', 'Results', 'Known', 'Unknown', 'Not Found', 'Created At']);

            foreach ($logs as $log) {
                fputcsv($handle, [
                    $log->id,
                    $log->query,
                    $log->user?->name ?? 'Guest',
                    $log->user_role ?? '',
                    $log->session_id ?? '',
                    $log->search_type,
                    $log->results_count,
                    $log->known_count,
                    $log->unknown_count,
                    $log->notFoundWords->pluck('character')->join(', '),
                    $log->created_at?->toIso8601String(),
                ]);
            }
            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }
}
