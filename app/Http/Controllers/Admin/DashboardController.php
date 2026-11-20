<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ReportStatus;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Report;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $byStatus = Report::selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');

        $counts = [];
        foreach (ReportStatus::cases() as $status) {
            $counts[$status->value] = (int) ($byStatus[$status->value] ?? 0);
        }
        $total = array_sum($counts);

        $queue = Report::status(ReportStatus::Submitted)
            ->with(['category', 'region', 'user'])
            ->mostUpvoted()
            ->limit(6)
            ->get();

        $categories = Category::withCount('reports')->orderByDesc('reports_count')->get();

        $officers = User::role(User::ROLE_OFFICER)
            ->withCount([
                'assignedReports as active_count' => fn ($q) => $q->whereIn('status', [ReportStatus::Verified->value, ReportStatus::InProgress->value]),
                'assignedReports as done_count' => fn ($q) => $q->where('status', ReportStatus::Done->value),
            ])
            ->orderByDesc('active_count')
            ->get();

        return view('admin.dashboard', compact('counts', 'total', 'queue', 'categories', 'officers'));
    }
}
