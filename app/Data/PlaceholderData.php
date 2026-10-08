<?php

namespace App\Data;

/**
 * Données placeholders pour la démonstration du frontend AquaSecure
 * Ces données seront remplacées par de vraies données provenant de la base de données
 */
class PlaceholderData
{
    /**
     * Zones du réseau d'eau
     */
    public static function zones(): array
    {
        return [
            [
                'id' => 'z1',
                'name' => 'Tunis Nord',
                'emoji' => '🏛️',
                'color' => '#2dd4bf',
                'status' => 'normal',
                'x' => 72,
                'y' => 28,
                'consumption' => 3200,
                'quality' => 98,
                'pressure' => 4.2,
                'population' => 45000,
                'lastUpdate' => '2 min',
                'incidents' => 0,
                'flowRate' => 125
            ],
            [
                'id' => 'z2',
                'name' => 'Tunis Sud',
                'emoji' => '🌆',
                'color' => '#2dd4bf',
                'status' => 'normal',
                'x' => 65,
                'y' => 22,
                'consumption' => 1800,
                'quality' => 97,
                'pressure' => 4.5,
                'population' => 32000,
                'lastUpdate' => '5 min',
                'incidents' => 0,
                'flowRate' => 95
            ],
            [
                'id' => 'z3',
                'name' => 'Ariana',
                'emoji' => '🏘️',
                'color' => '#fbbf24',
                'status' => 'alert',
                'x' => 60,
                'y' => 30,
                'consumption' => 2400,
                'quality' => 92,
                'pressure' => 3.1,
                'population' => 38000,
                'lastUpdate' => '1 min',
                'incidents' => 2,
                'flowRate' => 108
            ],
            [
                'id' => 'z4',
                'name' => 'Ben Arous',
                'emoji' => '🏭',
                'color' => '#ef4444',
                'status' => 'critical',
                'x' => 42,
                'y' => 48,
                'consumption' => 4100,
                'quality' => 78,
                'pressure' => 2.0,
                'population' => 52000,
                'lastUpdate' => '12 min',
                'incidents' => 5,
                'flowRate' => 87
            ],
            [
                'id' => 'z5',
                'name' => 'Sfax Centre',
                'emoji' => '🏢',
                'color' => '#fbbf24',
                'status' => 'alert',
                'x' => 50,
                'y' => 40,
                'consumption' => 5200,
                'quality' => 85,
                'pressure' => 2.8,
                'population' => 68000,
                'lastUpdate' => '3 min',
                'incidents' => 3,
                'flowRate' => 142
            ],
            [
                'id' => 'z6',
                'name' => 'Sfax Sud',
                'emoji' => '🏖️',
                'color' => '#2dd4bf',
                'status' => 'normal',
                'x' => 68,
                'y' => 42,
                'consumption' => 2900,
                'quality' => 96,
                'pressure' => 4.0,
                'population' => 41000,
                'lastUpdate' => '4 min',
                'incidents' => 0,
                'flowRate' => 118
            ],
            [
                'id' => 'z7',
                'name' => 'Sousse Nord',
                'emoji' => '🌊',
                'color' => '#2dd4bf',
                'status' => 'normal',
                'x' => 55,
                'y' => 35,
                'consumption' => 3400,
                'quality' => 95,
                'pressure' => 3.8,
                'population' => 47000,
                'lastUpdate' => '6 min',
                'incidents' => 1,
                'flowRate' => 132
            ],
            [
                'id' => 'z8',
                'name' => 'Sousse Sud',
                'emoji' => '🏝️',
                'color' => '#fbbf24',
                'status' => 'alert',
                'x' => 48,
                'y' => 18,
                'consumption' => 3800,
                'quality' => 88,
                'pressure' => 2.5,
                'population' => 54000,
                'lastUpdate' => '8 min',
                'incidents' => 2,
                'flowRate' => 115
            ],
            [
                'id' => 'z9',
                'name' => 'Monastir',
                'emoji' => '🕌',
                'color' => '#2dd4bf',
                'status' => 'normal',
                'x' => 58,
                'y' => 65,
                'consumption' => 3100,
                'quality' => 94,
                'pressure' => 3.6,
                'population' => 39000,
                'lastUpdate' => '7 min',
                'incidents' => 0,
                'flowRate' => 122
            ],
            [
                'id' => 'z10',
                'name' => 'Nabeul',
                'emoji' => '🎨',
                'color' => '#ef4444',
                'status' => 'critical',
                'x' => 52,
                'y' => 72,
                'consumption' => 2200,
                'quality' => 72,
                'pressure' => 1.5,
                'population' => 28000,
                'lastUpdate' => '15 min',
                'incidents' => 4,
                'flowRate' => 68
            ],
            [
                'id' => 'z11',
                'name' => 'Bizerte',
                'emoji' => '⚓',
                'color' => '#2dd4bf',
                'status' => 'normal',
                'x' => 62,
                'y' => 12,
                'consumption' => 2600,
                'quality' => 97,
                'pressure' => 4.3,
                'population' => 35000,
                'lastUpdate' => '10 min',
                'incidents' => 0,
                'flowRate' => 102
            ],
            [
                'id' => 'z12',
                'name' => 'Gabès',
                'emoji' => '🏜️',
                'color' => '#fbbf24',
                'status' => 'alert',
                'x' => 30,
                'y' => 55,
                'consumption' => 3500,
                'quality' => 83,
                'pressure' => 2.2,
                'population' => 43000,
                'lastUpdate' => '11 min',
                'incidents' => 2,
                'flowRate' => 98
            ],
        ];
    }

    /**
     * Statistiques des zones par statut
     */
    public static function zoneStats(): array
    {
        $zones = self::zones();
        return [
            'normal' => count(array_filter($zones, fn($z) => $z['status'] === 'normal')),
            'alert' => count(array_filter($zones, fn($z) => $z['status'] === 'alert')),
            'critical' => count(array_filter($zones, fn($z) => $z['status'] === 'critical')),
        ];
    }

    /**
     * Statistiques globales du système
     */
    public static function stats(): array
    {
        $zones = self::zones();
        return [
            'totalZones' => count($zones),
            'activeIncidents' => 8,
            'responseTime' => '2.4h',
            'totalUsers' => 1247,
            'systemUptime' => '99.7%',
        ];
    }

    /**
     * Labels des couleurs par statut
     */
    public static function zoneColors(): array
    {
        return [
            'normal' => '#2dd4bf',
            'alert' => '#fbbf24',
            'critical' => '#ef4444',
        ];
    }

    /**
     * Labels des statuts
     */
    public static function zoneLabels(): array
    {
        return [
            'normal' => 'Normal',
            'alert' => 'Alerte',
            'critical' => 'Critique',
        ];
    }

    /**
     * KPIs pour la landing page
     */
    public static function landingKPIs(): array
    {
        return [
            ['icon' => 'activity', 'value' => '12', 'label' => 'Zones surveillées'],
            ['icon' => 'droplet', 'value' => '38 200', 'label' => 'm³ / jour'],
            ['icon' => 'shield', 'value' => '91%', 'label' => 'Qualité moyenne'],
        ];
    }

    /**
     * Trouver une zone par ID
     */
    public static function findZone(string $id): ?array
    {
        $zones = self::zones();
        $filtered = array_filter($zones, fn($z) => $z['id'] === $id);
        return !empty($filtered) ? array_values($filtered)[0] : null;
    }

    /**
     * Formater un nombre pour l'affichage
     */
    public static function formatNumber(int $number): string
    {
        return number_format($number, 0, ',', ' ');
    }

    /**
     * Rapports citoyens (déclarations)
     */
    public static function citizenReports(): array
    {
        return [
            [
                'id' => 'INC-2026-089',
                'type' => 'Fuite d\'eau',
                'zone' => 'Tunis Nord',
                'status' => 'in_progress',
                'priority' => 'high',
                'created_at' => '2026-09-24 09:15',
                'updated_at' => '2026-09-25 14:30',
                'description' => 'Fuite importante détectée sur la conduite principale, rue Habib Bourguiba',
                'address' => '42 Rue Habib Bourguiba, Tunis',
                'assigned_to' => 'Équipe Alpha - Amira Ben Ali',
                'estimated_resolution' => '2026-09-26 16:00',
            ],
            [
                'id' => 'INC-2026-076',
                'type' => 'Qualité de l\'eau',
                'zone' => 'Ariana',
                'status' => 'resolved',
                'priority' => 'medium',
                'created_at' => '2026-09-20 14:22',
                'updated_at' => '2026-09-22 11:45',
                'description' => 'Eau trouble avec odeur inhabituelle',
                'address' => '15 Avenue de la République, Ariana',
                'assigned_to' => 'Équipe Beta',
                'resolved_at' => '2026-09-22 11:45',
            ],
            [
                'id' => 'INC-2026-062',
                'type' => 'Pression insuffisante',
                'zone' => 'Tunis Sud',
                'status' => 'pending',
                'priority' => 'low',
                'created_at' => '2026-09-18 08:30',
                'updated_at' => '2026-09-18 08:30',
                'description' => 'Faible pression d\'eau aux étages supérieurs',
                'address' => '28 Rue de Marseille, Tunis',
                'assigned_to' => null,
                'estimated_resolution' => null,
            ],
        ];
    }

    /**
     * Notifications citoyens
     */
    public static function citizenNotifications(): array
    {
        return [
            [
                'id' => 'notif-1',
                'type' => 'report_update',
                'title' => 'Votre déclaration #INC-2026-089 a été mise à jour',
                'message' => 'Un technicien a été assigné et interviendra demain matin',
                'time' => '2 heures',
                'read' => false,
                'icon' => 'wrench',
                'color' => 'cyan',
            ],
            [
                'id' => 'notif-2',
                'type' => 'water_quality',
                'title' => 'Alerte qualité de l\'eau',
                'message' => 'Analyse en cours dans votre zone - Tunis Nord',
                'time' => '5 heures',
                'read' => false,
                'icon' => 'flask-conical',
                'color' => 'amber',
            ],
            [
                'id' => 'notif-3',
                'type' => 'maintenance',
                'title' => 'Maintenance programmée',
                'message' => 'Interruption prévue le 28/09 de 9h à 12h',
                'time' => '1 jour',
                'read' => true,
                'icon' => 'calendar',
                'color' => 'blue',
            ],
            [
                'id' => 'notif-4',
                'type' => 'report_resolved',
                'title' => 'Problème résolu #INC-2026-076',
                'message' => 'Le problème de qualité d\'eau a été résolu',
                'time' => '2 jours',
                'read' => true,
                'icon' => 'check-circle',
                'color' => 'emerald',
            ],
            [
                'id' => 'notif-5',
                'type' => 'invoice',
                'title' => 'Nouvelle facture disponible',
                'message' => 'Facture de septembre 2026 - 87.50 TND',
                'time' => '3 jours',
                'read' => true,
                'icon' => 'receipt',
                'color' => 'purple',
            ],
        ];
    }

