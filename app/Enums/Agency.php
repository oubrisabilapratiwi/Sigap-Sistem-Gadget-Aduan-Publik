<?php

namespace App\Enums;

/** Master data instansi / asal dinas petugas lapangan. */
enum Agency: string
{
    case PamJaya = 'pam_jaya';
    case JasaRaharja = 'jasa_raharja';
    case BinaMarga = 'bina_marga';
    case LingkunganHidup = 'lingkungan_hidup';
    case SumberDayaAir = 'sumber_daya_air';
    case PertamananHutanKota = 'pertamanan_hutan_kota';
    case Pln = 'pln';
    case Dishub = 'dishub';

    public function label(): string
    {
        return match ($this) {
            self::PamJaya => 'PAM Jaya',
            self::JasaRaharja => 'Jasa Raharja',
            self::BinaMarga => 'Dinas Bina Marga',
            self::LingkunganHidup => 'Dinas Lingkungan Hidup',
            self::SumberDayaAir => 'Dinas Sumber Daya Air',
            self::PertamananHutanKota => 'Dinas Pertamanan dan Hutan Kota',
            self::Pln => 'PLN',
            self::Dishub => 'Dishub',
        };
    }

    /** Untuk <select>: value => label. */
    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn (self $a) => [$a->value => $a->label()])->all();
    }
}
