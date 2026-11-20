<?php

namespace App\Enums;

enum ReportStatus: string
{
    case Submitted = 'submitted';
    case Verified = 'verified';
    case InProgress = 'in_progress';
    case Done = 'done';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Submitted => 'Diajukan',
            self::Verified => 'Diverifikasi',
            self::InProgress => 'Sedang Dikerjakan',
            self::Done => 'Selesai',
            self::Rejected => 'Ditolak',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Submitted => 'Laporan masuk dan menunggu validasi admin.',
            self::Verified => 'Laporan valid dan sudah didisposisikan ke petugas.',
            self::InProgress => 'Petugas sedang menangani di lapangan.',
            self::Done => 'Perbaikan selesai dan sudah ada foto bukti.',
            self::Rejected => 'Laporan tidak memenuhi syarat atau terindikasi spam.',
        };
    }

    /** Kelas Tailwind untuk badge. */
    public function badge(): string
    {
        return match ($this) {
            self::Submitted => 'bg-slate-100 text-slate-700 ring-slate-300/70',
            self::Verified => 'bg-sky-50 text-sky-700 ring-sky-300/70',
            self::InProgress => 'bg-amber-50 text-amber-700 ring-amber-300/70',
            self::Done => 'bg-emerald-50 text-emerald-700 ring-emerald-300/70',
            self::Rejected => 'bg-rose-50 text-rose-700 ring-rose-300/70',
        };
    }

    public function dot(): string
    {
        return match ($this) {
            self::Submitted => 'bg-slate-400',
            self::Verified => 'bg-sky-500',
            self::InProgress => 'bg-amber-500',
            self::Done => 'bg-emerald-500',
            self::Rejected => 'bg-rose-500',
        };
    }

    /** Warna heksadesimal (dipakai untuk marker peta). */
    public function hex(): string
    {
        return match ($this) {
            self::Submitted => '#64748b',
            self::Verified => '#0ea5e9',
            self::InProgress => '#f59e0b',
            self::Done => '#10b981',
            self::Rejected => '#f43f5e',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Submitted => 'send',
            self::Verified => 'shield-check',
            self::InProgress => 'hammer',
            self::Done => 'badge-check',
            self::Rejected => 'x-circle',
        };
    }

    /** Posisi pada alur 4 langkah (0-3). -1 untuk ditolak. */
    public function step(): int
    {
        return match ($this) {
            self::Submitted => 0,
            self::Verified => 1,
            self::InProgress => 2,
            self::Done => 3,
            self::Rejected => -1,
        };
    }

    public function isFinal(): bool
    {
        return in_array($this, [self::Done, self::Rejected], true);
    }

    /** Alur utama yang ditampilkan ke warga. */
    public static function flow(): array
    {
        return [self::Submitted, self::Verified, self::InProgress, self::Done];
    }

    /** Status yang masih "terbuka" (belum selesai / ditolak). */
    public static function activeValues(): array
    {
        return [self::Submitted->value, self::Verified->value, self::InProgress->value];
    }
}