    /**
     * Factures citoyens
     */
    public static function citizenInvoices(): array
    {
        return [
            [
                'id' => 'INV-2026-09-001',
                'month' => 'Septembre 2026',
                'amount' => 87.50,
                'status' => 'pending',
                'due_date' => '2026-10-05',
                'consumption' => 18.5,
                'issue_date' => '2026-09-25',
            ],
            [
                'id' => 'INV-2026-08-001',
                'month' => 'Août 2026',
                'amount' => 92.30,
                'status' => 'paid',
                'due_date' => '2026-09-05',
                'consumption' => 19.8,
                'issue_date' => '2026-08-25',
                'paid_date' => '2026-09-02',
            ],
            [
                'id' => 'INV-2026-07-001',
                'month' => 'Juillet 2026',
                'amount' => 95.75,
                'status' => 'paid',
                'due_date' => '2026-08-05',
                'consumption' => 20.5,
                'issue_date' => '2026-07-25',
                'paid_date' => '2026-08-01',
            ],
        ];
    }

    /**
     * Interventions technicien
     */
    public static function technicianInterventions(): array
    {
        return [
            [
                'id' => 'INT-089',
                'incident_id' => 'INC-2026-089',
                'type' => 'Fuite d\'eau',
                'status' => 'in_progress',
                'priority' => 'high',
                'zone' => 'Tunis Nord',
                'address' => '42 Rue Habib Bourguiba, Tunis',
                'scheduled_time' => '2026-09-26 09:00',
                'started_at' => '2026-09-26 09:15',
                'estimated_duration' => '2 heures',
                'description' => 'Fuite importante sur conduite principale',
                'equipment_needed' => ['Clé à molette', 'Joint d\'étanchéité', 'Ruban Téflon'],
                'team' => 'Équipe Alpha',
                'contact' => '+216 71 234 567',
            ],
            [
                'id' => 'INT-085',
                'incident_id' => 'INC-2026-085',
                'type' => 'Maintenance préventive',
                'status' => 'scheduled',
                'priority' => 'medium',
                'zone' => 'Ariana',
                'address' => '28 Avenue de la République, Ariana',
                'scheduled_time' => '2026-09-26 14:00',
                'started_at' => null,
                'estimated_duration' => '1.5 heures',
                'description' => 'Inspection vanne de sectionnement',
                'equipment_needed' => ['Clé spéciale', 'Lubrifiant'],
                'team' => 'Équipe Alpha',
                'contact' => '+216 71 234 567',
            ],
            [
                'id' => 'INT-082',
                'incident_id' => 'INC-2026-082',
                'type' => 'Réparation compteur',
                'status' => 'completed',
                'priority' => 'low',
                'zone' => 'Tunis Sud',
                'address' => '15 Rue de Marseille, Tunis',
                'scheduled_time' => '2026-09-25 10:00',
                'started_at' => '2026-09-25 10:30',
                'completed_at' => '2026-09-25 11:45',
                'estimated_duration' => '1 heure',
                'description' => 'Remplacement compteur défectueux',
                'equipment_needed' => ['Compteur neuf', 'Joint'],
                'team' => 'Équipe Alpha',
                'contact' => '+216 71 234 567',
            ],
            [
                'id' => 'INT-079',
                'incident_id' => 'INC-2026-079',
                'type' => 'Qualité de l\'eau',
                'status' => 'completed',
                'priority' => 'high',
                'zone' => 'Ariana',
                'address' => '8 Boulevard Habib Thameur, Ariana',
                'scheduled_time' => '2026-09-24 08:00',
                'started_at' => '2026-09-24 08:20',
                'completed_at' => '2026-09-24 10:30',
                'estimated_duration' => '2 heures',
                'description' => 'Prélèvement et analyse eau trouble',
                'equipment_needed' => ['Kit analyse', 'Flacons stériles'],
                'team' => 'Équipe Alpha',
                'contact' => '+216 71 234 567',
            ],
        ];
    }

    /**
     * Équipement technicien
     */
    public static function technicianEquipment(): array
    {
        return [
            [
                'id' => 'EQ-001',
                'name' => 'Clé à molette professionnelle',
                'category' => 'Outillage',
                'status' => 'available',
                'quantity' => 3,
                'location' => 'Camion Alpha-1',
                'last_maintenance' => '2026-09-01',
                'next_maintenance' => '2026-12-01',
            ],
            [
                'id' => 'EQ-015',
                'name' => 'Détecteur de fuite électronique',
                'category' => 'Électronique',
                'status' => 'available',
                'quantity' => 1,
                'location' => 'Camion Alpha-1',
                'last_maintenance' => '2026-08-15',
                'next_maintenance' => '2026-11-15',
            ],
            [
                'id' => 'EQ-023',
                'name' => 'Kit joints d\'étanchéité',
                'category' => 'Consommables',
                'status' => 'low_stock',
                'quantity' => 5,
                'location' => 'Dépôt central',
                'last_maintenance' => null,
                'next_maintenance' => null,
            ],
            [
                'id' => 'EQ-042',
                'name' => 'Pompe submersible portable',
                'category' => 'Équipement lourd',
                'status' => 'in_use',
                'quantity' => 1,
                'location' => 'Intervention INT-089',
                'last_maintenance' => '2026-07-20',
                'next_maintenance' => '2026-10-20',
            ],
            [
                'id' => 'EQ-058',
                'name' => 'Caméra d\'inspection canalisation',
                'category' => 'Électronique',
                'status' => 'maintenance',
                'quantity' => 1,
                'location' => 'Atelier',
                'last_maintenance' => '2026-09-20',
                'next_maintenance' => '2026-09-27',
            ],
        ];
    }

    /**
     * Zones assignées au technicien
     */
    public static function technicianZones(): array
    {
        $allZones = self::zones();
        return array_filter($allZones, fn($z) => in_array($z['id'], ['z1', 'z2', 'z3']));
    }

    /**
     * Utilisateurs système (admin)
     */
    public static function adminUsers(): array
    {
        return [
            [
                'id' => 1,
                'name' => 'Ahmed Administrateur',
                'email' => 'admin@aquasecure.tn',
                'role' => 'admin',
                'status' => 'active',
                'avatar' => null,
                'phone' => '+216 71 123 456',
                'last_login' => '2026-09-26 08:30',
                'created_at' => '2025-01-15',
                'permissions' => ['all'],
            ],
            [
                'id' => 2,
                'name' => 'Sara Gestionnaire',
                'email' => 'gestionnaire@aquasecure.tn',
                'role' => 'manager',
                'status' => 'active',
                'avatar' => null,
                'phone' => '+216 71 234 567',
                'last_login' => '2026-09-26 07:15',
                'created_at' => '2025-02-20',
                'permissions' => ['incidents', 'teams', 'reports', 'analytics'],
            ],
            [
                'id' => 3,
                'name' => 'Amira Ben Ali',
                'email' => 'amira@aquasecure.tn',
                'role' => 'technician',
                'status' => 'active',
                'avatar' => null,
                'phone' => '+216 98 765 432',
                'last_login' => '2026-09-26 09:00',
                'created_at' => '2025-03-10',
                'permissions' => ['interventions', 'equipment'],
            ],
            [
                'id' => 4,
                'name' => 'Mohamed Citoyen',
                'email' => 'citoyen@aquasecure.tn',
                'role' => 'citizen',
                'status' => 'active',
                'avatar' => null,
                'phone' => '+216 22 456 789',
                'last_login' => '2026-09-25 18:30',
                'created_at' => '2025-06-01',
                'permissions' => ['reports', 'invoices', 'notifications'],
            ],
            [
                'id' => 5,
                'name' => 'Leila Technicienne',
                'email' => 'leila.tech@aquasecure.tn',
                'role' => 'technician',
                'status' => 'active',
                'avatar' => null,
                'phone' => '+216 97 123 654',
                'last_login' => '2026-09-25 16:45',
                'created_at' => '2025-04-12',
                'permissions' => ['interventions', 'equipment'],
            ],
            [
                'id' => 6,
                'name' => 'Karim Manager',
                'email' => 'karim@aquasecure.tn',
                'role' => 'manager',
                'status' => 'inactive',
                'avatar' => null,
                'phone' => '+216 71 987 654',
                'last_login' => '2026-08-15 11:20',
                'created_at' => '2025-05-08',
                'permissions' => ['incidents', 'teams', 'reports'],
            ],
        ];
    }

    /**
     * Statistiques utilisateurs
     */
    public static function adminUserStats(): array
    {
        $users = self::adminUsers();
        return [
            'total' => count($users),
            'active' => count(array_filter($users, fn($u) => $u['status'] === 'active')),
            'admins' => count(array_filter($users, fn($u) => $u['role'] === 'admin')),
            'managers' => count(array_filter($users, fn($u) => $u['role'] === 'manager')),
            'technicians' => count(array_filter($users, fn($u) => $u['role'] === 'technician')),
            'citizens' => count(array_filter($users, fn($u) => $u['role'] === 'citizen')),
        ];
    }

    /**
     * Rôles et permissions
     */
    public static function adminRoles(): array
    {
        return [
            [
                'id' => 'admin',
                'name' => 'Administrateur',
                'description' => 'Accès complet au système',
                'users_count' => 1,
                'color' => 'purple',
                'permissions' => [
                    'users' => ['create', 'read', 'update', 'delete'],
                    'roles' => ['create', 'read', 'update', 'delete'],
                    'system' => ['read', 'update'],
                    'incidents' => ['create', 'read', 'update', 'delete'],
                    'teams' => ['create', 'read', 'update', 'delete'],
                    'reports' => ['create', 'read', 'update', 'delete'],
                    'analytics' => ['read'],
                ],
            ],
            [
                'id' => 'manager',
                'name' => 'Gestionnaire',
                'description' => 'Gestion des équipes et incidents',
                'users_count' => 2,
                'color' => 'blue',
                'permissions' => [
                    'incidents' => ['create', 'read', 'update'],
                    'teams' => ['read', 'update'],
                    'reports' => ['read'],
                    'analytics' => ['read'],
                ],
            ],
            [
                'id' => 'technician',
                'name' => 'Technicien',
                'description' => 'Interventions terrain',
                'users_count' => 2,
                'color' => 'cyan',
                'permissions' => [
                    'interventions' => ['read', 'update'],
                    'equipment' => ['read', 'update'],
                    'reports' => ['create'],
                ],
            ],
            [
                'id' => 'citizen',
                'name' => 'Citoyen',
                'description' => 'Signalement et consultation',
                'users_count' => 1,
                'color' => 'emerald',
                'permissions' => [
                    'reports' => ['create', 'read'],
                    'invoices' => ['read'],
                    'notifications' => ['read'],
                ],
            ],
        ];
    }

