<?php

namespace App\Http\Controllers;

use App\Enums\ReportStatus;
use App\Models\Report;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class VoteController extends Controller
{
    /** Beri / cabut dukungan (upvote) pada laporan warga lain. */
    public function __invoke(Request $request, Report $report): JsonResponse|RedirectResponse
    {
        $user = $request->user();

        $error = match (true) {
            $report->user_id === $user->id => 'Anda tidak dapat mendukung laporan Anda sendiri.',
            $report->status->isFinal() => 'Laporan ini sudah ' . strtolower($report->status->label()) . ', dukungan ditutup.',
            default => null,
        };

        if ($error) {
            return $request->expectsJson()
                ? response()->json(['message' => $error], 422)
                : back()->with('error', $error);
        }

        $voted = $report->toggleVote($user);

        if ($request->expectsJson()) {
            return response()->json([
                'voted' => $voted,
                'count' => $report->upvotes_count,
            ]);
        }

        return back()->with('success', $voted ? 'Terima kasih, dukungan Anda tercatat.' : 'Dukungan Anda dicabut.');
    }
}
