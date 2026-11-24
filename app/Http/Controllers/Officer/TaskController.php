<?php

namespace App\Http\Controllers\Officer;

use App\Enums\ReportStatus;
use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Services\ImageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Throwable;

class TaskController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $tab = $request->query('tab', 'aktif');

        $query = Report::query()->assignedTo($user)->with(['category', 'region']);

        // Riwayat = laporan yang sudah ditutup (selesai atau ditolak setelah dicek petugas)
        $tab === 'selesai'
            ? $query->whereIn('status', [ReportStatus::Done->value, ReportStatus::Rejected->value])->latest('updated_at')
            : $query->whereIn('status', ReportStatus::activeValues())->mostUpvoted();

        $stats = [
            'submitted' => $user->assignedReports()->where('status', ReportStatus::Submitted->value)->count(),
            'verified' => $user->assignedReports()->where('status', ReportStatus::Verified->value)->count(),
            'in_progress' => $user->assignedReports()->where('status', ReportStatus::InProgress->value)->count(),
            'done' => $user->assignedReports()->where('status', ReportStatus::Done->value)->count(),
        ];

        return view('officer.index', [
            'tasks' => $query->paginate(9)->withQueryString(),
            'stats' => $stats,
            'tab' => $tab,
        ]);
    }

    public function show(Request $request, Report $report): View
    {
        abort_unless($report->assigned_to === $request->user()->id, 403);

        $report->load(['category', 'region', 'user', 'updates.user']);

        return view('officer.show', compact('report'));
    }

    /**
     * Petugas menindaklanjuti laporan yang didisposisikan admin.
     *
     *  - Diajukan  : cek lapangan -> "Diverifikasi" (valid) atau "Ditolak" (hoax, WAJIB foto bukti asli di lokasi).
     *  - Diverifikasi / Sedang Dikerjakan : lanjut ke "Sedang Dikerjakan" atau "Selesai" (WAJIB foto bukti perbaikan).
     */
    public function update(Request $request, Report $report, ImageService $images): RedirectResponse
    {
        abort_unless($report->assigned_to === $request->user()->id, 403);

        if ($report->status->isFinal()) {
            return back()->with('error', 'Laporan ini sudah ditutup dan tidak dapat diubah lagi.');
        }

        $allowed = $report->status === ReportStatus::Submitted
            ? [ReportStatus::Verified->value, ReportStatus::Rejected->value]
            : [ReportStatus::InProgress->value, ReportStatus::Done->value];

        $input = $request->input('status');
        $isDone = $input === ReportStatus::Done->value;
        $isReject = $input === ReportStatus::Rejected->value;
        $isVerify = $input === ReportStatus::Verified->value;

        $data = $request->validate([
            'status' => ['required', Rule::in($allowed)],
            'note' => [Rule::requiredIf(! $isDone && ! $isVerify), 'nullable', 'string', 'min:' . ($isReject ? 10 : 5), 'max:1000'],
            'photo' => [Rule::requiredIf($isDone || $isReject), 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
        ], [
            'status.in' => 'Status tidak valid untuk tahap laporan saat ini.',
            'note.required' => $isReject ? 'Jelaskan temuan di lapangan yang menunjukkan laporan tidak sesuai fakta.' : 'Tuliskan catatan progres pengerjaan.',
            'note.min' => $isReject ? 'Alasan penolakan minimal 10 karakter.' : 'Catatan minimal 5 karakter.',
            'photo.required' => $isReject
                ? 'Foto bukti kondisi asli di lokasi wajib diunggah untuk menolak laporan.'
                : 'Foto bukti perbaikan wajib diunggah untuk menandai laporan selesai.',
        ]);

        $path = $request->hasFile('photo') ? $images->store($request->file('photo'), 'proofs') : null;

        try {
            DB::transaction(function () use ($report, $request, $data, $path, $isDone, $isReject, $isVerify) {
                $changes = ['status' => $data['status']];

                if ($isVerify) {
                    $changes += ['verified_by' => $request->user()->id, 'verified_at' => now()];
                } elseif ($isReject) {
                    $changes += ['verified_by' => $request->user()->id, 'rejection_reason' => $data['note']];
                } else {
                    $changes['completed_at'] = $isDone ? now() : null;
                }

                $report->update($changes);

                $note = match (true) {
                    $isReject => 'Laporan ditolak setelah dicek di lokasi: ' . $data['note'],
                    $isVerify => 'Laporan terbukti valid setelah dicek di lokasi.' . (! empty($data['note']) ? ' Catatan petugas: ' . $data['note'] : ''),
                    $isDone => $data['note'] ?? 'Perbaikan selesai dikerjakan.',
                    default => $data['note'] ?? null,
                };

                $report->updates()->create([
                    'user_id' => $request->user()->id,
                    'status' => $data['status'],
                    'note' => $note,
                    'photo' => $path,
                ]);
            });
        } catch (Throwable $e) {
            $images->delete($path);
            throw $e;
        }

        $message = match (true) {
            $isReject => 'Laporan ditolak dan foto bukti tersimpan. Pelapor dapat melihat alasan penolakan.',
            $isVerify => 'Laporan diverifikasi. Silakan lanjutkan penanganan hingga selesai.',
            $isDone => 'Laporan ditandai selesai. Terima kasih atas kerja kerasnya!',
            default => 'Progres pengerjaan diperbarui.',
        };

        return redirect()->route('officer.tasks.show', $report)->with('success', $message);
    }
}
