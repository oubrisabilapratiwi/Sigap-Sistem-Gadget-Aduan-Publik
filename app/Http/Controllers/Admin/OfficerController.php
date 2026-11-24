<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Agency;
use App\Enums\ReportStatus;
use App\Http\Controllers\Controller;
use App\Models\Region;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class OfficerController extends Controller
{
    public function index(): View
    {
        $officers = User::role(User::ROLE_OFFICER)
            ->with('region')
            ->withCount([
                'assignedReports as active_count' => fn ($q) => $q->whereIn('status', ReportStatus::activeValues()),
                'assignedReports as done_count' => fn ($q) => $q->where('status', ReportStatus::Done->value),
            ])
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->get();

        return view('admin.officers.index', [
            'officers' => $officers,
            'regions' => Region::orderBy('name')->get(),
            'agencies' => Agency::cases(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:20'],
            'region_id' => ['required', 'exists:regions,id'],
            'agency' => ['required', Rule::enum(Agency::class)],
            'password' => ['required', 'string', 'min:8'],
        ], ['agency.required' => 'Pilih instansi / asal dinas petugas.']);

        $officer = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'region_id' => $data['region_id'],
            'agency' => $data['agency'],
            'password' => Hash::make($data['password']),
            'is_active' => true,
        ]);
        $officer->assignRole(User::ROLE_OFFICER);

        return back()->with('success', "Akun petugas {$officer->name} berhasil dibuat.");
    }

    /** Aktifkan / nonaktifkan akun petugas. */
    public function toggle(User $user): RedirectResponse
    {
        abort_unless($user->isOfficer(), 404);

        $user->update(['is_active' => ! $user->is_active]);

        return back()->with('success', $user->is_active
            ? "Akun {$user->name} diaktifkan kembali."
            : "Akun {$user->name} dinonaktifkan.");
    }
}
