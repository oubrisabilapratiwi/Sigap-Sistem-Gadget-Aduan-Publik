<?php

namespace App\Http\Controllers;

use App\Enums\ReportStatus;
use App\Models\Category;
use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(Request $request): View
    {
        $total = Report::visible()->count();
        $done = Report::status(ReportStatus::Done)->count();
        $processing = Report::whereIn('status', [ReportStatus::Verified->value, ReportStatus::InProgress->value])->count();

        $stats = [
            'total' => $total,
            'done' => $done,
            'processing' => $processing,
            'rate' => $total ? (int) round($done / $total * 100) : 0,
        ];

        // Prioritas utama: laporan aktif dengan dukungan warga terbanyak
        $urgent = Report::visible()->active()
            ->with(['category', 'region'])
            ->withVotedBy($request->user())
            ->mostUpvoted()
            ->limit(6)
            ->get();

        // Laporan untuk kartu hero: yang punya foto & dukungan terbanyak
        $featured = Report::visible()->active()->whereNotNull('photo')
            ->with(['category', 'region'])
            ->mostUpvoted()
            ->first() ?? $urgent->first();

        // Data kartu hero (selalu lengkap; memakai contoh bila belum ada laporan)
        $hero = [
            'url' => $featured ? route('reports.show', $featured) : '#',
            'photo' => $featured?->photo_url,
            'title' => $featured?->title ?? 'Lubang besar di depan sekolah membahayakan pengendara',
            'address' => $featured?->address ?? 'Jl. Melati Raya No. 12',
            'category' => $featured?->category->name ?? 'Jalan Rusak',
            'icon' => $featured?->category->icon ?? 'construction',
            'color' => $featured?->category->color ?? '#f97316',
            'step' => $featured ? $featured->status->step() : 2,
            'votes' => $featured?->upvotes_count ?? 128,
            'region' => $featured?->region->name ?? 'Contoh tampilan',
            'status' => $featured?->status,
        ];

        $recentDone = Report::status(ReportStatus::Done)
            ->with(['category', 'region'])
            ->latest('completed_at')
            ->limit(4)
            ->get();

        $categories = Category::withCount(['reports' => fn ($q) => $q->visible()])->get();

        $markers = Report::visible()->active()
            ->get(['id', 'tracking_code', 'title', 'latitude', 'longitude', 'status', 'upvotes_count'])
            ->map(fn ($r) => [
                'lat' => $r->latitude,
                'lng' => $r->longitude,
                'title' => $r->title,
                'status' => $r->status->label(),
                'color' => $r->status->hex(),
                'votes' => $r->upvotes_count,
                'url' => route('reports.show', $r),
            ]);

        return view('home', compact('stats', 'urgent', 'hero', 'recentDone', 'categories', 'markers'));
    }
}