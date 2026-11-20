<?php

namespace App\Http\Controllers;

use App\Enums\ReportStatus;
use App\Models\Category;
use App\Models\Region;
use App\Models\Report;
use App\Services\ImageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Throwable;

class ReportController extends Controller
{
    /** Daftar laporan publik (diurutkan berdasarkan dukungan terbanyak). */
    public function index(Request $request): View
    {
        $filters = $request->only(['q', 'category', 'region', 'status', 'sort']);

        $base = Report::query()->visible()->filter($filters);

        $reports = (clone $base)
            ->with(['category', 'region'])
            ->withVotedBy($request->user())
            ->sortedBy($filters['sort'] ?? null)
            ->paginate(9)
            ->withQueryString();

        $markers = (clone $base)->limit(300)
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

        return view('reports.index', [
            'reports' => $reports,
            'markers' => $markers,
            'filters' => $filters,
            'categories' => Category::orderBy('id')->get(),
            'regions' => Region::orderBy('name')->get(),
            'statuses' => ReportStatus::flow(),
        ]);
    }

    public function show(Request $request, Report $report): View
    {
        $user = $request->user();

        // Laporan yang ditolak hanya boleh dilihat pelapor & admin
        if ($report->status === ReportStatus::Rejected) {
            abort_unless($user && ($user->id === $report->user_id || $user->isAdmin()), 404);
        }

        $report->load(['category', 'region', 'user', 'assignee', 'updates.user']);
        $report->setAttribute('voted_by_me', $report->hasVotedBy($user));

        $related = Report::visible()->active()
            ->where('id', '!=', $report->id)
            ->where('region_id', $report->region_id)
            ->with(['category', 'region'])
            ->withVotedBy($user)
            ->mostUpvoted()
            ->limit(3)
            ->get();

        return view('reports.show', compact('report', 'related'));
    }

    public function create(): View
    {
        return view('reports.create', [
            'categories' => Category::orderBy('id')->get(),
            'regions' => Region::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request, ImageService $images): RedirectResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'title' => ['required', 'string', 'min:5', 'max:120'],
            'category_id' => ['required', 'exists:categories,id'],
            'region_id' => ['required', 'exists:regions,id'],
            'description' => ['required', 'string', 'min:20', 'max:2000'],
            'address' => ['required', 'string', 'max:255'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'photo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
        ], [
            'latitude.required' => 'Tentukan titik lokasi pada peta terlebih dahulu.',
            'longitude.required' => 'Tentukan titik lokasi pada peta terlebih dahulu.',
            'photo.required' => 'Foto bukti wajib dilampirkan.',
        ]);

        // Anti-spam: batasi jumlah laporan per hari
        $limit = config('sigap.daily_report_limit');
        if ($user->reports()->whereDate('created_at', today())->count() >= $limit) {
            return back()->withInput()->withErrors([
                'title' => "Batas {$limit} laporan per hari sudah tercapai. Silakan coba lagi besok.",
            ]);
        }

        // Foto dikompresi otomatis oleh Intervention Image
        $path = $images->store($request->file('photo'));

        try {
            $report = DB::transaction(function () use ($data, $user, $path) {
                $report = Report::create([
                    'user_id' => $user->id,
                    'category_id' => $data['category_id'],
                    'region_id' => $data['region_id'],
                    'title' => $data['title'],
                    'description' => $data['description'],
                    'address' => $data['address'],
                    'latitude' => $data['latitude'],
                    'longitude' => $data['longitude'],
                    'photo' => $path,
                    'status' => ReportStatus::Submitted,
                ]);

                $report->updates()->create([
                    'user_id' => $user->id,
                    'status' => ReportStatus::Submitted,
                    'note' => 'Laporan diajukan oleh warga dan menunggu verifikasi admin.',
                ]);

                return $report;
            });
        } catch (Throwable $e) {
            $images->delete($path);
            throw $e;
        }

        return redirect()->route('reports.show', $report)
            ->with('success', 'Laporan terkirim! Simpan kode pelacakan Anda: ' . $report->tracking_code);
    }

    /** Laporan milik saya. */
    public function mine(Request $request): View
    {
        $reports = $request->user()->reports()
            ->with(['category', 'region'])
            ->latest()
            ->paginate(9);

        $counts = $request->user()->reports()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('reports.mine', compact('reports', 'counts'));
    }

    /** Pelapor boleh menghapus laporannya selama belum diverifikasi. */
    public function destroy(Request $request, Report $report, ImageService $images): RedirectResponse
    {
        abort_unless($report->user_id === $request->user()->id, 403);

        if ($report->status !== ReportStatus::Submitted) {
            return back()->with('error', 'Laporan yang sudah diproses tidak dapat dihapus.');
        }

        $images->delete($report->photo);
        $report->delete();

        return redirect()->route('reports.mine')->with('success', 'Laporan berhasil dihapus.');
    }
}
