<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    private function buildQuery(Request $request)
    {
        $query = AuditLog::with('user', 'franchise')->latest('created_at');

        if ($request->filled('from')) {
            $query->where('created_at', '>=', $request->from . ' 00:00:00');
        }
        if ($request->filled('to')) {
            $query->where('created_at', '<=', $request->to . ' 23:59:59');
        }
        if ($request->filled('action') && $request->action !== 'all') {
            $query->where('action', $request->action);
        }
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('action', 'like', '%' . $request->search . '%')
                  ->orWhere('entity_type', 'like', '%' . $request->search . '%');
            });
        }

        return $query;
    }

    public function index(Request $request): View
    {
        $logs    = $this->buildQuery($request)->paginate(25)->withQueryString();
        $actions = AuditLog::distinct('action')->orderBy('action')->pluck('action');

        return view('admin.audit-log', compact('logs', 'actions'));
    }

    public function export(Request $request): Response
    {
        $logs = $this->buildQuery($request)->limit(5000)->get();

        $csv  = "Timestamp,User,Email,Action,Entity,Entity ID,Branch,IP Address\n";
        foreach ($logs as $log) {
            $csv .= implode(',', [
                '"' . $log->created_at->format('d M Y H:i:s') . '"',
                '"' . str_replace('"', '""', $log->user?->name ?? 'System') . '"',
                '"' . ($log->user?->email ?? '') . '"',
                '"' . str_replace('"', '""', $log->action) . '"',
                '"' . ($log->entity_type ?? '') . '"',
                $log->entity_id ?? '',
                '"' . str_replace('"', '""', $log->franchise?->name ?? '') . '"',
                '"' . ($log->ip_address ?? '') . '"',
            ]) . "\n";
        }

        $filename = 'audit-log-' . now()->format('Y-m-d') . '.csv';

        return response($csv, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}
