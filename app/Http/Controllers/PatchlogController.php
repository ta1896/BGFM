<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PatchlogController extends Controller
{
    public function index(): Response
    {
        $patchlogs = [
            [
                'version' => '1.4.0',
                'date' => '26. März 2026',
                'title' => 'Match-Center Stabilität & Immersion',
                'categories' => [
                    [
                        'name' => 'Match Center',
                        'items' => [
                            [
                                'title' => 'VAR-System (Video Assistant Referee)',
                                'description' => 'Realistische VAR-Checks für Tore und Elfmeter mit emotionalen Narrativen und zeitlichen Sequenzen.',
                                'type' => 'feature',
                            ],
                            [
                                'title' => 'Visuelle Schusskarte (Shot Map)',
                                'description' => 'Spatial-Analyse aller Torschüsse auf dem Spielfeld inklusive xG-Visualisierung (Größe des Markers) und Ergebnis-Kodierung.',
                                'type' => 'feature',
                            ],
                            [
                                'title' => 'Match Awards & Highlights',
                                'description' => 'Neue visuelle Präsentation für "Spieler des Spiels", "Wendepunkt" und "Parade des Spiels" im Glassmorphism-Design.',
                                'type' => 'ui',
                            ],
                            [
                                'title' => 'Animation-Blending 2.0',
                                'description' => 'Flüssigere Übergänge zwischen Lauf-, Schuss- und Zweikampf-Animationen in der 2D-Ansicht.',
                                'type' => 'visual',
                            ],
                        ],
                    ],
                    [
                        'name' => 'Stabilität & Fixes',
                        'items' => [
                            [
                                'title' => 'Render-Error Fix (TypeError)',
                                'description' => 'Behebung von Abstürzen im Match-Center durch robuste Daten-Guards (Array.isArray) für alle Listen und Aktionen.',
                                'type' => 'fix',
                            ],
                            [
                                'title' => 'Pusher Payload Optimierung',
                                'description' => 'Reduzierung der Broadcast-Payload-Größe im Live-Overview zur Vermeidung von Übertragungsfehlern.',
                                'type' => 'fix',
                            ],
                        ],
                    ],
                    [
                        'name' => 'Navigation & UX',
                        'items' => [
                            [
                                'title' => 'Klickbare Entitäten',
                                'description' => 'Spieler und Vereine sind nun konsistent in allen Match-Center-Bereichen und Übersichten anklickbar.',
                                'type' => 'ux',
                            ],
                            [
                                'title' => 'Änderungsprotokoll (Patchlogs)',
                                'description' => 'Einführung dieser Übersicht zur besseren Nachvollziehbarkeit aller System-Updates.',
                                'type' => 'feature',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'version' => '1.3.0',
                'date' => '25. März 2026',
                'title' => 'Performance & Roadmap',
                'categories' => [
                    [
                        'name' => 'System',
                        'items' => [
                            [
                                'title' => 'Roadmap Redesign',
                                'description' => 'Komplette Überarbeitung des Roadmap-Boards mit 200 neuen Konzepten und Drag-and-Drop Sortierung.',
                                'type' => 'feature',
                            ],
                            [
                                'title' => 'Performance Tuning',
                                'description' => 'Optimierung des LCP (Largest Contentful Paint) durch verbessertes Asset-Loading und Caching.',
                                'type' => 'performance',
                            ],
                        ],
                    ],
                ],
            ],
        ];

        return Inertia::render('Patchlogs/Index', [
            'patchlogs' => $patchlogs,
        ]);
    }
}
