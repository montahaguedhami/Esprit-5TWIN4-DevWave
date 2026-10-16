<?php

namespace App\Enums;

enum InfrastructureType: string
{
    case Canalisation = 'Canalisation';
    case Reservoir = 'Réservoir';
    case StationPompage = 'Station de pompage';
    case StationTraitement = 'Station de traitement';
    case Forage = 'Forage';
    case Vanne = 'Vanne';

    public function label(): string
    {
        return $this->value;
    }

    public function icon(): string
    {
        return match ($this) {
            self::Canalisation => 'Waves',
            self::Reservoir => 'Droplet',
            self::StationPompage => 'Gauge',
            self::StationTraitement => 'FlaskConical',
            self::Forage => 'Meter',
            self::Vanne => 'PowerOff',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        $options = [];

        foreach (self::cases() as $type) {
            $options[$type->value] = $type->label();
        }

        return $options;
    }
}
