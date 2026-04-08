<?php

namespace App\Modules\Tagihan\Enums;

enum TagihanStatus: string
{
    case Draft       = 'draft';
    case Terbit      = 'terbit';
    case Sebagian    = 'sebagian';
    case Lunas       = 'lunas';
    case JatuhTempo  = 'jatuh_tempo';
    case Void        = 'void';

    public function label(): string
    {
        return match($this) {
            self::Draft      => 'Draft',
            self::Terbit     => 'Belum Bayar',
            self::Sebagian   => 'Bayar Sebagian',
            self::Lunas      => 'Lunas',
            self::JatuhTempo => 'Jatuh Tempo',
            self::Void       => 'Dibatalkan',
        };
    }

    /**
     * Apakah tagihan ini masih bisa dibayar?
     */
    public function isBisaBayar(): bool
    {
        return in_array($this, [self::Terbit, self::Sebagian, self::JatuhTempo]);
    }

    /**
     * Apakah ini terminal state (tidak bisa berubah lagi)?
     */
    public function isTerminal(): bool
    {
        return in_array($this, [self::Lunas, self::Void]);
    }

    /**
     * Transisi status yang diizinkan dari state saat ini.
     *
     * @return TagihanStatus[]
     */
    public function allowedTransitions(): array
    {
        return match($this) {
            self::Draft      => [self::Terbit, self::Void],
            self::Terbit     => [self::Sebagian, self::Lunas, self::JatuhTempo, self::Void],
            self::Sebagian   => [self::Lunas, self::JatuhTempo, self::Void],
            self::JatuhTempo => [self::Lunas, self::Void],
            self::Lunas,
            self::Void       => [],   // terminal — tidak ada transisi
        };
    }

    public function canTransitionTo(self $target): bool
    {
        return in_array($target, $this->allowedTransitions());
    }

    /**
     * Warna badge untuk UI.
     */
    public function badgeColor(): string
    {
        return match($this) {
            self::Draft      => 'gray',
            self::Terbit     => 'yellow',
            self::Sebagian   => 'orange',
            self::Lunas      => 'green',
            self::JatuhTempo => 'red',
            self::Void       => 'slate',
        };
    }
}
