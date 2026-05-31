<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        $auditLogs = AuditLog::with('user')
            ->when($request->event, fn ($query) => $query->where('event', $request->event))
            ->when($request->actor, function ($query) use ($request) {
                if ($request->actor === 'system') {
                    $query->whereNull('user_id');
                    return;
                }

                $query->where('user_id', $request->actor);
            })
            ->when($request->search, fn ($query) => $query->where(function ($query) use ($request) {
                $search = $request->search;

                $query->where('event', 'like', "%{$search}%")
                    ->orWhere('auditable_type', 'like', "%{$search}%")
                    ->orWhere('auditable_id', 'like', "%{$search}%")
                    ->orWhere('ip_address', 'like', "%{$search}%")
                    ->orWhereHas('user', fn ($userQuery) => $userQuery
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%"));
            }))
            ->latest()
            ->paginate(25)
            ->withQueryString();

        $events = AuditLog::query()
            ->select('event')
            ->distinct()
            ->orderBy('event')
            ->pluck('event');

        $actorIds = AuditLog::query()
            ->whereNotNull('user_id')
            ->distinct()
            ->pluck('user_id');

        $actors = User::whereIn('id', $actorIds)
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('admin.audit-logs.index', compact('auditLogs', 'events', 'actors'));
    }
}
