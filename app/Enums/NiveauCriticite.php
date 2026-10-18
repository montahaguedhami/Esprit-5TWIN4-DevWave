<?php

namespace App\Enums;

enum NiveauCriticite: string
{
    case Faible = 'faible';
    case Moyen = 'moyen';
    case Critique = 'critique';

    public function label(): string
    {
        return match ($this) {
            self::Faible => 'Faible',
            self::Moyen => 'Moyen',
            self::Critique => 'Critique',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Faible => 'green',
            self::Moyen => 'amber',
            self::Critique => 'rose',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        $options = [];

        foreach (self::cases() as $case) {
            $options[$case->value] = $case->label();
        }

        return $options;
    }
}