    /**
     * Logs système
     */
    public static function adminLogs(): array
    {
        return [
            [
                'id' => 'log-1',
                'type' => 'user_login',
                'user' => 'admin@aquasecure.tn',
                'action' => 'Connexion réussie',
                'ip' => '197.15.232.10',
                'timestamp' => '2026-09-26 08:30:15',
                'level' => 'info',
            ],
            [
                'id' => 'log-2',
                'type' => 'incident_created',
                'user' => 'citoyen@aquasecure.tn',
                'action' => 'Création incident INC-2026-089',
                'ip' => '197.15.232.45',
                'timestamp' => '2026-09-24 09:15:42',
                'level' => 'info',
            ],
            [
                'id' => 'log-3',
                'type' => 'system_alert',
                'user' => 'system',
                'action' => 'Pression critique détectée - Zone Ben Arous',
                'ip' => null,
                'timestamp' => '2026-09-26 07:22:18',
                'level' => 'warning',
            ],
            [
                'id' => 'log-4',
                'type' => 'failed_login',
                'user' => 'unknown@example.com',
                'action' => 'Tentative de connexion échouée',
                'ip' => '103.45.78.92',
                'timestamp' => '2026-09-26 03:15:30',
                'level' => 'error',
            ],
            [
                'id' => 'log-5',
                'type' => 'user_updated',
                'user' => 'admin@aquasecure.tn',
                'action' => 'Modification utilisateur #6',
                'ip' => '197.15.232.10',
                'timestamp' => '2026-09-25 14:45:22',
                'level' => 'info',
            ],
        ];
    }

    /**
     * Métriques système
     */
    public static function adminSystemMetrics(): array
    {
        return [
            'uptime' => '99.7%',
            'cpu_usage' => 45,
            'memory_usage' => 62,
            'disk_usage' => 38,
            'database_size' => '2.4 GB',
            'active_sessions' => 24,
            'api_requests_today' => 12847,
            'avg_response_time' => '142ms',
        ];
    }

    /**
     * Sauvegardes système
     */
    public static function adminBackups(): array
    {
        return [
            [
                'id' => 'backup-1',
                'name' => 'backup-2026-09-26-daily.sql',
                'type' => 'automatic',
                'size' => '2.4 GB',
                'status' => 'completed',
                'created_at' => '2026-09-26 02:00',
                'duration' => '4m 32s',
            ],
            [
                'id' => 'backup-2',
                'name' => 'backup-2026-09-25-daily.sql',
                'type' => 'automatic',
                'size' => '2.3 GB',
                'status' => 'completed',
                'created_at' => '2026-09-25 02:00',
                'duration' => '4m 18s',
            ],
            [
                'id' => 'backup-3',
                'name' => 'backup-2026-09-23-manual.sql',
                'type' => 'manual',
                'size' => '2.2 GB',
                'status' => 'completed',
                'created_at' => '2026-09-23 16:30',
                'duration' => '4m 45s',
            ],
        ];
    }

    /**
     * Alertes sécurité
     */
    public static function adminSecurityAlerts(): array
    {
        return [
            [
                'id' => 'alert-1',
                'type' => 'failed_login_attempts',
                'severity' => 'medium',
                'message' => '5 tentatives de connexion échouées depuis 103.45.78.92',
                'timestamp' => '2026-09-26 03:15',
                'status' => 'active',
            ],
            [
                'id' => 'alert-2',
                'type' => 'unusual_activity',
                'severity' => 'low',
                'message' => 'Activité inhabituelle détectée - utilisateur #4',
                'timestamp' => '2026-09-25 22:30',
                'status' => 'resolved',
            ],
            [
                'id' => 'alert-3',
                'type' => 'api_rate_limit',
                'severity' => 'high',
                'message' => 'Limite API dépassée - IP 45.67.89.12',
                'timestamp' => '2026-09-25 18:45',
                'status' => 'blocked',
            ],
        ];
    }

    /**
     * Données des zones avec coordonnées GPS réelles en Tunisie
     * pour l'affichage sur carte OpenStreetMap / Leaflet
     */
    public static function mapZones(): array
    {
        return [
            [
                'id' => 'z1',
                'name' => 'Tunis Nord',
                'lat' => 36.8568,
                'lng' => 10.1853,
                'status' => 'normal',
                'color' => '#2dd4bf',
                'quality' => 98,
                'pressure' => 4.2,
                'incidents' => 0,
                'consumption' => 3200,
                'population' => 45000,
                'flowRate' => 125,
                'lastUpdate' => '2 min',
                'address' => 'La Marsa, Tunis',
                'technician' => 'Équipe Alpha',
                'emoji' => '🏛️',
            ],
            [
                'id' => 'z2',
                'name' => 'Tunis Centre',
                'lat' => 36.8190,
                'lng' => 10.1657,
                'status' => 'normal',
                'color' => '#2dd4bf',
                'quality' => 97,
                'pressure' => 4.5,
                'incidents' => 0,
                'consumption' => 1800,
                'population' => 32000,
                'flowRate' => 95,
                'lastUpdate' => '5 min',
                'address' => 'Avenue Habib Bourguiba, Tunis',
                'technician' => 'Équipe Beta',
                'emoji' => '🌆',
            ],
            [
                'id' => 'z3',
                'name' => 'Ariana',
                'lat' => 36.8665,
                'lng' => 10.1647,
                'status' => 'alert',
                'color' => '#fbbf24',
                'quality' => 92,
                'pressure' => 3.1,
                'incidents' => 2,
                'consumption' => 2400,
                'population' => 38000,
                'flowRate' => 108,
                'lastUpdate' => '1 min',
                'address' => 'Centre Ariana',
                'technician' => 'Équipe Alpha',
                'emoji' => '🏘️',
            ],
            [
                'id' => 'z4',
                'name' => 'Ben Arous',
                'lat' => 36.7533,
                'lng' => 10.2286,
                'status' => 'critical',
                'color' => '#ef4444',
                'quality' => 78,
                'pressure' => 2.0,
                'incidents' => 5,
                'consumption' => 4100,
                'population' => 52000,
                'flowRate' => 87,
                'lastUpdate' => '12 min',
                'address' => 'Ben Arous Centre',
                'technician' => 'Équipe Gamma',
                'emoji' => '🏭',
            ],
            [
                'id' => 'z5',
                'name' => 'Sfax Nord',
                'lat' => 34.7400,
                'lng' => 10.7600,
                'status' => 'alert',
                'color' => '#fbbf24',
                'quality' => 85,
                'pressure' => 2.8,
                'incidents' => 3,
                'consumption' => 5200,
                'population' => 68000,
                'flowRate' => 142,
                'lastUpdate' => '3 min',
                'address' => 'Sfax Nord',
                'technician' => 'Équipe Delta',
                'emoji' => '🏢',
            ],
            [
                'id' => 'z6',
                'name' => 'Sfax Centre',
                'lat' => 34.7406,
                'lng' => 10.7603,
                'status' => 'normal',
                'color' => '#2dd4bf',
                'quality' => 96,
                'pressure' => 4.0,
                'incidents' => 0,
                'consumption' => 2900,
                'population' => 41000,
                'flowRate' => 118,
                'lastUpdate' => '4 min',
                'address' => 'Sfax Medina',
                'technician' => 'Équipe Delta',
                'emoji' => '🏖️',
            ],
            [
                'id' => 'z7',
                'name' => 'Sousse',
                'lat' => 35.8245,
                'lng' => 10.6346,
                'status' => 'normal',
                'color' => '#2dd4bf',
                'quality' => 95,
                'pressure' => 3.8,
                'incidents' => 1,
                'consumption' => 3400,
                'population' => 47000,
                'flowRate' => 132,
                'lastUpdate' => '6 min',
                'address' => 'Sousse Ville',
                'technician' => 'Équipe Epsilon',
                'emoji' => '🌊',
            ],
            [
                'id' => 'z8',
                'name' => 'Monastir',
                'lat' => 35.7643,
                'lng' => 10.8113,
                'status' => 'normal',
                'color' => '#2dd4bf',
                'quality' => 94,
                'pressure' => 3.6,
                'incidents' => 0,
                'consumption' => 3100,
                'population' => 39000,
                'flowRate' => 122,
                'lastUpdate' => '7 min',
                'address' => 'Monastir Centre',
                'technician' => 'Équipe Epsilon',
                'emoji' => '🕌',
            ],
            [
                'id' => 'z9',
                'name' => 'Bizerte',
                'lat' => 37.2744,
                'lng' => 9.8739,
                'status' => 'normal',
                'color' => '#2dd4bf',
                'quality' => 97,
                'pressure' => 4.3,
                'incidents' => 0,
                'consumption' => 2600,
                'population' => 35000,
                'flowRate' => 102,
                'lastUpdate' => '10 min',
                'address' => 'Bizerte Port',
                'technician' => 'Équipe Zeta',
                'emoji' => '⚓',
            ],
            [
                'id' => 'z10',
                'name' => 'Nabeul',
                'lat' => 36.4523,
                'lng' => 10.7355,
                'status' => 'critical',
                'color' => '#ef4444',
                'quality' => 72,
                'pressure' => 1.5,
                'incidents' => 4,
                'consumption' => 2200,
                'population' => 28000,
                'flowRate' => 68,
                'lastUpdate' => '15 min',
                'address' => 'Nabeul Ville',
                'technician' => 'Équipe Zeta',
                'emoji' => '🎨',
            ],
            [
                'id' => 'z11',
                'name' => 'Gabès',
                'lat' => 33.8814,
                'lng' => 10.0982,
                'status' => 'alert',
                'color' => '#fbbf24',
                'quality' => 83,
                'pressure' => 2.2,
                'incidents' => 2,
                'consumption' => 3500,
                'population' => 43000,
                'flowRate' => 98,
                'lastUpdate' => '11 min',
                'address' => 'Gabès Centre',
                'technician' => 'Équipe Eta',
                'emoji' => '🏜️',
            ],
            [
                'id' => 'z12',
                'name' => 'Kairouan',
                'lat' => 35.6712,
                'lng' => 10.1001,
                'status' => 'alert',
                'color' => '#fbbf24',
                'quality' => 88,
                'pressure' => 2.5,
                'incidents' => 2,
                'consumption' => 3800,
                'population' => 54000,
                'flowRate' => 115,
                'lastUpdate' => '8 min',
                'address' => 'Kairouan Médina',
                'technician' => 'Équipe Eta',
                'emoji' => '🕍',
            ],
        ];
    }

