<?php

namespace App\Http\Controllers;

use App\Models\AccessRequest;
use App\Models\AuditLog;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\View\View;

class AdminInsightsController extends Controller
{
    public function reports(Request $request): View
    {
        [$from, $to] = $this->resolveDateRange($request, 30);

        $baseQuery = $this->reportsBaseQuery($from, $to);

        $stats = [
            'total' => (clone $baseQuery)->count(),
            'pending' => (clone $baseQuery)->where('status', 'pending')->count(),
            'approved' => (clone $baseQuery)->where('status', 'approved')->count(),
            'fulfilled' => (clone $baseQuery)->where('status', 'fulfilled')->count(),
            'rejected' => (clone $baseQuery)->where('status', 'rejected')->count(),
            'cancelled' => (clone $baseQuery)->where('status', 'cancelled')->count(),
        ];

        $byStatus = (clone $baseQuery)
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->orderByDesc('total')
            ->get();

        $byType = (clone $baseQuery)
            ->select('request_type', DB::raw('COUNT(*) as total'))
            ->groupBy('request_type')
            ->orderByDesc('total')
            ->get();

        $byDepartment = (clone $baseQuery)
            ->leftJoin('departments', 'access_requests.department_id', '=', 'departments.id')
            ->select('departments.name as department_name', DB::raw('COUNT(*) as total'))
            ->groupBy('departments.name')
            ->orderByDesc('total')
            ->limit(8)
            ->get();

        $recentRequests = (clone $baseQuery)
            ->with(['template', 'requester'])
            ->latest('submitted_at')
            ->take(12)
            ->get();

        $avgFulfillmentHours = (clone $baseQuery)
            ->whereNotNull('fulfilled_at')
            ->selectRaw('AVG(EXTRACT(EPOCH FROM (fulfilled_at - submitted_at)) / 3600) as avg_hours')
            ->value('avg_hours');

        $requestsByDate = (clone $baseQuery)
            ->select(DB::raw('DATE(submitted_at) as date'), DB::raw('COUNT(*) as total'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return view('reports.index', compact(
            'from',
            'to',
            'stats',
            'byStatus',
            'byType',
            'byDepartment',
            'recentRequests',
            'avgFulfillmentHours',
            'requestsByDate'
        ));
    }

    public function exportReportsCsv(Request $request): StreamedResponse
    {
        [$from, $to] = $this->resolveDateRange($request, 30);
        $filename = "reports-{$from}-to-{$to}.csv";

        return response()->streamDownload(function () use ($from, $to) {
            $output = fopen('php://output', 'w');
            fputcsv($output, ['Request #', 'Submitted At', 'Requester', 'Target User', 'Email', 'Template', 'Type', 'Status', 'Priority']);

            $query = $this->reportsBaseQuery($from, $to)
                ->with(['requester', 'template'])
                ->orderByDesc('submitted_at');

            foreach ($query->cursor() as $row) {
                fputcsv($output, [
                    $row->request_number,
                    optional($row->submitted_at)->format('Y-m-d H:i:s'),
                    $row->requester?->name ?? 'N/A',
                    $row->full_name,
                    $row->email,
                    $row->template?->display_name ?? 'N/A',
                    $row->request_type,
                    $row->status,
                    $row->priority,
                ]);
            }

            fclose($output);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function exportReportsPdf(Request $request)
    {
        [$from, $to] = $this->resolveDateRange($request, 30);
        $baseQuery = $this->reportsBaseQuery($from, $to);

        $stats = [
            'total' => (clone $baseQuery)->count(),
            'pending' => (clone $baseQuery)->where('status', 'pending')->count(),
            'approved' => (clone $baseQuery)->where('status', 'approved')->count(),
            'fulfilled' => (clone $baseQuery)->where('status', 'fulfilled')->count(),
            'rejected' => (clone $baseQuery)->where('status', 'rejected')->count(),
            'cancelled' => (clone $baseQuery)->where('status', 'cancelled')->count(),
        ];

        $recentRequests = (clone $baseQuery)
            ->with(['template', 'requester'])
            ->latest('submitted_at')
            ->take(30)
            ->get();

        $pdf = Pdf::loadView('reports.pdf', [
            'from' => $from,
            'to' => $to,
            'stats' => $stats,
            'recentRequests' => $recentRequests,
            'generatedAt' => now(),
        ])->setPaper('a4', 'portrait');

        return $pdf->download("reports-{$from}-to-{$to}.pdf");
    }

    public function auditLogs(Request $request): View
    {
        [$from, $to] = $this->resolveDateRange($request, 14);
        $action = trim((string) $request->input('action', ''));
        $search = trim((string) $request->input('search', ''));

        $baseQuery = $this->auditLogsBaseQuery($from, $to, $action, $search);

        $auditLogs = (clone $baseQuery)
            ->latest('created_at')
            ->paginate(20)
            ->withQueryString();

        $stats = [
            'total' => (clone $baseQuery)->count(),
            'unique_users' => (clone $baseQuery)->whereNotNull('user_id')->distinct('user_id')->count('user_id'),
            'unique_actions' => (clone $baseQuery)->distinct('action')->count('action'),
        ];

        $topActions = (clone $baseQuery)
            ->select('action', DB::raw('COUNT(*) as total'))
            ->groupBy('action')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        return view('audit-logs.index', compact(
            'from',
            'to',
            'action',
            'search',
            'auditLogs',
            'stats',
            'topActions'
        ));
    }

    public function exportAuditLogsCsv(Request $request): StreamedResponse
    {
        [$from, $to] = $this->resolveDateRange($request, 14);
        $action = trim((string) $request->input('action', ''));
        $search = trim((string) $request->input('search', ''));
        $filename = "audit-logs-{$from}-to-{$to}.csv";

        return response()->streamDownload(function () use ($from, $to, $action, $search) {
            $output = fopen('php://output', 'w');
            fputcsv($output, ['Time', 'User', 'Email', 'Action', 'Model Type', 'Model ID', 'IP', 'Changes']);

            $query = $this->auditLogsBaseQuery($from, $to, $action, $search)
                ->latest('created_at');

            foreach ($query->cursor() as $row) {
                fputcsv($output, [
                    optional($row->created_at)->format('Y-m-d H:i:s'),
                    $row->user?->name ?? 'System',
                    $row->user?->email ?? '',
                    $row->action,
                    $row->model_type,
                    $row->model_id,
                    $row->ip_address,
                    is_array($row->changes) ? json_encode($row->changes) : '',
                ]);
            }

            fclose($output);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function exportAuditLogsPdf(Request $request)
    {
        [$from, $to] = $this->resolveDateRange($request, 14);
        $action = trim((string) $request->input('action', ''));
        $search = trim((string) $request->input('search', ''));

        $auditLogs = $this->auditLogsBaseQuery($from, $to, $action, $search)
            ->latest('created_at')
            ->take(100)
            ->get();

        $pdf = Pdf::loadView('audit-logs.pdf', [
            'from' => $from,
            'to' => $to,
            'action' => $action,
            'search' => $search,
            'auditLogs' => $auditLogs,
            'generatedAt' => now(),
        ])->setPaper('a4', 'landscape');

        return $pdf->download("audit-logs-{$from}-to-{$to}.pdf");
    }

    private function reportsBaseQuery(string $from, string $to): Builder
    {
        return AccessRequest::query()
            ->whereDate('submitted_at', '>=', $from)
            ->whereDate('submitted_at', '<=', $to);
    }

    private function auditLogsBaseQuery(string $from, string $to, string $action, string $search): Builder
    {
        $query = AuditLog::query()
            ->with('user')
            ->whereDate('created_at', '>=', $from)
            ->whereDate('created_at', '<=', $to);

        if ($action !== '') {
            $query->where('action', 'ILIKE', "%{$action}%");
        }

        if ($search !== '') {
            $query->where(function ($innerQuery) use ($search) {
                $innerQuery->where('model_type', 'ILIKE', "%{$search}%")
                    ->orWhere('ip_address', 'ILIKE', "%{$search}%")
                    ->orWhere('action', 'ILIKE', "%{$search}%")
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'ILIKE', "%{$search}%")
                            ->orWhere('email', 'ILIKE', "%{$search}%");
                    });
            });
        }

        return $query;
    }

    private function resolveDateRange(Request $request, int $defaultDays): array
    {
        $from = $request->input('from', now()->subDays($defaultDays)->toDateString());
        $to = $request->input('to', now()->toDateString());

        return [$from, $to];
    }
}
