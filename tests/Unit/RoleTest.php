<?php

namespace Tests\Unit;

use App\Models\Role;
use PHPUnit\Framework\TestCase;

class RoleTest extends TestCase
{
    public function test_normalize_slug_maps_aliases_to_official_roles(): void
    {
        $this->assertSame(Role::ADMINISTRATEUR, Role::normalizeSlug('admin'));
        $this->assertSame(Role::ADMINISTRATEUR, Role::normalizeSlug('Administrator'));
        $this->assertSame(Role::ADMINISTRATEUR, Role::normalizeSlug(' administrateur '));
        $this->assertSame(Role::MANAGER, Role::normalizeSlug('manager'));
        $this->assertSame(Role::MANAGER, Role::normalizeSlug('gestionnaire'));
        $this->assertSame(Role::TECHNICIEN, Role::normalizeSlug('technician'));
        $this->assertSame(Role::TECHNICIEN, Role::normalizeSlug('technicien'));
        $this->assertSame(Role::CITOYEN, Role::normalizeSlug('citizen'));
        $this->assertSame(Role::CITOYEN, Role::normalizeSlug('citoyen'));
    }

    public function test_normalize_slug_returns_null_for_null_and_keeps_unknown_values(): void
    {
        $this->assertNull(Role::normalizeSlug(null));
        $this->assertSame('superviseur', Role::normalizeSlug('Superviseur'));
    }

    public function test_official_roles_contains_the_four_aqua_secure_roles(): void
    {
        $this->assertSame([
            Role::ADMINISTRATEUR,
            Role::MANAGER,
            Role::TECHNICIEN,
            Role::CITOYEN,
        ], Role::OFFICIAL_ROLES);
    }

    public function test_label_and_home_route_are_unique_per_official_role(): void
    {
        $this->assertSame('Administrateur', Role::label('admin'));
        $this->assertSame('Gestionnaire', Role::label('gestionnaire'));
        $this->assertSame('Technicien', Role::label('technician'));
        $this->assertSame('Citoyen', Role::label('citizen'));

        $this->assertSame('admin.dashboard', Role::homeRoute(Role::ADMINISTRATEUR));
        $this->assertSame('manager.dashboard', Role::homeRoute(Role::MANAGER));
        $this->assertSame('technician.dashboard', Role::homeRoute(Role::TECHNICIEN));
        $this->assertSame('citizen.dashboard', Role::homeRoute(Role::CITOYEN));
    }
}