    /**
     * Pipelines / conduites reliant les zones (pour affichage sur carte)
     */
    public static function mapPipelines(): array
    {
        return [
            ['from' => 'z1', 'to' => 'z2', 'status' => 'normal', 'diameter' => 300, 'pressure' => 4.2, 'flow' => 120],
            ['from' => 'z2', 'to' => 'z3', 'status' => 'alert',  'diameter' => 250, 'pressure' => 3.1, 'flow' => 95],
            ['from' => 'z2', 'to' => 'z4', 'status' => 'critical','diameter' => 400, 'pressure' => 2.0, 'flow' => 80],
            ['from' => 'z1', 'to' => 'z9', 'status' => 'normal', 'diameter' => 200, 'pressure' => 4.3, 'flow' => 60],
            ['from' => 'z7', 'to' => 'z8', 'status' => 'normal', 'diameter' => 300, 'pressure' => 3.8, 'flow' => 110],
            ['from' => 'z5', 'to' => 'z6', 'status' => 'normal', 'diameter' => 350, 'pressure' => 4.0, 'flow' => 130],
            ['from' => 'z7', 'to' => 'z12', 'status' => 'alert', 'diameter' => 250, 'pressure' => 2.5, 'flow' => 85],
            ['from' => 'z11', 'to' => 'z5', 'status' => 'normal', 'diameter' => 200, 'pressure' => 3.5, 'flow' => 75],
        ];
    }

    /**
     * Données analytiques mensuelles (12 mois)
     */
    public static function analyticsMonthly(): array
    {
        return [
            'labels' => ['Oct', 'Nov', 'Déc', 'Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Jun', 'Jul', 'Aoû', 'Sep'],
            'consumption' => [32400, 30100, 29800, 28900, 31200, 33500, 35100, 37800, 40200, 43600, 42100, 38200],
            'incidents'   => [12, 9, 8, 11, 7, 14, 10, 13, 16, 9, 11, 8],
            'quality'     => [94, 95, 96, 93, 97, 95, 92, 94, 91, 93, 94, 96],
            'pressure'    => [3.8, 3.9, 4.1, 4.2, 4.0, 3.7, 3.6, 3.5, 3.4, 3.6, 3.8, 4.0],
            'resolved'    => [10, 9, 8, 10, 7, 12, 9, 11, 14, 8, 10, 8],
        ];
    }

    /**
     * KPIs analytiques comparés (mois actuel vs précédent)
     */
    public static function analyticsKPIs(): array
    {
        return [
            [
                'label'  => 'Consommation totale',
                'value'  => '38 200 m³',
                'prev'   => '42 100 m³',
                'change' => -9.3,
                'icon'   => 'droplet',
                'color'  => 'cyan',
                'unit'   => 'm³/j',
            ],
            [
                'label'  => 'Qualité moyenne',
                'value'  => '91.4 %',
                'prev'   => '93.7 %',
                'change' => -2.3,
                'icon'   => 'flask-conical',
                'color'  => 'teal',
                'unit'   => '%',
            ],
            [
                'label'  => 'Incidents ouverts',
                'value'  => '8',
                'prev'   => '11',
                'change' => +27.3,
                'icon'   => 'alert-triangle',
                'color'  => 'orange',
                'unit'   => '',
            ],
            [
                'label'  => 'Taux de résolution',
                'value'  => '94.7 %',
                'prev'   => '90.9 %',
                'change' => +3.8,
                'icon'   => 'check-circle',
                'color'  => 'emerald',
                'unit'   => '%',
            ],
            [
                'label'  => 'Pression moyenne',
                'value'  => '3.42 bar',
                'prev'   => '3.56 bar',
                'change' => -3.9,
                'icon'   => 'gauge',
                'color'  => 'blue',
                'unit'   => 'bar',
            ],
            [
                'label'  => 'Interventions',
                'value'  => '47',
                'prev'   => '38',
                'change' => +23.7,
                'icon'   => 'wrench',
                'color'  => 'purple',
                'unit'   => '',
            ],
        ];
    }

    /**
     * Répartition des incidents par type
     */
    public static function analyticsIncidentTypes(): array
    {
        return [
            ['type' => 'Fuite d\'eau',         'count' => 34, 'color' => '#ef4444', 'pct' => 36],
            ['type' => 'Qualité dégradée',     'count' => 22, 'color' => '#f97316', 'pct' => 23],
            ['type' => 'Pression insuffisante','count' => 19, 'color' => '#fbbf24', 'pct' => 20],
            ['type' => 'Coupure réseau',       'count' => 12, 'color' => '#60a5fa', 'pct' => 13],
            ['type' => 'Compteur défaillant',  'count' =>  8, 'color' => '#a78bfa', 'pct' =>  8],
        ];
    }

    /**
     * Performance des équipes techniciens
     */
    public static function analyticsTeamPerformance(): array
    {
        return [
            ['team' => 'Équipe Alpha', 'interventions' => 18, 'resolved' => 17, 'avg_time' => '1h 42min', 'score' => 94, 'color' => '#2dd4bf'],
            ['team' => 'Équipe Beta',  'interventions' => 14, 'resolved' => 13, 'avg_time' => '2h 05min', 'score' => 88, 'color' => '#38bdf8'],
            ['team' => 'Équipe Gamma', 'interventions' => 11, 'resolved' => 10, 'avg_time' => '2h 30min', 'score' => 82, 'color' => '#818cf8'],
            ['team' => 'Équipe Delta', 'interventions' =>  9, 'resolved' =>  9, 'avg_time' => '1h 55min', 'score' => 97, 'color' => '#34d399'],
            ['team' => 'Équipe Epsilon','interventions'=> 7,  'resolved' =>  6, 'avg_time' => '3h 10min', 'score' => 75, 'color' => '#fb923c'],
        ];
    }

    /**
     * Consommation hebdomadaire par zone (Top 5)
     */
    public static function analyticsWeeklyByZone(): array
    {
        return [
            'labels' => ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'],
            'zones'  => [
                ['name' => 'Sfax Nord',    'data' => [820, 840, 790, 860, 810, 750, 720], 'color' => '#fbbf24'],
                ['name' => 'Tunis Nord',   'data' => [540, 560, 520, 580, 550, 490, 480], 'color' => '#2dd4bf'],
                ['name' => 'Ben Arous',    'data' => [670, 690, 650, 710, 680, 620, 600], 'color' => '#ef4444'],
                ['name' => 'Sousse',       'data' => [480, 500, 460, 520, 490, 440, 420], 'color' => '#38bdf8'],
                ['name' => 'Kairouan',     'data' => [390, 410, 380, 430, 400, 360, 340], 'color' => '#a78bfa'],
            ],
        ];
    }

    /**
     * Tous les comptes utilisateurs avec statut actif/bloqué pour gestion Admin
     */
    public static function adminAllAccounts(): array
    {
        return [
            [
                'id'        => 1,
                'name'      => 'Amina Kacem',
                'email'     => 'admin@aquasecure.tn',
                'role'      => 'admin',
                'status'    => 'active',
                'initials'  => 'AK',
                'phone'     => '+216 71 123 456',
                'zone'      => 'Toutes zones',
                'joined'    => '15 jan. 2025',
                'last_seen' => 'À l\'instant',
                'reports'   => 0,
            ],
            [
                'id'        => 2,
                'name'      => 'Ines Mansouri',
                'email'     => 'gestionnaire@aquasecure.tn',
                'role'      => 'manager',
                'status'    => 'active',
                'initials'  => 'IM',
                'phone'     => '+216 71 234 567',
                'zone'      => 'Grand Tunis',
                'joined'    => '20 fév. 2025',
                'last_seen' => 'Il y a 5 min',
                'reports'   => 5,
            ],
            [
                'id'        => 3,
                'name'      => 'Amira Ben Ali',
                'email'     => 'amira@aquasecure.tn',
                'role'      => 'technician',
                'status'    => 'active',
                'initials'  => 'AB',
                'phone'     => '+216 98 765 432',
                'zone'      => 'Tunis Nord / Ariana',
                'joined'    => '10 mar. 2025',
                'last_seen' => 'Il y a 12 min',
                'reports'   => 2,
            ],
            [
                'id'        => 4,
                'name'      => 'Yassine Hamdi',
                'email'     => 'citoyen@aquasecure.tn',
                'role'      => 'citizen',
                'status'    => 'active',
                'initials'  => 'YH',
                'phone'     => '+216 22 456 789',
                'zone'      => 'Tunis Centre',
                'joined'    => '01 juin 2025',
                'last_seen' => 'Il y a 2 h',
                'reports'   => 3,
            ],
            [
                'id'        => 5,
                'name'      => 'Sami Ben Salah',
                'email'     => 'sami.b@aquasecure.tn',
                'role'      => 'citizen',
                'status'    => 'blocked',
                'initials'  => 'SB',
                'phone'     => '+216 55 123 789',
                'zone'      => 'Ariana',
                'joined'    => '14 avr. 2025',
                'last_seen' => 'Il y a 3 j',
                'reports'   => 7,
            ],
            [
                'id'        => 6,
                'name'      => 'Leila Technicienne',
                'email'     => 'leila.tech@aquasecure.tn',
                'role'      => 'technician',
                'status'    => 'active',
                'initials'  => 'LT',
                'phone'     => '+216 97 123 654',
                'zone'      => 'Ben Arous',
                'joined'    => '12 avr. 2025',
                'last_seen' => 'Il y a 1 h',
                'reports'   => 0,
            ],
            [
                'id'        => 7,
                'name'      => 'Nadia Mzoughi',
                'email'     => 'nadia.m@aquasecure.tn',
                'role'      => 'citizen',
                'status'    => 'active',
                'initials'  => 'NM',
                'phone'     => '+216 23 456 123',
                'zone'      => 'La Marsa',
                'joined'    => '22 juil. 2025',
                'last_seen' => 'Il y a 4 h',
                'reports'   => 1,
            ],
            [
                'id'        => 8,
                'name'      => 'Karim Manager',
                'email'     => 'karim@aquasecure.tn',
                'role'      => 'manager',
                'status'    => 'blocked',
                'initials'  => 'KM',
                'phone'     => '+216 71 987 654',
                'zone'      => 'Sfax',
                'joined'    => '08 mai 2025',
                'last_seen' => 'Il y a 11 j',
                'reports'   => 0,
            ],
            [
                'id'        => 9,
                'name'      => 'Omar Trabelsi',
                'email'     => 'omar.t@aquasecure.tn',
                'role'      => 'citizen',
                'status'    => 'active',
                'initials'  => 'OT',
                'phone'     => '+216 24 789 456',
                'zone'      => 'Sousse',
                'joined'    => '03 sept. 2025',
                'last_seen' => 'Il y a 6 h',
                'reports'   => 2,
            ],
            [
                'id'        => 10,
                'name'      => 'Fatma Kammoun',
                'email'     => 'fatma.k@aquasecure.tn',
                'role'      => 'citizen',
                'status'    => 'active',
                'initials'  => 'FK',
                'phone'     => '+216 27 321 654',
                'zone'      => 'Ben Arous',
                'joined'    => '18 août 2025',
                'last_seen' => 'Hier',
                'reports'   => 1,
            ],
        ];
    }

