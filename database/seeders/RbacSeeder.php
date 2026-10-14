<?php

namespace Database\Seeders;

use App\Models\AuditLog;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class RbacSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Les 4 rôles officiels d'AquaSecure
        $rolesDefinition = [
            Role::ADMINISTRATEUR => [
                'name' => 'Administrateur',
                'description' => 'Accès complet aux fonctionnalités administratives, gestion des utilisateurs, des rôles et journaux d\'audit.',
                'color' => 'purple',
            ],
            Role::MANAGER => [
                'name' => 'Gestionnaire',
                'description' => 'Gestion opérationnelle, supervision du réseau et des techniciens, validation des mesures et analyse des rapports.',
                'color' => 'blue',
            ],
            Role::TECHNICIEN => [
                'name' => 'Technicien',
                'description' => 'Saisie terrain des mesures de qualité, consultation des interventions et transmission des résultats pour validation.',
                'color' => 'cyan',
            ],
            Role::CITOYEN => [
                'name' => 'Citoyen',
                'description' => 'Consultation publique de la qualité de l\'eau, alertes, et soumission de signalements d\'incidents.',
                'color' => 'emerald',
            ],
        ];

        $roles = [];
        foreach ($rolesDefinition as $slug => $data) {
            $roles[$slug] = Role::updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $data['name'],
                    'description' => $data['description'],
                    'color' => $data['color'],
                ]
            );
        }

        // 2. Permissions granulaires par ressource et action
        $permissionsDefinition = [
            // Users
            ['name' => 'users.view', 'label' => 'Consulter les utilisateurs', 'module' => 'users'],
            ['name' => 'users.create', 'label' => 'Créer des utilisateurs', 'module' => 'users'],
            ['name' => 'users.update', 'label' => 'Modifier les utilisateurs', 'module' => 'users'],
            ['name' => 'users.delete', 'label' => 'Supprimer des utilisateurs', 'module' => 'users'],
            ['name' => 'users.toggle_status', 'label' => 'Activer / désactiver des comptes', 'module' => 'users'],
            ['name' => 'users.assign_roles', 'label' => 'Attribuer des rôles et permissions', 'module' => 'users'],

            // Roles
            ['name' => 'roles.view', 'label' => 'Consulter les rôles', 'module' => 'roles'],
            ['name' => 'roles.manage', 'label' => 'Gérer les rôles et permissions', 'module' => 'roles'],

            // Measurements
            ['name' => 'measurements.view', 'label' => 'Consulter les mesures de qualité', 'module' => 'measurements'],
            ['name' => 'measurements.create', 'label' => 'Saisir une mesure de qualité', 'module' => 'measurements'],
            ['name' => 'measurements.update', 'label' => 'Modifier une mesure de qualité', 'module' => 'measurements'],
            ['name' => 'measurements.validate', 'label' => 'Valider les mesures de qualité', 'module' => 'measurements'],
            ['name' => 'measurements.delete', 'label' => 'Supprimer une mesure de qualité', 'module' => 'measurements'],

            // Reports
            ['name' => 'reports.view', 'label' => 'Consulter les rapports', 'module' => 'reports'],
            ['name' => 'reports.export', 'label' => 'Exporter les rapports', 'module' => 'reports'],

            // Alerts
            ['name' => 'alerts.view', 'label' => 'Consulter les alertes et anomalies', 'module' => 'alerts'],
            ['name' => 'alerts.manage', 'label' => 'Traiter et gérer les alertes', 'module' => 'alerts'],

            // Incidents
            ['name' => 'incidents.view', 'label' => 'Consulter les incidents', 'module' => 'incidents'],
            ['name' => 'incidents.create', 'label' => 'Déclarer un incident', 'module' => 'incidents'],
            ['name' => 'incidents.update', 'label' => 'Modifier un incident', 'module' => 'incidents'],
            ['name' => 'incidents.delete', 'label' => 'Supprimer un incident', 'module' => 'incidents'],

            // Actions correctives
            ['name' => 'actions_correctives.view', 'label' => 'Consulter les actions correctives', 'module' => 'actions_correctives'],
            ['name' => 'actions_correctives.create', 'label' => 'Créer une action corrective', 'module' => 'actions_correctives'],
            ['name' => 'actions_correctives.update', 'label' => 'Modifier une action corrective', 'module' => 'actions_correctives'],
            ['name' => 'actions_correctives.delete', 'label' => 'Supprimer une action corrective', 'module' => 'actions_correctives'],

            // Interventions
            ['name' => 'interventions.view', 'label' => 'Consulter les interventions', 'module' => 'interventions'],
            ['name' => 'interventions.create', 'label' => 'Planifier une intervention', 'module' => 'interventions'],
            ['name' => 'interventions.update', 'label' => 'Mettre à jour une intervention', 'module' => 'interventions'],

            // Projets
            ['name' => 'projets.view', 'label' => 'Consulter les projets', 'module' => 'projets'],
            ['name' => 'projets.create', 'label' => 'Créer un projet', 'module' => 'projets'],
            ['name' => 'projets.update', 'label' => 'Modifier un projet', 'module' => 'projets'],
            ['name' => 'projets.delete', 'label' => 'Supprimer un projet', 'module' => 'projets'],

            // Financements
            ['name' => 'financements.view', 'label' => 'Consulter les financements', 'module' => 'financements'],
            ['name' => 'financements.create', 'label' => 'Ajouter un financement', 'module' => 'financements'],
            ['name' => 'financements.update', 'label' => 'Modifier un financement', 'module' => 'financements'],
            ['name' => 'financements.delete', 'label' => 'Supprimer un financement', 'module' => 'financements'],

            // Audit
            ['name' => 'audit.view', 'label' => 'Consulter les journaux d\'audit', 'module' => 'audit'],

            // Profile
            ['name' => 'profile.view', 'label' => 'Consulter son profil', 'module' => 'profile'],
            ['name' => 'profile.update', 'label' => 'Modifier son profil', 'module' => 'profile'],
        ];

        $permissionModels = [];
        foreach ($permissionsDefinition as $perm) {
            $permissionModels[$perm['name']] = Permission::updateOrCreate(
                ['name' => $perm['name']],
                ['label' => $perm['label'], 'module' => $perm['module']]
            );
        }

        // 3. Attribution des permissions selon le principe du moindre privilège

        // Administrateur: TOUTES les permissions
        $roles[Role::ADMINISTRATEUR]->permissions()->sync(
            collect($permissionModels)->pluck('id')->toArray()
        );

        // Manager (Gestionnaire)
        $managerPerms = [
            'users.view',
            'measurements.view',
            'measurements.update',
            'measurements.validate',
            'measurements.delete',
            'reports.view',
            'reports.export',
            'alerts.view',
            'alerts.manage',
            'incidents.view',
            'incidents.create',
            'incidents.update',
            'incidents.delete',
            'actions_correctives.view',
            'actions_correctives.create',
            'actions_correctives.update',
            'actions_correctives.delete',
            'interventions.view',
            'interventions.create',
            'interventions.update',
            'projets.view',
            'projets.create',
            'projets.update',
            'projets.delete',
            'financements.view',
            'financements.create',
            'financements.update',
            'financements.delete',
            'profile.view',
            'profile.update',
        ];
        $roles[Role::MANAGER]->permissions()->sync(
            collect($permissionModels)->only($managerPerms)->pluck('id')->toArray()
        );

        // Technicien
        $technicienPerms = [
            'measurements.view',
            'measurements.create',
            'measurements.update',
            'interventions.view',
            'interventions.update',
            'alerts.view',
            'profile.view',
            'profile.update',
        ];
        $roles[Role::TECHNICIEN]->permissions()->sync(
            collect($permissionModels)->only($technicienPerms)->pluck('id')->toArray()
        );

        // Citoyen
        $citoyenPerms = [
            'measurements.view',
            'reports.view',
            'alerts.view',
            'incidents.view',
            'incidents.create',
            'incidents.update',
            'projets.view',
            'profile.view',
            'profile.update',
        ];
        $roles[Role::CITOYEN]->permissions()->sync(
            collect($permissionModels)->only($citoyenPerms)->pluck('id')->toArray()
        );

        // 4. Comptes officiels et assignation
        $demoUsers = [
            [
                'name' => 'Amina Kacem',
                'email' => 'admin@aquasecure.tn',
                'password' => 'password123',
                'role' => Role::ADMINISTRATEUR,
            ],
            [
                'name' => 'Ines Mansouri',
                'email' => 'gestionnaire@aquasecure.tn',
                'password' => 'password123',
                'role' => Role::MANAGER,
            ],
            [
                'name' => 'Amira Ben Ali',
                'email' => 'amira@aquasecure.tn',
                'password' => 'password123',
                'role' => Role::TECHNICIEN,
            ],
            [
                'name' => 'Yassine Hamdi',
                'email' => 'citoyen@aquasecure.tn',
                'password' => 'password123',
                'role' => Role::CITOYEN,
            ],
        ];

        foreach ($demoUsers as $data) {
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => Hash::make($data['password']),
                    'is_active' => true,
                ]
            );

            // Assign role
            if (! $user->hasRole($data['role'])) {
                $user->assignRole($data['role']);
            }
        }

        // Assigner un rôle administrateur aux comptes locaux existants si nécessaire
        $firstUser = User::where('email', 'montaha@aquasecure.tn')->first();
        if ($firstUser && ! $firstUser->hasRole(Role::ADMINISTRATEUR)) {
            $firstUser->assignRole(Role::ADMINISTRATEUR);
        }

        // 5. Initial audit log
        AuditLog::record('rbac_initialized', 'Role', null, [
            'roles_count' => count($roles),
            'permissions_count' => count($permissionModels),
        ]);
    }
}
