<?php

namespace Tests\Unit;

use App\Models\MesureQualite;
use App\Models\PointMesure;
use App\Models\Role;
use App\Policies\MesureQualitePolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithRbac;
use Tests\TestCase;

class MesureQualitePolicyTest extends TestCase
{
    use RefreshDatabase;
    use InteractsWithRbac;

    private MesureQualitePolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRbac();
        $this->policy = new MesureQualitePolicy();
    }

    public function test_citizen_can_view_but_cannot_create_update_validate_or_delete(): void
    {
        $citizen = $this->createUserWithRole(Role::CITOYEN);
        $mesure = $this->makeMesure(false);

        $this->assertTrue($this->policy->viewAny($citizen)->allowed());
        $this->assertTrue($this->policy->view($citizen, $mesure)->allowed());
        $this->assertTrue($this->policy->create($citizen)->denied());
        $this->assertTrue($this->policy->update($citizen, $mesure)->denied());
        $this->assertTrue($this->policy->validate($citizen, $mesure)->denied());
        $this->assertTrue($this->policy->delete($citizen, $mesure)->denied());
    }

    public function test_technician_can_create_and_update_unverified_but_cannot_validate(): void
    {
        $tech = $this->createUserWithRole(Role::TECHNICIEN);
        $unverified = $this->makeMesure(false);
        $verified = $this->makeMesure(true);

        $this->assertTrue($this->policy->create($tech)->allowed());
        $this->assertTrue($this->policy->update($tech, $unverified)->allowed());
        $this->assertTrue($this->policy->update($tech, $verified)->denied());
        $this->assertTrue($this->policy->validate($tech, $unverified)->denied());
        $this->assertTrue($this->policy->delete($tech, $unverified)->denied());
    }

    public function test_manager_can_update_verified_measurements_and_validate(): void
    {
        $manager = $this->createUserWithRole(Role::MANAGER);
        $verified = $this->makeMesure(true);

        $this->assertTrue($this->policy->create($manager)->allowed());
        $this->assertTrue($this->policy->update($manager, $verified)->allowed());
        $this->assertTrue($this->policy->validate($manager, $verified)->allowed());
        $this->assertTrue($this->policy->delete($manager, $verified)->allowed());
    }

    public function test_administrator_can_manage_measurements(): void
    {
        $admin = $this->createUserWithRole(Role::ADMINISTRATEUR);
        $mesure = $this->makeMesure(true);

        $this->assertTrue($this->policy->viewAny($admin)->allowed());
        $this->assertTrue($this->policy->create($admin)->allowed());
        $this->assertTrue($this->policy->update($admin, $mesure)->allowed());
        $this->assertTrue($this->policy->validate($admin, $mesure)->allowed());
        $this->assertTrue($this->policy->delete($admin, $mesure)->allowed());
    }

    public function test_technician_who_is_also_manager_can_update_verified_measurements(): void
    {
        $user = $this->createUserWithRole(Role::TECHNICIEN);
        $user->assignRole(Role::MANAGER);
        $user = $user->fresh()->load('roles');
        $verified = $this->makeMesure(true);

        $this->assertTrue($this->policy->update($user, $verified)->allowed());
        $this->assertTrue($this->policy->validate($user, $verified)->allowed());
    }

    private function makeMesure(bool $verified): MesureQualite
    {
        $point = PointMesure::create([
            'code' => 'PT-'.uniqid(),
            'nom' => 'Point test',
            'type' => 'Station',
            'statut' => 'actif',
        ]);

        return MesureQualite::create([
            'reference' => 'MES-'.uniqid(),
            'point_mesure_id' => $point->id,
            'date_mesure' => now(),
            'ph' => 7.2,
            'turbidite' => 0.5,
            'chlore_residuel' => 0.8,
            'plomb' => 2,
            'nitrates' => 10,
            'is_verified' => $verified,
            'overall_compliance' => 100,
            'status' => 'Compliant',
        ]);
    }
}