    /**
     * Toutes les réclamations/signalements (vue Admin complète)
     */
    public static function adminAllReclamations(): array
    {
        return [
            [
                'id'           => '#REC-125',
                'type'         => 'Fuite canalisation',
                'description'  => 'Fuite importante détectée sur la conduite principale, rue Habib Bourguiba',
                'zone'         => 'Ariana Centre',
                'address'      => '42 Rue Habib Bourguiba, Ariana',
                'citizen'      => 'Yassine Hamdi',
                'citizen_init' => 'YH',
                'priority'     => 'critical',
                'status'       => 'in_progress',
                'technician'   => 'Amira Ben Ali',
                'manager'      => 'Ines Mansouri',
                'created_at'   => '26/09 09:42',
                'updated_at'   => '26/09 10:15',
                'lat'          => 36.8665,
                'lng'          => 10.1647,
            ],
            [
                'id'           => '#REC-124',
                'type'         => 'Coupure d\'eau',
                'description'  => 'Absence totale d\'eau depuis 6h du matin',
                'zone'         => 'Tunis Nord',
                'address'      => '18 Av. Mohamed V, Tunis',
                'citizen'      => 'Sami Trabelsi',
                'citizen_init' => 'ST',
                'priority'     => 'medium',
                'status'       => 'resolved',
                'technician'   => 'Leila Technicienne',
                'manager'      => 'Ines Mansouri',
                'created_at'   => '26/09 07:20',
                'updated_at'   => '26/09 12:00',
                'lat'          => 36.8568,
                'lng'          => 10.1853,
            ],
            [
                'id'           => '#REC-123',
                'type'         => 'Contamination eau',
                'description'  => 'Eau trouble avec odeur inhabituelle depuis 2 jours',
                'zone'         => 'La Marsa',
                'address'      => '7 Rue de Carthage, La Marsa',
                'citizen'      => 'Nadia Mzoughi',
                'citizen_init' => 'NM',
                'priority'     => 'critical',
                'status'       => 'in_progress',
                'technician'   => 'Amira Ben Ali',
                'manager'      => 'Ines Mansouri',
                'created_at'   => '25/09 14:30',
                'updated_at'   => '26/09 09:00',
                'lat'          => 36.8881,
                'lng'          => 10.3235,
            ],
            [
                'id'           => '#REC-122',
                'type'         => 'Pression insuffisante',
                'description'  => 'Faible pression aux étages supérieurs',
                'zone'         => 'Ben Arous',
                'address'      => '25 Cité des Orangers, Ben Arous',
                'citizen'      => 'Fatma Kammoun',
                'citizen_init' => 'FK',
                'priority'     => 'low',
                'status'       => 'resolved',
                'technician'   => 'Leila Technicienne',
                'manager'      => 'Ines Mansouri',
                'created_at'   => '25/09 08:00',
                'updated_at'   => '25/09 15:30',
                'lat'          => 36.7533,
                'lng'          => 10.2286,
            ],
            [
                'id'           => '#REC-121',
                'type'         => 'Fuite mineur',
                'description'  => 'Petite fuite au niveau du compteur',
                'zone'         => 'Sousse',
                'address'      => '4 Rue Ibn Khaldoun, Sousse',
                'citizen'      => 'Omar Trabelsi',
                'citizen_init' => 'OT',
                'priority'     => 'low',
                'status'       => 'pending',
                'technician'   => null,
                'manager'      => null,
                'created_at'   => '24/09 16:00',
                'updated_at'   => '24/09 16:00',
                'lat'          => 35.8245,
                'lng'          => 10.6346,
            ],
            [
                'id'           => '#REC-120',
                'type'         => 'Compteur défaillant',
                'description'  => 'Compteur d\'eau bloqué, lecture impossible',
                'zone'         => 'Sfax Centre',
                'address'      => '12 Rue de la République, Sfax',
                'citizen'      => 'Hatem Gargouri',
                'citizen_init' => 'HG',
                'priority'     => 'medium',
                'status'       => 'resolved',
                'technician'   => 'Ahmed Trabelsi',
                'manager'      => 'Karim Manager',
                'created_at'   => '24/09 11:00',
                'updated_at'   => '25/09 09:00',
                'lat'          => 34.7406,
                'lng'          => 10.7603,
            ],
            [
                'id'           => '#REC-119',
                'type'         => 'Fuite canalisation',
                'description'  => 'Fuite sur conduite principale, eau sur chaussée',
                'zone'         => 'Tunis Centre',
                'address'      => '88 Av. de Paris, Tunis',
                'citizen'      => 'Rania Bouzid',
                'citizen_init' => 'RB',
                'priority'     => 'medium',
                'status'       => 'resolved',
                'technician'   => 'Amira Ben Ali',
                'manager'      => 'Ines Mansouri',
                'created_at'   => '24/09 09:20',
                'updated_at'   => '24/09 16:20',
                'lat'          => 36.8190,
                'lng'          => 10.1657,
            ],
        ];
    }

    /**
     * KPIs globaux de la plateforme (pour Admin Dashboard — vision globale)
     */
    public static function adminGlobalKPIs(): array
    {
        return [
            ['icon' => 'users',           'color' => 'teal',   'value' => '1 190', 'label' => 'Citoyens inscrits',      'trend' => '+14 ce mois',  'trend_up' => true],
            ['icon' => 'hard-hat',        'color' => 'cyan',   'value' => '47',    'label' => 'Techniciens',            'trend' => '+2 ce mois',   'trend_up' => true],
            ['icon' => 'briefcase',       'color' => 'blue',   'value' => '8',     'label' => 'Gestionnaires',          'trend' => 'stable',       'trend_up' => true],
            ['icon' => 'alert-triangle',  'color' => 'red',    'value' => '124',   'label' => 'Incidents (total)',      'trend' => 'cette année',  'trend_up' => false],
            ['icon' => 'loader',          'color' => 'amber',  'value' => '12',    'label' => 'Interventions en cours', 'trend' => '-3 vs hier',   'trend_up' => true],
            ['icon' => 'check-circle-2',  'color' => 'emerald','value' => '107',   'label' => 'Incidents résolus',      'trend' => '86% taux',     'trend_up' => true],
        ];
    }

    /**
     * Incidents récents pour Admin Dashboard
     */
    public static function adminRecentIncidents(): array
    {
        return [
            ['id' => '#125', 'type' => 'Fuite canalisation',   'zone' => 'Ariana Centre',   'priority' => 'critical', 'status' => 'in_progress', 'reported_by' => 'Yassine Hamdi',    'time' => 'Il y a 10 min'],
            ['id' => '#124', 'type' => 'Coupure d\'eau',       'zone' => 'Tunis Nord',      'priority' => 'medium',   'status' => 'resolved',    'reported_by' => 'Sami Trabelsi',    'time' => 'Il y a 1 h'],
            ['id' => '#123', 'type' => 'Contamination',        'zone' => 'La Marsa',        'priority' => 'critical', 'status' => 'in_progress', 'reported_by' => 'Nadia Mzoughi',    'time' => 'Il y a 2 h'],
            ['id' => '#122', 'type' => 'Pression insuffisante','zone' => 'Mnihla',          'priority' => 'low',      'status' => 'resolved',    'reported_by' => 'Omar Ben Salah',   'time' => 'Il y a 4 h'],
            ['id' => '#121', 'type' => 'Fuite mineur',         'zone' => 'Ben Arous',       'priority' => 'medium',   'status' => 'resolved',    'reported_by' => 'Fatma Kammoun',    'time' => 'Il y a 5 h'],
            ['id' => '#120', 'type' => 'Compteur défaillant',  'zone' => 'Sfax Centre',     'priority' => 'low',      'status' => 'resolved',    'reported_by' => 'Hatem Gargouri',   'time' => 'Il y a 7 h'],
            ['id' => '#119', 'type' => 'Fuite canalisation',   'zone' => 'Sousse Nord',     'priority' => 'medium',   'status' => 'resolved',    'reported_by' => 'Rania Bouzid',     'time' => 'Il y a 1 j'],
        ];
    }

    /**
     * Techniciens avec statut temps réel (pour Admin Dashboard)
     */
    public static function adminTechnicians(): array
    {
        return [
            ['name' => 'Amira Ben Ali',    'zone' => 'Tunis Nord / Ariana', 'status' => 'on_mission', 'interventions' => 2, 'initials' => 'AB'],
            ['name' => 'Mohamed Ali',      'zone' => 'Tunis Centre',         'status' => 'available',  'interventions' => 0, 'initials' => 'MA'],
            ['name' => 'Leila Technicienne','zone' => 'Ben Arous',           'status' => 'on_mission', 'interventions' => 1, 'initials' => 'LT'],
            ['name' => 'Ahmed Trabelsi',   'zone' => 'Sfax Nord',            'status' => 'on_mission', 'interventions' => 1, 'initials' => 'AT'],
            ['name' => 'Salma Khelifi',    'zone' => 'Sousse',               'status' => 'available',  'interventions' => 0, 'initials' => 'SK'],
            ['name' => 'Karim Mejri',      'zone' => 'Nabeul / Bizerte',     'status' => 'off_duty',   'interventions' => 0, 'initials' => 'KM'],
        ];
    }

    /**
     * Gestionnaires pour Admin Dashboard
     */
    public static function adminManagers(): array
    {
        return [
            ['name' => 'Ines Mansouri',  'zone' => 'Grand Tunis',   'status' => 'active', 'incidents' => 5,  'initials' => 'IM'],
            ['name' => 'Sami Ben Ali',   'zone' => 'Ariana',        'status' => 'active', 'incidents' => 3,  'initials' => 'SB'],
            ['name' => 'Karim Manager',  'zone' => 'Sfax',          'status' => 'inactive','incidents' => 0, 'initials' => 'KM'],
            ['name' => 'Sara Mechri',    'zone' => 'Sousse/Monastir','status' => 'active', 'incidents' => 2,  'initials' => 'SM'],
        ];
    }

