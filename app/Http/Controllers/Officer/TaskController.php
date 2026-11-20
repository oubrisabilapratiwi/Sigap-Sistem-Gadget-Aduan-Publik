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

        $tab === 'selesai'
            ? $query->status(ReportStatus::Done)->latest('completed_at')
            : $query->whereIn('status', [ReportStatus::Verified->value, ReportStatus::InProgress->value])->mostUpvoted();

        $stats = [
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

    /** Ubah status pengerjaan. Status "Selesai" WAJIB menyertakan foto bukti. */
    public function update(Request $request, Report $report, ImageService $images): RedirectResponse
    {
        abort_unless($report->assigned_to === $request->user()->id, 403);

        if ($report->status->isFinal()) {
            return back()->with('error', 'Laporan ini sudah ditutup dan tidak dapat diubah lagi.');
        }

        $isDone = $request->input('status') === ReportStatus::Done->value;

        $data = $request->validate([
            'status' => ['required', Rule::in([ReportStatus::InProgress->value, ReportStatus::Done->value])],
            'note' => [Rule::requiredIf(! $isDone), 'nullable', 'string', 'min:5', 'max:1000'],
            'photo' => [Rule::requiredIf($isDone), 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
        ], [
            'note.required' => 'Tuliskan catatan progres pengerjaan.',
            'note.min' => 'Catatan minimal 5 karakter.',
            'photo.required' => 'Foto bukti perbaikan wajib diunggah untuk menandai laporan selesai.',
        ]);

        $path = $request->hasFile('photo') ? $images->store($request->file('photo'), 'proofs') : null;

        try {
            DB::transaction(function () use ($report, $request, $data, $path, $isDone) {
                $report->update([
                    'status' => $data['status'],
                    'completed_at' => $isDone ? now() : null,
                ]);

                $report->updates()->create([
                    'user_id' => $request->user()->id,
                    'status' => $data['status'],
                    'note' => $data['note'] ?? ($isDone ? 'Perbaikan selesai dikerjakan.' : null),
                    'photo' => $path,
                ]);
            });
        } catch (Throwable $e) {
            $images->delete($path);
            throw $e;
        }

        return redirect()->route('officer.tasks.show', $report)
            ->with('success', $isDone ? 'Laporan ditandai selesai. Terima kasih atas kerja kerasnya!' : 'Progres pengerjaan diperbarui.');
    }
}
