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
                'version' => '1.6.0',
                'date' => '29. März 2026',
                'title' => 'Dashboard-Übersicht & Daten-Sync',
                'categories' => [
                    [
                        'name' => 'Dashboard',
                        'items' => [
                            [
                                'title' => 'Letzte 5 Spiele — eigenständige Sektion',
                                'description' => 'Die Formkurve der letzten 5 Partien erscheint jetzt als vollbreite Sektion direkt unterhalb des Nächstes-Spiel-Blocks – mit S/U/N-Übersicht, Vereinslogos und Hover-Tooltip (Ergebnis, Heim/Gast).',
                                'type' => 'ui',
                            ],
                            [
                                'title' => 'Dashboard komplett auf Deutsch',
                                'description' => 'Alle Bezeichnungen, Karten-Labels und Leertext-Meldungen wurden ins Deutsche übersetzt. Budget, Ligaposition, Fan-Stimmung, Lazarett, Kader-Puls, Live-Spiele, Manager Online.',
                                'type' => 'ux',
                            ],
                            [
                                'title' => 'Nächstes Spiel als Hero-Block',
                                'description' => 'Das anstehende Match steht als vollbreiter Hero ganz oben — mit Vereinslogos, Anstoßzeit, Stadionname und Aufstellungs-Status beider Teams.',
                                'type' => 'ui',
                            ],
                        ],
                    ],
                    [
                        'name' => 'Simulation & Stabilität',
                        'items' => [
                            [
                                'title' => 'Max. 15 parallele Spiele',
                                'description' => 'Die Simulationsengine verarbeitet jetzt maximal 15 Spiele gleichzeitig. Der Wert ist über config/simulation.php konfigurierbar.',
                                'type' => 'fix',
                            ],
                            [
                                'title' => 'Broadcast-Fehler nicht mehr spielunterbrechend',
                                'description' => 'WebSocket-Übertragungsfehler (z. B. Payload zu groß oder Reverb nicht erreichbar) brechen die Simulation nicht mehr ab – sie werden als Warnung geloggt und das Spiel läuft weiter.',
                                'type' => 'fix',
                            ],
                            [
                                'title' => 'transferHistories Relation ergänzt',
                                'description' => 'Fehlender Alias transferHistories() im Player-Model ergänzt, der intern an transferHistory() delegiert. Verhinderte zuvor einen Fatal Error während der Simulation.',
                                'type' => 'fix',
                            ],
                        ],
                    ],
                    [
                        'name' => 'Daten-Center',
                        'items' => [
                            [
                                'title' => 'Sofascore ID Finder (Bulk)',
                                'description' => 'Neuer Bulk-Job im Daten-Center sucht automatisch Sofascore-IDs für alle Spieler ohne bestehende Verknüpfung — via Sofascore-Suche mit Namens- und Vereins-Abgleich. Fortschritt sichtbar im Import-Journal.',
                                'type' => 'feature',
                            ],
                            [
                                'title' => 'sofascore_url Referenzen bereinigt',
                                'description' => 'Die veraltete Spalte sofascore_url wurde vollständig aus Fillable, Validierungsregeln und Import-Jobs entfernt. Verhinderte zuvor das Anlegen neuer Spieler per Import.',
                                'type' => 'fix',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'version' => '1.5.0',
                'date' => '29. März 2026',
                'title' => 'Performance-Optimierungen',
                'categories' => [
                    [
                        'name' => 'Datenbank & Backend',
                        'items' => [
                            [
                                'title' => 'Composite DB-Indexes',
                                'description' => 'Neue zusammengesetzte Indizes auf match_live_actions (match_id, minute), match_live_player_states (match_id, club_id, player_id) und matches (status, type) für deutlich schnellere Abfragen während der Live-Simulation.',
                                'type' => 'performance',
                            ],
                            [
                                'title' => 'Constrained Eager Loading',
                                'description' => 'Datenbankabfragen für Live-Aktionen und Minuten-Snapshots werden jetzt direkt auf DB-Ebene auf 400 bzw. 30 Einträge begrenzt – keine nachträgliche PHP-Filterung mehr.',
                                'type' => 'performance',
                            ],
                            [
                                'title' => 'Lineup-Payload Caching',
                                'description' => 'Der aufwendig berechnete Aufstellungs-Payload wird für 30 Sekunden im Cache gehalten und nur bei einer Live-Änderung invalidiert.',
                                'type' => 'performance',
                            ],
                            [
                                'title' => 'League Table Cache-TTL reduziert',
                                'description' => 'Die Livetabelle wird nun alle 5 statt 60 Minuten neu berechnet – aktuellere Daten bei vergleichbarer Last.',
                                'type' => 'fix',
                            ],
                            [
                                'title' => 'Storage URL Request-Cache',
                                'description' => 'Wiederholte Storage::url()-Aufrufe für dasselbe Spielerfoto werden innerhalb eines Requests gecacht, was bei 44 Spielern pro Live-Poll-Zyklus deutlich Rechenzeit spart.',
                                'type' => 'performance',
                            ],
                            [
                                'title' => 'Match-Abschluss als Queue-Job',
                                'description' => 'Die Nachbearbeitungs-Pipeline nach Spielende (Statistiken, Tabelle, Bewertungen) läuft jetzt asynchron als idempotenter Queue-Job, damit der letzte Simulations-Tick nicht blockiert wird.',
                                'type' => 'performance',
                            ],
                        ],
                    ],
                    [
                        'name' => 'Realtime & WebSocket',
                        'items' => [
                            [
                                'title' => 'Broadcast Delta-Payload',
                                'description' => 'Pro WebSocket-Tick werden nur noch die 20 neuesten Aktionen übertragen statt bis zu 400. Der Client merged die eingehenden Daten mit seiner lokalen Historie – identische Darstellung, ~95 % kleinerer Payload.',
                                'type' => 'performance',
                            ],
                            [
                                'title' => 'Live-State direkt aus Broadcast',
                                'description' => 'Das Match-Center wertet den WebSocket-Payload jetzt direkt aus und vermeidet so einen zweiten HTTP-Request pro Tick. Fallback auf HTTP bleibt für ältere Broadcasts erhalten.',
                                'type' => 'performance',
                            ],
                            [
                                'title' => 'LiveOverview Broadcast-Deduplication',
                                'description' => 'Dashboard-Broadcasts werden innerhalb eines 5-Sekunden-Fensters gebündelt, um redundante Übertragungen bei schnell aufeinanderfolgenden Match-Ticks zu verhindern.',
                                'type' => 'performance',
                            ],
                        ],
                    ],
                    [
                        'name' => 'Frontend',
                        'items' => [
                            [
                                'title' => 'React.memo auf allen Match-Center-Komponenten',
                                'description' => 'TickerTab, HighlightsTab, StatsTab, LiveTableTab, PlayersTab, OverviewTab, ScoreHero, LineupPitch, Live2DTab und weitere Komponenten werden nur noch neu gerendert, wenn sich ihre Props tatsächlich geändert haben.',
                                'type' => 'performance',
                            ],
                            [
                                'title' => 'Stabile Handler-Referenzen (useCallback)',
                                'description' => 'Taktik-Handler (Spielstil, Anfeuerungen, Set-Piece-Strategie) in Show.jsx sind jetzt mit useCallback stabilisiert und lösen keine unnötigen Re-Renders in OverviewTab aus.',
                                'type' => 'performance',
                            ],
                        ],
                    ],
                ],
            ],
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
                            [
                                'title' => 'Taktische Pressing-Auslöser',
                                'description' => 'Neue Detail-Einstellungen für das Defensivverhalten: Pressing bei Rückpässen, Ballannahme oder gezielt am Flügel.',
                                'type' => 'feature',
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