    /**
     * Activité fonctionnelle AquaSecure (historique résolutions/signalements)
     */
    public static function adminFunctionalActivity(): array
    {
        return [
            ['icon' => 'file-plus',   'color' => 'amber', 'actor' => 'Yassine Hamdi',    'role' => 'citizen',    'event' => 'a créé un signalement #125',           'time' => '09:42'],
            ['icon' => 'user-check',  'color' => 'blue',  'actor' => 'Ines Mansouri',    'role' => 'manager',    'event' => 'a affecté l\'intervention #52 à Amira', 'time' => '09:35'],
            ['icon' => 'wrench',      'color' => 'cyan',  'actor' => 'Amira Ben Ali',    'role' => 'technician', 'event' => 'a commencé l\'intervention #52',         'time' => '09:20'],
            ['icon' => 'check-circle','color' => 'teal',  'actor' => 'Amira Ben Ali',    'role' => 'technician', 'event' => 'a terminé l\'intervention #49',          'time' => '08:55'],
            ['icon' => 'user-plus',   'color' => 'teal',  'actor' => 'Sami Trabelsi',    'role' => 'citizen',    'event' => 'nouveau citoyen inscrit',                'time' => '08:40'],
            ['icon' => 'map-pin',     'color' => 'blue',  'actor' => 'Ines Mansouri',    'role' => 'manager',    'event' => 'a clôturé l\'incident #124',             'time' => '08:20'],
            ['icon' => 'file-plus',   'color' => 'amber', 'actor' => 'Nadia Mzoughi',    'role' => 'citizen',    'event' => 'a créé un signalement #123',             'time' => '07:55'],
            ['icon' => 'check-circle','color' => 'teal',  'actor' => 'Ahmed Trabelsi',   'role' => 'technician', 'event' => 'a résolu la fuite zone Sousse Nord',     'time' => '07:30'],
        ];
    }

    /**
     * Historique des résolutions (pour Admin Dashboard)
     */
    public static function adminResolutionHistory(): array
    {
        return [
            [
                'id'          => '#119',
                'type'        => 'Fuite canalisation',
                'zone'        => 'Tunis Centre',
                'technician'  => 'Amira Ben Ali',
                'manager'     => 'Ines Mansouri',
                'reported_at' => '24/09 09:20',
                'assigned_at' => '24/09 11:30',
                'started_at'  => '24/09 14:00',
                'resolved_at' => '24/09 16:20',
                'duration'    => '2h 20min',
                'status'      => 'resolved',
            ],
            [
                'id'          => '#115',
                'type'        => 'Qualité dégradée',
                'zone'        => 'Ariana',
                'technician'  => 'Leila Technicienne',
                'manager'     => 'Sami Ben Ali',
                'reported_at' => '22/09 14:10',
                'assigned_at' => '22/09 15:00',
                'started_at'  => '22/09 16:30',
                'resolved_at' => '23/09 09:45',
                'duration'    => '1j 2h',
                'status'      => 'resolved',
            ],
            [
                'id'          => '#112',
                'type'        => 'Coupure eau',
                'zone'        => 'Ben Arous',
                'technician'  => 'Ahmed Trabelsi',
                'manager'     => 'Ines Mansouri',
                'reported_at' => '21/09 08:00',
                'assigned_at' => '21/09 09:15',
                'started_at'  => '21/09 10:00',
                'resolved_at' => '21/09 13:30',
                'duration'    => '3h 30min',
                'status'      => 'resolved',
            ],
        ];
    }

    /**
     * Vue d'ensemble des zones pour Admin (incidents par zone)
     */
    public static function adminZoneOverview(): array
    {
        return [
            ['zone' => 'Tunis Nord',   'emoji' => '🏛️', 'total' => 3,  'in_progress' => 1, 'resolved' => 2, 'status' => 'alert',    'lat' => 36.8568, 'lng' => 10.1853],
            ['zone' => 'Ariana',       'emoji' => '🏘️', 'total' => 7,  'in_progress' => 2, 'resolved' => 5, 'status' => 'critical', 'lat' => 36.8665, 'lng' => 10.1647],
            ['zone' => 'La Marsa',     'emoji' => '🏖️', 'total' => 5,  'in_progress' => 2, 'resolved' => 3, 'status' => 'alert',    'lat' => 36.8881, 'lng' => 10.3235],
            ['zone' => 'Ben Arous',    'emoji' => '🏭', 'total' => 1,  'in_progress' => 0, 'resolved' => 1, 'status' => 'normal',   'lat' => 36.7533, 'lng' => 10.2286],
            ['zone' => 'Tunis Centre', 'emoji' => '🌆', 'total' => 3,  'in_progress' => 0, 'resolved' => 3, 'status' => 'normal',   'lat' => 36.8190, 'lng' => 10.1657],
            ['zone' => 'Sousse',       'emoji' => '🌊', 'total' => 1,  'in_progress' => 0, 'resolved' => 1, 'status' => 'normal',   'lat' => 35.8245, 'lng' => 10.6346],
            ['zone' => 'Sfax Centre',  'emoji' => '🏢', 'total' => 1,  'in_progress' => 1, 'resolved' => 0, 'status' => 'alert',    'lat' => 34.7406, 'lng' => 10.7603],
        ];
    }

    /**
     * Statistiques de performance globales
     */
    public static function adminPerformanceStats(): array
    {
        return [
            'total_resolved'      => 107,
            'avg_resolution_time' => '4h 32m',
            'resolution_rate'     => 86,
            'incidents_this_month'=> 24,
            'resolved_this_month' => 21,
            'avg_response_time'   => '38 min',
        ];
    }

    /**
     * Historique incidents 7 derniers jours (pour sparkline Admin)
     */
    public static function adminIncidents7Days(): array
    {
        return [
            'labels'   => ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'],
            'created'  => [8,  12, 6,  15, 9,  4,  3],
            'resolved' => [6,  10, 8,  12, 11, 3,  2],
        ];
    }

    /**
     * Activité plateforme récente (pour Admin Dashboard)
     */
    public static function adminPlatformActivity(): array
    {
        return [
            [
                'icon'  => 'log-in',
                'color' => 'cyan',
                'event' => 'Connexion administrateur',
                'detail'=> 'admin@aquasecure.tn s\'est connecté',
                'user'  => 'Amina Kacem',
                'time'  => 'Il y a 5 min',
                'type'  => 'info',
            ],
            [
                'icon'  => 'user-plus',
                'color' => 'teal',
                'event' => 'Nouveau compte citoyen',
                'detail'=> 'Compte créé pour Sami Trabelsi',
                'user'  => 'Amina Kacem',
                'time'  => 'Il y a 18 min',
                'type'  => 'success',
            ],
            [
                'icon'  => 'user-cog',
                'color' => 'blue',
                'event' => 'Compte technicien modifié',
                'detail'=> 'Amira Ben Ali — rôle mis à jour',
                'user'  => 'Amina Kacem',
                'time'  => 'Il y a 32 min',
                'type'  => 'info',
            ],
            [
                'icon'  => 'database-backup',
                'color' => 'teal',
                'event' => 'Sauvegarde complète terminée',
                'detail'=> 'backup-2026-09-26-daily.sql — 2.4 GB',
                'user'  => 'Système',
                'time'  => 'Il y a 1 h',
                'type'  => 'success',
            ],
            [
                'icon'  => 'settings',
                'color' => 'blue',
                'event' => 'Configuration mise à jour',
                'detail'=> 'Paramètres SMTP modifiés',
                'user'  => 'Amina Kacem',
                'time'  => 'Il y a 2 h',
                'type'  => 'info',
            ],
            [
                'icon'  => 'shield-check',
                'color' => 'teal',
                'event' => 'Vérification sécurité complète',
                'detail'=> 'Aucune menace détectée',
                'user'  => 'Système',
                'time'  => 'Il y a 3 h',
                'type'  => 'success',
            ],
            [
                'icon'  => 'key-round',
                'color' => 'amber',
                'event' => 'Réinitialisation mot de passe',
                'detail'=> 'citoyen@aquasecure.tn a demandé une réinit.',
                'user'  => 'Yassine Hamdi',
                'time'  => 'Il y a 4 h',
                'type'  => 'warning',
            ],
            [
                'icon'  => 'toggle-right',
                'color' => 'blue',
                'event' => 'Permission modifiée',
                'detail'=> 'Rôle Gestionnaire — accès Analytics ajouté',
                'user'  => 'Amina Kacem',
                'time'  => 'Il y a 5 h',
                'type'  => 'info',
            ],
        ];
    }

    /**
     * Événements de sécurité récents (pour Admin Dashboard)
     */
    public static function adminSecurityEvents(): array
    {
        return [
            [
                'type'   => 'success',
                'icon'   => 'log-in',
                'event'  => 'Connexion réussie',
                'target' => 'admin@aquasecure.tn',
                'ip'     => '197.15.232.10',
                'time'   => 'Il y a 5 min',
            ],
            [
                'type'   => 'info',
                'icon'   => 'key-round',
                'event'  => 'Réinitialisation mot de passe',
                'target' => 'citoyen@aquasecure.tn',
                'ip'     => '197.15.232.45',
                'time'   => 'Il y a 1 h',
            ],
            [
                'type'   => 'info',
                'icon'   => 'shield',
                'event'  => 'Rôle mis à jour',
                'target' => 'gestionnaire@aquasecure.tn',
                'ip'     => '197.15.232.10',
                'time'   => 'Il y a 2 h',
            ],
            [
                'type'   => 'warning',
                'icon'   => 'shield-x',
                'event'  => 'Tentative de connexion bloquée',
                'target' => 'unknown@example.com',
                'ip'     => '103.45.78.92',
                'time'   => 'Il y a 3 h',
            ],
            [
                'type'   => 'success',
                'icon'   => 'log-in',
                'event'  => 'Connexion réussie',
                'target' => 'amira@aquasecure.tn',
                'ip'     => '41.226.18.44',
                'time'   => 'Il y a 4 h',
            ],
            [
                'type'   => 'warning',
                'icon'   => 'alert-triangle',
                'event'  => '5 tentatives échouées',
                'target' => 'unknown IP',
                'ip'     => '45.67.89.12',
                'time'   => 'Il y a 6 h',
            ],
        ];
    }

    /**
     * Ressources système avec valeurs fake (pour Admin Dashboard)
     */
    public static function adminResourceUsage(): array
    {
        return [
            ['label' => 'Stockage',    'used' => 2.4,  'total' => 10,   'unit' => 'GB', 'pct' => 24, 'color' => '#2dd4bf', 'icon' => 'hard-drive'],
            ['label' => 'Base données','used' => 3.8,  'total' => 10,   'unit' => 'GB', 'pct' => 38, 'color' => '#38bdf8', 'icon' => 'database'],
            ['label' => 'Logs',        'used' => 1.7,  'total' => 10,   'unit' => 'GB', 'pct' => 17, 'color' => '#818cf8', 'icon' => 'scroll-text'],
            ['label' => 'Cache',       'used' => 512,  'total' => 2048, 'unit' => 'MB', 'pct' => 25, 'color' => '#34d399', 'icon' => 'zap'],
        ];
    }

