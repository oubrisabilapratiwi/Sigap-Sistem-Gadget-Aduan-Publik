<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ReportStatus;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Region;
use App\Models\Report;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->only(['q', 'category', 'region', 'status', 'sort']);

        $reports = Report::query()
            ->filter($filters)
            ->with(['category', 'region', 'user', 'assignee'])
            ->sortedBy($filters['sort'] ?? null)
            ->paginate(12)
            ->withQueryString();

        // Jumlah per status untuk tab (mengabaikan filter status)
        $tabCounts = Report::query()
            ->filter(collect($filters)->except('status')->all())
            ->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');

        return view('admin.reports.index', [
            'reports' => $reports,
            'filters' => $filters,
            'tabCounts' => $tabCounts,
            'categories' => Category::orderBy('id')->get(),
            'regions' => Region::orderBy('name')->get(),
        ]);
    }

    public function show(Report $report): View
    {
        $report->load(['category', 'region', 'user', 'assignee', 'verifier', 'updates.user']);

        return view('admin.reports.show', [
            'report' => $report,
            'officers' => $this->officersFor($report),
        ]);
    }

    /** Validasi laporan valid + disposisi ke petugas lapangan. */
    public function verify(Request $request, Report $report): RedirectResponse
    {
        if ($report->status !== ReportStatus::Submitted) {
            return back()->with('error', 'Hanya laporan berstatus "Diajukan" yang dapat diverifikasi.');
        }

        $data = $request->validate([
            'assigned_to' => ['required', $this->officerRule()],
            'note' => ['nullable', 'string', 'max:500'],
        ], ['assigned_to.required' => 'Pilih petugas lapangan yang akan menangani.']);

        $officer = User::findOrFail($data['assigned_to']);

        DB::transaction(function () use ($report, $officer, $request, $data) {
            $report->update([
                'status' => ReportStatus::Verified,
                'assigned_to' => $officer->id,
                'verified_by' => $request->user()->id,
                'verified_at' => now(),
            ]);

            $note = "Laporan valid dan didisposisikan ke {$officer->name}.";
            if (! empty($data['note'])) {
                $note .= ' Catatan admin: ' . $data['note'];
            }

            $report->updates()->create([
                'user_id' => $request->user()->id,
                'status' => ReportStatus::Verified,
                'note' => $note,
            ]);
        });

        return redirect()->route('admin.reports.show', $report)
            ->with('success', "Laporan diverifikasi dan ditugaskan ke {$officer->name}.");
    }

    /** Tolak laporan (spam / palsu / duplikat). */
    public function reject(Request $request, Report $report): RedirectResponse
    {
        if ($report->status !== ReportStatus::Submitted) {
            return back()->with('error', 'Hanya laporan berstatus "Diajukan" yang dapat ditolak.');
        }

        $data = $request->validate([
            'rejection_reason' => ['required', 'string', 'min:10', 'max:500'],
            'block_user' => ['nullable', 'boolean'],
        ], [
            'rejection_reason.required' => 'Tuliskan alasan penolakan.',
            'rejection_reason.min' => 'Alasan penolakan minimal 10 karakter.',
        ]);

        DB::transaction(function () use ($report, $request, $data) {
            $report->update([
                'status' => ReportStatus::Rejected,
                'rejection_reason' => $data['rejection_reason'],
                'verified_by' => $request->user()->id,
            ]);

            $report->updates()->create([
                'user_id' => $request->user()->id,
                'status' => ReportStatus::Rejected,
                'note' => 'Laporan ditolak: ' . $data['rejection_reason'],
            ]);

            if ($request->boolean('block_user')) {
                $report->user()->update(['is_active' => false]);
            }
        });

        return redirect()->route('admin.reports.index', ['status' => 'submitted'])
            ->with('success', 'Laporan ditolak dan disembunyikan dari publik.');
    }

    /** Pindahkan tugas ke petugas lain. */
    public function reassign(Request $request, Report $report): RedirectResponse
    {
        if (! in_array($report->status, [ReportStatus::Verified, ReportStatus::InProgress], true)) {
            return back()->with('error', 'Tugas hanya dapat dialihkan selama laporan belum selesai.');
        }

        $data = $request->validate([
            'assigned_to' => ['required', $this->officerRule()],
        ]);

        $officer = User::findOrFail($data['assigned_to']);

        if ($officer->id === $report->assigned_to) {
            return back()->with('error', 'Laporan sudah ditugaskan ke petugas tersebut.');
        }

        DB::transaction(function () use ($report, $officer, $request) {
            $report->update(['assigned_to' => $officer->id]);

            $report->updates()->create([
                'user_id' => $request->user()->id,
                'status' => $report->status,
                'note' => "Tugas dialihkan ke {$officer->name}.",
            ]);
        });

        return back()->with('success', "Tugas dialihkan ke {$officer->name}.");
    }

    /** Petugas aktif; yang satu wilayah dengan laporan ditampilkan lebih dulu. */
    private function officersFor(Report $report)
    {
        return User::role(User::ROLE_OFFICER)
            ->where('is_active', true)
            ->with('region')
            ->withCount(['assignedReports as active_count' => fn ($q) => $q->whereIn('status', [ReportStatus::Verified->value, ReportStatus::InProgress->value])])
            ->orderByRaw('region_id = ? desc', [$report->region_id])
            ->orderBy('active_count')
            ->get();
    }

    /** Pastikan user yang dipilih memang petugas aktif. */
    private function officerRule()
    {
        return Rule::exists('users', 'id')->where(function ($q) {
            $q->where('is_active', true)
                ->whereIn('id', DB::table('model_has_roles')
                    ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
                    ->where('roles.name', User::ROLE_OFFICER)
                    ->where('model_has_roles.model_type', User::class)
                    ->select('model_has_roles.model_id'));
        });
    }
}
