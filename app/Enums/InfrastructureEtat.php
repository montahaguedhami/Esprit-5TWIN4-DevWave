<?php

namespace App\Enums;

enum InfrastructureEtat: string
{
    case EnService = 'En service';
    case EnMaintenance = 'En maintenance';
    case HorsService = 'Hors service';

    public function label(): string
    {
        return $this->value;
    }

    public function color(): string
    {
        return match ($this) {
            self::EnService => 'green',
            self::EnMaintenance => 'amber',
            self::HorsService => 'rose',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::EnService => 'bg-emerald-500/15 text-emerald-300 border-emerald-400/30',
            self::EnMaintenance => 'bg-amber-500/15 text-amber-300 border-amber-400/30',
            self::HorsService => 'bg-rose-500/15 text-rose-300 border-rose-400/30',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        $options = [];

        foreach (self::cases() as $etat) {
            $options[$etat->value] = $etat->label();
        }

        return $options;
    }
}