    /**
     * Alertes système (pour Admin Dashboard)
     */
    public static function adminSystemAlerts(): array
    {
        return [
            [
                'level'   => 'warning',
                'icon'    => 'hard-drive',
                'title'   => 'Stockage approche du seuil',
                'message' => 'L\'espace disque atteindra 80% dans ~5 jours au rythme actuel.',
                'time'    => 'Il y a 3 h',
                'action'  => 'Gérer',
                'route'   => 'admin.backups',
            ],
            [
                'level'   => 'info',
                'icon'    => 'wrench',
                'title'   => 'Maintenance planifiée disponible',
                'message' => 'Laravel 12.x — mise à jour mineure disponible.',
                'time'    => 'Il y a 1 j',
                'action'  => 'Voir',
                'route'   => 'admin.system',
            ],
            [
                'level'   => 'success',
                'icon'    => 'database-backup',
                'title'   => 'Sauvegarde base de données réussie',
                'message' => 'Sauvegarde quotidienne complétée à 02:00 — 2.4 GB.',
                'time'    => 'Il y a 8 h',
                'action'  => 'Détails',
                'route'   => 'admin.backups',
            ],
        ];
    }

    /**
     * Activité utilisateurs sur 7 jours (sparkline pour Admin Dashboard)
     */
    public static function adminUserActivity7Days(): array
    {
        return [
            'labels' => ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'],
            'logins' => [142, 168, 155, 190, 174, 89, 67],
            'new'    => [8, 12, 7, 15, 9, 3, 2],
        ];
    }

    /**
     * Notifications globales (pour le notification center)
     */
    public static function globalNotifications(): array
    {
        return [
            [
                'id' => 'notif-global-1',
                'type' => 'system',
                'title' => 'Mise à jour système disponible',
                'message' => 'Une nouvelle version d\'AquaSecure est disponible avec des améliorations de sécurité',
                'icon' => 'download',
                'color' => 'blue',
                'time' => 'Il y a 30min',
                'read' => false,
                'action' => ['label' => 'Voir les détails', 'url' => '#'],
            ],
            [
                'id' => 'notif-global-2',
                'type' => 'alert',
                'title' => 'Alerte qualité - Zone Ariana',
                'message' => 'Taux de chlore légèrement élevé détecté. Analyse en cours',
                'icon' => 'alert-triangle',
                'color' => 'amber',
                'time' => 'Il y a 1h',
                'read' => false,
                'action' => ['label' => 'Consulter', 'url' => '#'],
            ],
            [
                'id' => 'notif-global-3',
                'type' => 'intervention',
                'title' => 'Intervention programmée',
                'message' => 'Maintenance préventive prévue demain à 9h - Zone Tunis Nord',
                'icon' => 'wrench',
                'color' => 'cyan',
                'time' => 'Il y a 2h',
                'read' => false,
            ],
            [
                'id' => 'notif-global-4',
                'type' => 'success',
                'title' => 'Problème résolu',
                'message' => 'La fuite d\'eau rue Habib Bourguiba a été réparée avec succès',
                'icon' => 'check-circle',
                'color' => 'emerald',
                'time' => 'Il y a 3h',
                'read' => true,
            ],
            [
                'id' => 'notif-global-5',
                'type' => 'report',
                'title' => 'Nouvelle déclaration',
                'message' => 'Un citoyen a signalé un problème de pression dans votre zone',
                'icon' => 'file-text',
                'color' => 'purple',
                'time' => 'Il y a 5h',
                'read' => true,
            ],
            [
                'id' => 'notif-global-6',
                'type' => 'info',
                'title' => 'Rapport mensuel disponible',
                'message' => 'Le rapport de performance de septembre 2026 est maintenant disponible',
                'icon' => 'bar-chart',
                'color' => 'blue',
                'time' => 'Il y a 1j',
                'read' => true,
            ],
        ];
    }

    /**
     * Municipalités & Régies d'eau (120Water Utility Selector)
     */
    public static function municipalities(): array
    {
        return [
            ['id' => 'muni-1', 'name' => 'Tunis Grand Water Utility', 'code' => 'TGWU-01', 'region' => 'Grand Tunis', 'assets' => 542, 'population' => 450000, 'active' => true],
            ['id' => 'muni-2', 'name' => 'Sousse Municipal Water Authority', 'code' => 'SMWA-02', 'region' => 'Sahel', 'assets' => 318, 'population' => 270000, 'active' => false],
            ['id' => 'muni-3', 'name' => 'Sfax Water District', 'code' => 'SWD-03', 'region' => 'Sfax', 'assets' => 420, 'population' => 380000, 'active' => false],
            ['id' => 'muni-4', 'name' => 'Bizerte Eco-Water Authority', 'code' => 'BEWA-04', 'region' => 'Bizerte', 'assets' => 202, 'population' => 160000, 'active' => false],
        ];
    }

    /**
     * Infrastructures du réseau (conduites, réservoirs, pompes et stations de traitement)
     */
    public static function infrastructureAssets(): array
    {
        return [
            [
                'id' => 'AST-PIP-104',
                'name' => 'Conduite Principale Bourguiba',
                'type' => 'Pipe Line',
                'category' => 'Distribution Pipe',
                'zone' => 'Tunis Nord',
                'lat' => 36.8568,
                'lng' => 10.1853,
                'status' => 'operational',
                'condition_score' => 92,
                'risk_level' => 'Low',
                'material' => 'Ductile Iron',
                'diameter' => '400mm',
                'installation_year' => 2018,
                'last_inspection' => '2026-08-12',
                'inspection_results' => [
                    'pressure_psi' => 58.4,
                    'flow_rate_lps' => 124.5,
                    'pH' => 7.42,
                    'turbidity_ntu' => 0.45,
                    'demo_values' => true
                ],
                'maintenance_count' => 2
            ],
            [
                'id' => 'AST-RES-002',
                'name' => 'Réservoir Central Belvédère',
                'type' => 'Reservoir',
                'category' => 'Elevated Water Tank',
                'zone' => 'Tunis Centre',
                'lat' => 36.8190,
                'lng' => 10.1657,
                'status' => 'operational',
                'condition_score' => 88,
                'risk_level' => 'Low',
                'capacity_m3' => 15000,
                'current_level_pct' => 84,
                'installation_year' => 2012,
                'last_inspection' => '2026-07-30',
                'inspection_results' => [
                    'pressure_psi' => 62.1,
                    'flow_rate_lps' => 210.0,
                    'pH' => 7.35,
                    'turbidity_ntu' => 0.38,
                    'demo_values' => true
                ],
                'maintenance_count' => 1
            ],
            [
                'id' => 'AST-PMP-089',
                'name' => 'Station de Pompage Ariana-Nord',
                'type' => 'Pumping Station',
                'category' => 'Booster Pump Station',
                'zone' => 'Ariana',
                'lat' => 36.8665,
                'lng' => 10.1647,
                'status' => 'maintenance',
                'condition_score' => 64,
                'risk_level' => 'Medium',
                'capacity_m3' => 8500,
                'current_level_pct' => 62,
                'installation_year' => 2008,
                'last_inspection' => '2026-09-10',
                'inspection_results' => [
                    'pressure_psi' => 41.2,
                    'flow_rate_lps' => 85.0,
                    'pH' => 7.68,
                    'turbidity_ntu' => 0.92,
                    'demo_values' => true
                ],
                'maintenance_count' => 5
            ],
            [
                'id' => 'AST-TRT-015',
                'name' => 'Usine de Traitement Ben Arous',
                'type' => 'Treatment Facility',
                'category' => 'Filtration & Decontamination Plant',
                'zone' => 'Ben Arous',
                'lat' => 36.7533,
                'lng' => 10.2286,
                'status' => 'critical',
                'condition_score' => 45,
                'risk_level' => 'Critical',
                'capacity_m3' => 45000,
                'current_level_pct' => 48,
                'installation_year' => 2001,
                'last_inspection' => '2026-09-22',
                'inspection_results' => [
                    'pressure_psi' => 28.5,
                    'flow_rate_lps' => 62.0,
                    'pH' => 8.45,
                    'turbidity_ntu' => 2.15,
                    'demo_values' => true
                ],
                'maintenance_count' => 9
            ],
            [
                'id' => 'AST-PIP-308',
                'name' => 'Conduite Secondaire Sousse Coast',
                'type' => 'Pipe Line',
                'category' => 'Secondary Feeder',
                'zone' => 'Sousse',
                'lat' => 35.8245,
                'lng' => 10.6346,
                'status' => 'operational',
                'condition_score' => 95,
                'risk_level' => 'Low',
                'material' => 'HDPE High Density Polyethylene',
                'diameter' => '250mm',
                'installation_year' => 2021,
                'last_inspection' => '2026-06-15',
                'inspection_results' => [
                    'pressure_psi' => 54.0,
                    'flow_rate_lps' => 98.2,
                    'pH' => 7.25,
                    'turbidity_ntu' => 0.30,
                    'demo_values' => true
                ],
                'maintenance_count' => 0
            ],
            [
                'id' => 'AST-MTR-702',
                'name' => 'Point de contrôle Sfax Port',
                'type' => 'Water Quality Checkpoint',
                'category' => 'Inspection Point',
                'zone' => 'Sfax Centre',
                'lat' => 34.7406,
                'lng' => 10.7603,
                'status' => 'operational',
                'condition_score' => 90,
                'risk_level' => 'Low',
                'installation_year' => 2023,
                'last_inspection' => '2026-09-01',
                'inspection_results' => [
                    'pressure_psi' => 52.8,
                    'flow_rate_lps' => 142.0,
                    'pH' => 7.30,
                    'turbidity_ntu' => 0.41,
                    'demo_values' => true
                ],
                'maintenance_count' => 1
            ],
        ];
    }

