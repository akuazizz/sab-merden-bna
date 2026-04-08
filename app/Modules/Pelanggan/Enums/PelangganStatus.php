<?php

namespace App\Modules\Pelanggan\Enums;

enum PelangganStatus: string
{
    case Aktif    = 'aktif';
    case Nonaktif = 'nonaktif';

    public function label(): string
    {
        return match($this) {
            self::Aktif    => 'Aktif',
            self::Nonaktif => 'Nonaktif',
        };
    }

    public function isAktif(): bool
    {
        return $this === self::Aktif;
    }

    /**
     * Warna badge untuk UI (Tailwind class).
     */
    public function badgeColor(): string
    {
        return match($this) {
            self::Aktif    => 'green',
            self::Nonaktif => 'gray',
        };
    }
}