    /**
     * Records de Qualité de l'Eau (pH, Turbidité, Chlore résiduel, Plomb, Nitrates)
     */
    public static function waterQualityRecords(): array
    {
        return [
            [
                'id' => 'WQR-2026-108',
                'sampling_point' => 'Station Belvédère - Point #04',
                'zone' => 'Tunis Centre',
                'lat' => 36.8190,
                'lng' => 10.1657,
                'date' => '2026-09-26 08:30',
                'pH' => 7.35,
                'pH_status' => 'Optimal (6.5-8.5)',
                'turbidity' => 0.38,
                'turbidity_unit' => 'NTU',
                'turbidity_status' => 'Optimal (<1.0)',
                'residual_chlorine' => 0.85,
                'chlorine_unit' => 'mg/L',
                'chlorine_status' => 'Optimal (0.2-2.0)',
                'lead_pb' => 2.1,
                'lead_unit' => 'ppb',
                'lead_status' => 'Safe (<15 ppb)',
                'nitrates' => 12.4,
                'nitrates_unit' => 'mg/L',
                'is_verified' => true,
                'verifier' => 'Laboratoire Central INNORPI',
                'overall_compliance' => 98.5,
                'status' => 'Compliant'
            ],
            [
                'id' => 'WQR-2026-107',
                'sampling_point' => 'Puits d\'Alimentation Ariana-Nord',
                'zone' => 'Ariana',
                'lat' => 36.8665,
                'lng' => 10.1647,
                'date' => '2026-09-25 14:15',
                'pH' => 7.82,
                'pH_status' => 'Optimal (6.5-8.5)',
                'turbidity' => 1.25,
                'turbidity_unit' => 'NTU',
                'turbidity_status' => 'Warning (>1.0 NTU)',
                'residual_chlorine' => 0.35,
                'chlorine_unit' => 'mg/L',
                'chlorine_status' => 'Low Warning',
                'lead_pb' => 6.8,
                'lead_unit' => 'ppb',
                'lead_status' => 'Safe (<15 ppb)',
                'nitrates' => 28.1,
                'nitrates_unit' => 'mg/L',
                'is_verified' => true,
                'verifier' => 'Unité Mobile Qualité #2',
                'overall_compliance' => 88.0,
                'status' => 'Alert'
            ],
            [
                'id' => 'WQR-2026-106',
                'sampling_point' => 'Réservoir Industriel Ben Arous',
                'zone' => 'Ben Arous',
                'lat' => 36.7533,
                'lng' => 10.2286,
                'date' => '2026-09-24 11:00',
                'pH' => 8.65,
                'pH_status' => 'Exceeded (>8.5)',
                'turbidity' => 2.45,
                'turbidity_unit' => 'NTU',
                'turbidity_status' => 'Critical (>2.0 NTU)',
                'residual_chlorine' => 0.12,
                'chlorine_unit' => 'mg/L',
                'chlorine_status' => 'Critical Low (<0.2)',
                'lead_pb' => 18.2,
                'lead_unit' => 'ppb',
                'lead_status' => 'Warning Exceeded (>15 ppb)',
                'nitrates' => 42.5,
                'nitrates_unit' => 'mg/L',
                'is_verified' => false,
                'verifier' => 'En attente de vérification',
                'overall_compliance' => 64.2,
                'status' => 'Non-Compliant'
            ],
            [
                'id' => 'WQR-2026-105',
                'sampling_point' => 'Réseau Sousse Port-Nord',
                'zone' => 'Sousse',
                'lat' => 35.8245,
                'lng' => 10.6346,
                'date' => '2026-09-24 09:40',
                'pH' => 7.20,
                'pH_status' => 'Optimal (6.5-8.5)',
                'turbidity' => 0.22,
                'turbidity_unit' => 'NTU',
                'turbidity_status' => 'Optimal (<1.0)',
                'residual_chlorine' => 1.10,
                'chlorine_unit' => 'mg/L',
                'chlorine_status' => 'Optimal (0.2-2.0)',
                'lead_pb' => 1.4,
                'lead_unit' => 'ppb',
                'lead_status' => 'Safe (<15 ppb)',
                'nitrates' => 9.2,
                'nitrates_unit' => 'mg/L',
                'is_verified' => true,
                'verifier' => 'Laboratoire Régional Sousse',
                'overall_compliance' => 99.1,
                'status' => 'Compliant'
            ],
        ];
    }

    /**
     * Projets de Rénovation et Décontamination (Maintenance & Project Management)
     */
    public static function projects(): array
    {
        return [
            [
                'id' => 'PRJ-2026-01',
                'title' => 'Modernisation Conduite Principale Grand Tunis',
                'code' => 'TGWU-RENOV-01',
                'category' => 'Infrastructure Renovation',
                'municipality' => 'Tunis Grand Water Utility',
                'zone' => 'Tunis Nord',
                'description' => 'Remplacement de 14.5 km de conduites en fonte ductile vétustes par du polyéthylène haute densité et installation de vannes motorisées télécommandées.',
                'project_manager' => 'Ing. Ines Mansouri',
                'contractor' => 'SOTUVERA Construction',
                'start_date' => '2026-03-01',
                'end_date' => '2026-11-30',
                'planned_budget' => 2400000,
                'actual_spent' => 1680000,
                'currency' => 'USD',
                'funding_sources' => ['EU Water Infrastructure Fund (60%)', 'Municipal Bond (40%)'],
                'completion_pct' => 70,
                'status' => 'In Progress',
                'health' => 'On Track',
                'milestones' => [
                    ['name' => 'Étude géotechnique & cartographie GIS', 'date' => '2026-03-25', 'done' => true],
                    ['name' => 'Pose 8km tronçon Tunis-Marsa', 'date' => '2026-06-30', 'done' => true],
                    ['name' => 'Inspection et cartographie du réseau', 'date' => '2026-09-15', 'done' => true],
                    ['name' => 'Raccordement final & tests de pression', 'date' => '2026-11-15', 'done' => false],
                ],
                'documents' => [
                    ['name' => 'Rapport_Avancement_T3_2026.pdf', 'size' => '4.2 MB', 'date' => '2026-09-15'],
                    ['name' => 'Plan_Cadastral_Conduites_v2.dwg', 'size' => '18.4 MB', 'date' => '2026-04-10'],
                ]
            ],
            [
                'id' => 'PRJ-2026-02',
                'title' => 'Décontamination & Filtration Plomb Ben Arous',
                'code' => 'BEWA-DECONTAM-02',
                'category' => 'Water Quality & Decontamination',
                'municipality' => 'Tunis Grand Water Utility',
                'zone' => 'Ben Arous',
                'description' => 'Installation d\'unités de décontamination par osmose inverse et traitement UV pour neutraliser les traces de nitrates et métaux lourds.',
                'project_manager' => 'Dr. Selim Dridi',
                'contractor' => 'AquaFilter Systems Int.',
                'start_date' => '2026-05-15',
                'end_date' => '2026-12-15',
                'planned_budget' => 1450000,
                'actual_spent' => 920000,
                'currency' => 'USD',
                'funding_sources' => ['Federal Environmental Protection Grant (75%)', 'Local Utility Budget (25%)'],
                'completion_pct' => 63,
                'status' => 'In Progress',
                'health' => 'Attention Needed',
                'milestones' => [
                    ['name' => 'Livraison réacteurs UV & filtres à charbon', 'date' => '2026-06-10', 'done' => true],
                    ['name' => 'Montage électromécanique centrale', 'date' => '2026-08-20', 'done' => true],
                    ['name' => 'Calibrage des sondes spectrophotométriques', 'date' => '2026-10-30', 'done' => false],
                ],
                'documents' => [
                    ['name' => 'Etude_Impact_Environnemental.pdf', 'size' => '8.1 MB', 'date' => '2026-05-01'],
                ]
            ],
            [
                'id' => 'PRJ-2026-03',
                'title' => 'Cartographie du réseau d’eau de Sousse & Sahel',
                'code' => 'SMWA-NETWORK-03',
                'category' => 'Network Mapping',
                'municipality' => 'Sousse Municipal Water Authority',
                'zone' => 'Sousse',
                'description' => 'Mise à jour de la cartographie des conduites et centralisation des rapports d’inspection pour faciliter la planification des interventions.',
                'project_manager' => 'Ing. Amira Ben Ali',
                'contractor' => 'Service technique Sousse',
                'start_date' => '2026-01-10',
                'end_date' => '2026-08-31',
                'planned_budget' => 1000000,
                'actual_spent' => 980000,
                'currency' => 'USD',
                'funding_sources' => ['African Development Bank Eco-Fund (100%)'],
                'completion_pct' => 100,
                'status' => 'Completed',
                'health' => 'Completed',
                'milestones' => [
                    ['name' => 'Collecte des plans de conduites', 'date' => '2026-02-28', 'done' => true],
                    ['name' => 'Vérification des points d’accès réseau', 'date' => '2026-07-15', 'done' => true],
                    ['name' => 'Validation de la cartographie et intégration des rapports', 'date' => '2026-08-28', 'done' => true],
                ],
                'documents' => [
                    ['name' => 'Certificat_Recette_Definitive.pdf', 'size' => '2.1 MB', 'date' => '2026-08-30'],
                ]
            ]
        ];
    }

    /**
     * Dashboard Financier & Transparence Budgétaire (120Water Funding & Budget)
     */
    public static function fundingData(): array
    {
        return [
            'total_approved_funding' => 4850000,
            'planned_expenditure'    => 4850000,
            'actual_expenditure'     => 3580000,
            'remaining_budget'       => 1270000,
            'utilization_rate'       => 73.8,
            'funding_sources' => [
                ['name' => 'EU Water Infrastructure Fund', 'amount' => 2100000, 'pct' => 43.3, 'color' => '#0284c7'],
                ['name' => 'Federal Eco-Grant',            'amount' => 1450000, 'pct' => 29.9, 'color' => '#0d9488'],
                ['name' => 'Municipal Infrastructure Bond','amount' =>  800000, 'pct' => 16.5, 'color' => '#3b82f6'],
                ['name' => 'African Development Bank',     'amount' =>  500000, 'pct' => 10.3, 'color' => '#8b5cf6'],
            ],
            'expenditure_history' => [
                ['id' => 'EXP-901', 'date' => '2026-09-20', 'project' => 'PRJ-2026-01', 'category' => 'Pipe Replacement Materials', 'vendor' => 'Siemens Water Tech', 'amount' => 340000, 'status' => 'Approved', 'public' => true],
                ['id' => 'EXP-902', 'date' => '2026-09-12', 'project' => 'PRJ-2026-02', 'category' => 'RO Membranes & Chemicals', 'vendor' => 'DuPont Water Solutions', 'amount' => 185000, 'status' => 'Approved', 'public' => true],
                ['id' => 'EXP-903', 'date' => '2026-09-02', 'project' => 'PRJ-2026-01', 'category' => 'Civil Engineering Labor', 'vendor' => 'SOTUVERA Construction', 'amount' => 220000, 'status' => 'Approved', 'public' => true],
                ['id' => 'EXP-904', 'date' => '2026-08-25', 'project' => 'PRJ-2026-03', 'category' => 'Network Mapping & Inspections', 'vendor' => 'Sousse Water Authority', 'amount' => 140000, 'status' => 'Approved', 'public' => true],
            ]
        ];
    }
}
