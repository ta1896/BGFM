# OpenWebSoccer Gap Backlog

Stand: 2026-02-13  
Vergleichsbasis: `NewGen` vs. `ihofmann/open-websoccer`

## Ziel
Schrittweise Aufholung der wichtigsten funktionalen Luecken in Simulation, Live-Ticker, Regelwerk und Betriebsstabilitaet.

## Priorisierte Backlog-Items

| Prio | Titel | Ergebnis |
|---|---|---|
| P0 | Simulations-Engine modularisieren (Executor/Strategy/Observer) | Klare Trennung von Tick-Orchestrierung, Spielstrategie und Seiteneffekten |
| P0 | Match-Logik pro Aktion verfeinern und parametrierbar machen | Realistischere Ereignisketten und einstellbare Wahrscheinlichkeiten |
| P0 | Live-Eingriffe mit geplanten Wechseln und Bedingungen | Manager kann Wechsel/Taktik geplant und regelkonform setzen |
| P0 | Positions-Malus differenziert (Haupt/Neben/Fremdposition) | Plausible Staerkeabzuege je nach Einsatzposition |
| P0 | Spieler-Datenmodell vertiefen | Haupt-/Second-/Third-Position, getrennte Sperren/Statistiken je Wettbewerb, klare Trennung Tore vs. Vorlagen |
| P0 | Match-Processing idempotent machen | Start/Finish/Abrechnung/Tabelle wirken bei Retry/Parallel-Run exakt einmal |
| P0 | Wettbewerbskontext als Core-Domain einziehen | Einheitliches Regelmodell fuer league/cup_national/cup_international/friendly |
| P0 | Migrations- und Backfill-Plan fuer Spieler/Verein | Sicherer Datenuebergang auf Positionen/Sperren/Statistiken ohne Datenverlust |
| P0 | Single Source of Truth fuer Statistiken | Zentrale Aggregation Match -> Saison -> Karriere ohne Drift zwischen Sichten |
| P0 | Deterministische Simulations-Regression | Seed-basierte Golden-Tests verhindern stille Gameplay-Regressionen |
| P0 | Betriebs-Hardening der Simulation | Last-/Parallel-Runner-Resilienz, Crash-Recovery-Drills und Monitoring/Alerting fuer produktionssicheren Dauerbetrieb |
| P1 | Pokal-Regelwerk vertiefen | Hin-/Rueckspiel, Auswaertstor/Turnierpfade, robustere Folgerundenlogik |
| P1 | Vereins-Datenmodell vertiefen | Mehr Vereinsdetails (Saisonziel/Historie/Rollen) und klare Saison-vs.-Gesamttrennung |
| P1 | Pokal-Wettbewerbe trennen (national/international) | Nationale und internationale Cup-Wettbewerbe mit separaten Regeln, Qualifikation und Auswertungen |
| P1 | Live-State-Persistenz erweitern | Mehr Simulationstate fuer sauberes Resume und Debugging |
| P1 | Cron/Queue-Sicherheit fuer Simulation | Keine Doppelberechnung bei Parallelitaet, bessere Fehlerrecovery |
| P1 | Matchday-Briefing vor Spielen | Kompakte Vorab-Ansicht mit Gegnerform, Sperren/Verletzungen und Aufstellungs-Hinweisen |
| P1 | Kader-Health-Ansicht | Zentrale Seite fuer Fitness, Sperren, Gelb-Gefahr und Vertragslaufzeiten |
| P1 | Lineup-Vorlagen pro Gegnerprofil | Schnellwahl passender Aufstellungs-Templates je Gegnerstaerke und Spielkontext |
| P1 | Finanz-Prognose (4-8 Wochen) | Vorschau auf Budgetentwicklung inkl. Gehaelter, Sponsoren, Stadion- und Transfereffekte |
| P1 | Benachrichtigungs-Center mit Prioritaeten | Wichtige Meldungen werden priorisiert, Rest gebuendelt und besser filterbar |
| P1 | Onboarding fuer Manager ohne Verein | Gefuehrter Einstieg von Clubwahl bis erste Aufstellung/erstes Spiel |
| P2 | Admin-Simulationskonfiguration | Intervall, Limits, Regelparameter, Strategie/Observer steuerbar |
| P2 | Nachwirkungen nach Match vertiefen | Sperren/Verletzungen/Vertragsfolgen einheitlich und testbar |
| P2 | Transfer-Shortlist mit Erinnerungen | Beobachtungsliste mit Hinweisen bei Preis-/Statusaenderungen |
| P2 | Training-Plaene als Presets | Vordefinierte Wochenplaene fuer Formaufbau, Regeneration und Schwerpunkttraining |
| P2 | Spielbericht-Insights | Automatische Match-Analyse mit Schluesselmomenten und konkreten Handlungstipps |
| P2 | Mobile Quick Actions | Schnelle Kernaktionen auf Mobile (Aufstellung, Wechselplanung, Matchcenter) |

## Umsetzungsstand (P0)

Stand umgesetzt am 2026-02-13:

- Erledigt: Simulations-Engine modularisieren (Executor/Strategy/Observer)
  - `MatchSimulationExecutor`, `DefaultSimulationStrategy` und Observer-Pipeline produktiv eingebunden.
- Erledigt: Match-Logik pro Aktion verfeinern und parametrierbar machen
  - Kernwahrscheinlichkeiten und Formeln zentral in `config/simulation.php`.
- Erledigt: Live-Eingriffe mit geplanten Wechseln und Bedingungen
  - Persistente Wechselplaene inkl. Minute/Bedingung, UI im Matchcenter, regelkonforme Ausfuehrung.
- Erledigt: Positions-Malus differenziert (Haupt/Neben/Fremdposition)
  - `position_main/second/third`-Profil und konsistente Fit-Faktoren in Live/Team/Stats/UI.
- Erledigt: Spieler-Datenmodell vertiefen (aktueller Lieferstand)
  - Kontextgetrennte Sperrzaehler (`league/cup_national/cup_international/friendly`) eingefuehrt.
  - Saison- und Karriere-Player-Statistik je Wettbewerbskontext eingefuehrt (`player_*_competition_statistics`).
  - Tore und Vorlagen bleiben getrennt von Match bis Aggregat.
- Erledigt: Match-Processing idempotent machen (aktueller Lieferstand)
  - Start/Finish mit DB-Lock + Status-Gate gegen Doppelwirkung bei Retry/Parallel-Run abgesichert.
  - Post-Processing-Schritte ueber `match_processing_steps` (unique `match_id + step`) exakt-einmal-geschuetzt.
  - Observer fuer Stats/Availability/Competition/Finance laufen idempotent.
- Erledigt: Wettbewerbskontext als Core-Domain einziehen (aktueller Lieferstand)
  - Match-Ebene um `competition_context` erweitert (`league`, `cup_national`, `cup_international`, `friendly`) inkl. Backfill.
  - Zentraler Resolver `CompetitionContextService` als Regelquelle fuer Cup-/Liga-/Friendly-Entscheidungen.
  - Kernservices (LiveTicker, Competition-Observer, Cup-Progression, Fixture/Friendly-Erzeugung) auf den zentralen Kontext ausgerichtet.
- Erledigt: Migrations- und Backfill-Plan fuer Spieler/Verein (aktueller Lieferstand)
  - Wiederholbarer Backfill-Command `game:backfill-player-club-model` mit `--dry-run` und konfigurierbarer Chunk-Groesse.
  - Backfill-Regeln fuer Spielerprofil (`position_main/second/third`), Legacy-vs.-Kontext-Sperren und Status-Reparatur umgesetzt.
  - Vereins-Backfill fuer `slug` und `short_name` plus Vorher/Nachher-Integritaets-Audit verfuegbar.
- Erledigt: Single Source of Truth fuer Statistiken (aktueller Lieferstand)
  - Zentrale Aggregation in `StatisticsAggregationService` als gemeinsame Quelle fuer League-/Club-/Player-Statistiken.
  - `LeagueTableService`, `PlayerCompetitionStatsService`, `ClubController` und Direkt-Simulation greifen auf dieselbe Aggregationslogik zu.
  - Rebuild/Audit-Command `game:rebuild-statistics` fuer Reparaturlaeufe und Drift-Kontrolle verfuegbar.
- Erledigt: Deterministische Simulations-Regression (aktueller Lieferstand)
  - Seed-basierte Golden-Tests fuer Direkt-Simulation und Live-Ticker in `tests/Feature/DeterministicSimulationRegressionTest.php` eingefuehrt.
  - Zufallsquellen fuer Snapshot-relevante Pfade auf seeded RNG ausgerichtet (`MatchSimulationService`, `LiveMatchTickerService`).
  - Golden-Hashes fuer reproduzierbare Regression-Gates aktualisiert und in wiederholten Testlaeufen verifiziert.
- Erledigt: Betriebs-Hardening der Simulation (Teil 1: Run-Monitoring + Last/Parallel-Drills)
  - Persistente Scheduler-Run-Logs in `simulation_scheduler_runs` eingefuehrt (Status, Parameter, Claim-/Processing-Metriken, Stale-Takeovers, Fehlermetrik).
  - `game:simulate-matches` schreibt pro Lauf strukturierte Betriebsdaten (`completed`, `completed_with_errors`, `skipped_locked`, `skipped_interval`, `failed`).
  - Health-Command `game:simulation-health` bereitgestellt (letzte Laeufe + Warnindikatoren fuer Fehler/Lock-Haeufung/Stale-Takeovers).
  - Last-/Parallelitaets-Regressionen in `tests/Feature/SimulateScheduledMatchesCommandTest.php` ausgebaut (High-Volume, Active-/Stale-Claim-Drill, Runner-Lock-Drill).
- Erledigt: Betriebs-Hardening der Simulation (Teil 2: Crash-Recovery + Alert-Schwellen)
  - Stale Scheduler-Runs mit Status `running` werden beim Start eines neuen Laufs automatisch als `abandoned` markiert (Timeout-basiert).
  - `game:simulation-health` um konfigurierbare Alert-Schwellen erweitert (`failed`, `skipped_locked`, `stale_takeovers`, `abandoned`) inkl. `--strict` Exit-Code.
  - Konfigurationsblock `simulation.scheduler.health.*` eingefuehrt (Schwellenwerte + `running_stale_after_seconds`).
  - Crash-Recovery- und Strict-Alert-Drills als Feature-Tests in `tests/Feature/SimulateScheduledMatchesCommandTest.php` abgesichert.
- Erledigt: Betriebs-Hardening der Simulation (Teil 3: Geplanter Strict-Healthcheck + Alert-Logging)
  - Neuer Command `game:simulation-health-check` als operativer Wrapper fuer `game:simulation-health` (inkl. Strict-Mode-Vererbung und konfigurierbarem Limit).
  - Bei Alarmfall schreibt der Check strukturierte Error-Logs (`exit_code`, `strict`, `output`) fuer externes Monitoring/Alerting.
  - Optionales Success-Logging und geplanter Cron-Lauf (`everyFiveMinutes`) ueber `simulation.scheduler.health.check_*`.
  - Abgesichert durch erweiterte Feature-Tests (`health-check` Fehler-/Success-Pfade) in `tests/Feature/SimulateScheduledMatchesCommandTest.php`.

## Umsetzungsstand (P1)

Stand umgesetzt am 2026-02-13:

- Erledigt: Pokal-Regelwerk vertiefen (Teil 1: Folgerunden-Stabilitaet)
  - Cup-Folgerundenbildung robust bei ungerader Siegerzahl: automatisches Freilos-Match statt Teilnehmerverlust.
  - Gewinnerermittlung bei bereits gespieltem Remis ohne Penaltydaten abgesichert (deterministischer Tiebreak, kein Progressions-Stall).
  - Rundenbenennung verbessert (`Finale`, `Halbfinale`, `Viertelfinale`, `Achtelfinale`, sonst `Cup Runde X`).
  - Abgesichert durch `tests/Feature/CupProgressionServiceTest.php`.
- Erledigt: Pokal-Regelwerk vertiefen (Teil 2: Hin-/Rueckspiel + Aggregate)
  - Optionale Hin-/Rueckspiel-Generierung pro Cup-Runde (`simulation.cup.two_legged.*`) inkl. konfigurierbarem Abstand zwischen beiden Legs.
  - Gewinnerermittlung je Tie ueber Aggregate, optional Auswaertstore (`simulation.cup.away_goals_rule`) und Decider-/Penalty-Fallback.
  - Tie-Gruppierung robust ueber Klubpaar statt Match-Einzelwertung, damit Hin-/Rueckspiel korrekt als ein Duell aufgeloest wird.
  - Abgesichert durch erweiterte `tests/Feature/CupProgressionServiceTest.php`.
- Erledigt: Pokal-Regelwerk vertiefen (Teil 3: Finanz-/Achievement-Hooks)
  - Idempotente Cup-Praemien fuer Rundenfortschritt und Titelgewinn ueber `cup_reward_logs` (unique Event-Key pro Verein/Wettbewerb).
  - Finanzbuchung bei Cup-Fortschritt als Einnahme in `club_financial_transactions` inkl. Budget-Update.
  - Manager-Benachrichtigung fuer Pokal-Erfolge (`cup_achievement`) bei Fortschritt und Pokalsieg.
  - Konfigurierbar ueber `simulation.cup.rewards.*` (Aktivierung, Staffel je Runde, Champion-Bonus, Notifications).
  - Abgesichert durch erweiterte `tests/Feature/CupProgressionServiceTest.php`.
- Erledigt: Vereins-Datenmodell vertiefen (Teil 1: Saisonziel + Historie/Trennung)
  - Vereinsfeld `season_objective` (Klassenerhalt/Mittelfeld/Aufstieg/Meisterschaft/Pokalrunde) eingefuehrt und in Manager-/Admin-Formularen verfuegbar.
  - Club-Statistik um kontextgetrennte Auswertung (`league`, `cup_national`, `cup_international`, `friendly`) erweitert.
  - Saisonhistorie pro Verein (letzte Saisons) als eigene Auswertung im Clubprofil ergaenzt.
  - Abgesichert durch `tests/Feature/ClubStatisticsHistoryTest.php`.
- Erledigt: Vereins-Datenmodell vertiefen (Teil 2: Teamrollen Captain/Vize)
  - Vereinsrollen `captain_player_id` und `vice_captain_player_id` als eigene, referenzielle Club-Felder eingefuehrt.
  - Rollenpflege im ACP-Clubformular integriert, strikt auf Spieler des jeweiligen Vereins validiert.
  - Clubprofil zeigt gesetzte Teamrollen fuer Manager sichtbar an.
  - Abgesichert durch `tests/Feature/ClubTeamRolesTest.php`.
- Erledigt: Pokal-Wettbewerbe trennen (Teil 1: explizite Wettbewerbsreichweite)
  - Wettbewerbe besitzen nun optionale Pokal-Reichweite `scope` (`national`/`international`) als explizites Domain-Feld.
  - Kontextauflosung priorisiert `scope` und faellt nur noch als Fallback auf `country_id` zurueck.
  - Admin-UI fuer Wettbewerbe um Reichweitenauswahl erweitert; internationale Cups werden ohne Landesbindung gespeichert.
  - Abgesichert durch erweiterten `tests/Feature/CompetitionContextServiceTest.php`.
- Erledigt: Pokal-Wettbewerbe trennen (Teil 2: internationale Qualifikation)
  - Regelbasierte Qualifikation aus Top-Liga in internationale Cups ueber `CupQualificationService` eingefuehrt (konfigurierbare Slots je Cup-Tier).
  - Qualifikationen werden pro Land synchronisiert (inkl. Bereinigung veralteter Registrierungen) und sind idempotent.
  - Optionale Auto-Erzeugung der ersten Cup-Runde fuer internationale Wettbewerbe integriert (`simulation.cup.qualification.auto_generate_fixtures`).
  - Abgesichert durch `tests/Feature/CupQualificationServiceTest.php` sowie Regression in `tests/Feature/SeasonProgressionTest.php`.
- Erledigt: Live-State-Persistenz erweitern (Teil 1: Kontextfelder + Transition-History)
  - Team-Live-State erweitert um persistente Kontextfelder (`current_ball_carrier_player_id`, letzter Set-Piece-Taker/-Typ/-Minute).
  - Historisierung fuer Simulationszustandswechsel in `match_live_state_transitions` eingefuehrt (z. B. Matchstart/-ende, Phasenwechsel, Taktikwechsel, Wechsel, Verletzung, Platzverweis).
  - Phasenwechsel werden teambezogen persistiert und sind fuer Resume/Debugging eindeutig nachvollziehbar.
  - Abgesichert durch `tests/Feature/LiveStatePersistenceTest.php`.
- Erledigt: Live-State-Persistenz erweitern (Teil 2: Minuten-Snapshots + Plan-Transitions)
  - Persistente Live-Minuten-Snapshots je Match (`match_live_minute_snapshots`) eingefuehrt, idempotent pro `match_id + minute`.
  - Snapshot enthaelt Spielstand, Teamphase/Taktik, Plan-Statuszaehler (`pending/executed/skipped/invalid`) und kompakten Team-Kontext im JSON-Payload.
  - Geplante Wechsel werden als eigene State-Transitions historisiert (`substitution_plan_scheduled/executed/skipped/invalid`) fuer Resume/Debugging.
  - Live-State-API liefert die letzten Snapshots im State-Payload fuer Diagnose und UI-Anbindung.
  - Abgesichert durch erweiterte `tests/Feature/LiveStatePersistenceTest.php`.
- Erledigt: Cron/Queue-Sicherheit fuer Simulation (Teil 1: DB-Claiming + Recovery-Monitoring)
  - Simulations-Runner claimt Matches atomar per DB-Lock (`live_processing_token`, `live_processing_started_at`) statt blindem Durchlauf.
  - Aktive Claims werden respektiert, stale Claims (Timeout) koennen uebernommen werden, damit haengende Worker keine Dauer-Blockade erzeugen.
  - Monitoring-Felder fuer Laufhistorie/Fehler (`live_processing_last_run_at`, `live_processing_attempts`, `live_processing_last_error`) pro Match eingefuehrt.
  - Fehlerpfad setzt Match weiter kontrolliert auf `live_paused` und gibt den Claim sicher frei.
  - Abgesichert durch erweiterten `tests/Feature/SimulateScheduledMatchesCommandTest.php`.
- Erledigt: Cron/Queue-Sicherheit fuer Simulation (Teil 2: Globaler Runner-Lock + Parallel-Run-Guard)
  - Globaler Scheduler-Runner-Lock ueber Cache-Lock (`simulation:scheduler:runner`) verhindert konkurrierende `game:simulate-matches`-Laeufe.
  - Lock-TTL ist als Runtime-Setting steuerbar (`simulation.scheduler.runner_lock_seconds`) und in der Admin-Simulationskonfiguration pflegbar.
  - Intervall-Guard wird innerhalb des Locks ausgewertet, damit parallele Runner nicht gegeneinander laufen.
  - Abgesichert durch erweiterten `tests/Feature/SimulateScheduledMatchesCommandTest.php` (`test_command_skips_run_when_global_runner_lock_is_active`).

## Umsetzungsstand (P2)

Stand umgesetzt am 2026-02-13:

- Erledigt: Admin-Simulationskonfiguration (Teil 1: persistente Runtime-Settings)
  - Persistente Tabelle `simulation_settings` mit Runtime-Override in `AppServiceProvider` eingefuehrt.
  - ACP-Dashboard um Admin-Form fuer Scheduler-Intervall/Limit/Typen, Claim-Timeout, Positionsfaktoren, Planned-Sub-Limits und Observer-Schalter erweitert.
  - `game:simulate-matches` nutzt persistierte Default-Werte und Intervall-Guard inkl. `--force`-Bypass.
  - Match-Observer-Pipeline kann per Setting aktiviert/deaktiviert werden (`simulation.observers.match_finished.enabled`).
  - Abgesichert durch `tests/Feature/AdminSimulationSettingsTest.php` und erweiterten `tests/Feature/SimulateScheduledMatchesCommandTest.php`.
- Erledigt: Admin-Simulationskonfiguration (Teil 2: granulare Observer-Auswahl)
  - Post-Match-Observer im ACP einzeln schaltbar gemacht (Stats-Rebuild, Competition-Stats-Aggregation, Availability, Competition-Update, Finance-Settlement).
  - Observer-Pipeline respektiert die granularen Runtime-Settings in stabiler Reihenfolge.
  - Persistenz und Runtime-Override fuer alle Observer-Toggles in `simulation_settings` integriert.
  - Abgesichert durch `tests/Feature/AdminSimulationSettingsTest.php` und `tests/Feature/MatchFinishedObserverPipelineOrderTest.php`.
- Erledigt: Nachwirkungen nach Match vertiefen (Teil 1: Regelmatrix + Notifications/Vertrag)
  - Verfuegbarkeitsfolgen (Verletzung/Sperre) laufen jetzt ueber eine zentrale, konfigurierbare Regelmatrix in `simulation.aftermath.*` (inkl. Kontextwerte fuer `league/cup_national/cup_international/friendly`).
  - Post-Match-Nachwirkungen werden zentral ueber `MatchAftermathService` ausgefuehrt und an Manager-Benachrichtigungen angeschlossen (`match_aftermath`).
  - Vertragsfolgen angedockt: bei ausfallenden Spielern mit nahem Vertragsende wird eine Vertragswarnung erzeugt (`contract_attention`).
  - Abgesichert durch `tests/Feature/MatchAftermathConsequencesTest.php`.
- Erledigt: Nachwirkungen nach Match vertiefen (Teil 2: Gelbkarten-Staffelung + Saisonreset)
  - Kontextgetrennte Gelbkarten-Konten pro Spieler eingefuehrt (`league/cup_national/cup_international/friendly`) inkl. Migration.
  - Konfigurierbare Gelb-Schwellen/Sperrdauer in `simulation.aftermath.yellow_cards.*` mit automatischer Sperrenvergabe bei Erreichen des Limits.
  - Match-Nachwirkungen benachrichtigen Gelb-Sperren explizit (`Gelb-Sperre`) und unterscheiden sie von allgemeinen Sperren.
  - Saisonwechsel setzt Gelbkarten-Konten optional zentral zurueck (`reset_on_season_rollover`).
  - Abgesichert durch erweiterte `tests/Feature/MatchAftermathConsequencesTest.php` und `tests/Feature/SeasonProgressionTest.php`.

## Issue-Schnitt (ready to implement)

### 1) Simulations-Engine modularisieren (Executor/Strategy/Observer)
- Scope:
  - Tick-Orchestrierung in eigenen Executor auslagern
  - Aktionslogik in Strategy kapseln
  - Nachgelagerte Effekte (Stats/Finanzen/Tabellen) als Observer-Pipeline
- Akzeptanz:
  - `LiveMatchTickerService` deutlich entlastet
  - Integrationstests fuer Pipeline-Reihenfolge vorhanden
- Referenzen:
  - `app/Services/LiveMatchTickerService.php`
  - `_reference_open_websoccer/websoccer/classes/MatchSimulationExecutor.class.php`
  - `_reference_open_websoccer/websoccer/classes/Simulator.class.php`

### 2) Match-Logik pro Aktion verfeinern und parametrierbar machen
- Scope:
  - Wahrscheinlichkeiten fuer Pass/Tackle/Foul/Freistoss/Ecke/Elfer zentralisieren
  - Konfigurierbare Balancewerte in `config/`
  - Eindeutige Ereignisketten je Sequenz
- Akzeptanz:
  - Regelparameter ohne Codeaenderung anpassbar
  - Deterministische Tests fuer Kernpfade
- Referenzen:
  - `app/Services/LiveMatchTickerService.php`
  - `_reference_open_websoccer/websoccer/classes/DefaultSimulationStrategy.class.php`

### 3) Live-Eingriffe mit geplanten Wechseln und Bedingungen
- Scope:
  - Wechselplaene mit Minute + Matchzustand (Fuehrung/Remis/Rueckstand)
  - Intervall- und Grenzvalidierung fuer spaete Aenderungen
  - UI fuer geplante Live-Aktionen
- Akzeptanz:
  - Geplante Wechsel werden zur passenden Minute verlässlich ausgefuehrt
  - Unzulaessige Aenderungen werden sauber abgefangen
- Referenzen:
  - `app/Http/Controllers/MatchCenterController.php`
  - `resources/views/leagues/matchcenter.blade.php`
  - `_reference_open_websoccer/websoccer/classes/actions/SaveMatchChangesController.class.php`
  - `_reference_open_websoccer/websoccer/templates/default/views/match_live_changes.twig`

### 4) Positions-Malus differenziert (Haupt/Neben/Fremdposition)
- Scope:
  - Unterscheidung Hauptposition, Nebenposition, Fremdposition
  - Maluswerte konfigurierbar machen
  - Live- und Stat-Bewertung konsistent auf denselben Faktor stutzen
- Akzeptanz:
  - Positionseffekt im Matchcenter nachvollziehbar
  - Tests decken alle Positionsklassen ab
- Referenzen:
  - `app/Services/PlayerPositionService.php`
  - `app/Services/LiveMatchTickerService.php`
  - `_reference_open_websoccer/websoccer/modules/simulation/module.xml`
  - `_reference_open_websoccer/websoccer/classes/actions/SaveMatchChangesController.class.php`

### 5) Pokal-Regelwerk vertiefen
- Scope:
  - Erweiterte Gewinnerermittlung (z. B. Hin-/Rueckspiel)
  - Robuste Folgerundenbildung inkl. ungerader Faelle
  - Optionale Finanz-/Achievement-Hooks
- Akzeptanz:
  - Cup-Progression bleibt stabil bei Sonderfaellen
  - End-to-end Tests fuer mehrere Cup-Runden
- Referenzen:
  - `app/Services/LiveMatchTickerService.php`
  - `_reference_open_websoccer/websoccer/classes/SimulationCupMatchHelper.class.php`

### 6) Live-State-Persistenz erweitern
- Scope:
  - Zusatzzustaende speichern (z. B. Ballfuehrer, Set-Piece-Rollen, geplante Wechsel)
  - Resume-Faehigkeit verbessern
  - Historisierung wichtiger Simulationszustandswechsel
- Akzeptanz:
  - Match kann mitten im Lauf robust wiederaufgenommen werden
  - Debugbare State-Transitions je Match
- Referenzen:
  - `database/migrations/2026_02_13_032000_create_match_live_state_tables.php`
  - `app/Models/MatchLivePlayerState.php`
  - `app/Models/MatchLiveTeamState.php`
  - `_reference_open_websoccer/websoccer/classes/SimulationStateHelper.class.php`

### 7) Cron/Queue-Sicherheit fuer Simulation
- Scope:
  - Datenbankseitiges Locking pro Match (statt nur Scheduler-Overlap-Schutz)
  - Sichere Fehlerbehandlung und Wiederanlauf
  - Monitoring-Felder fuer Lastlauf/Retry
- Akzeptanz:
  - Keine Doppel-Simulation unter Parallelitaet
  - Lasttests fuer konkurrierende Runner vorhanden
- Referenzen:
  - `app/Jobs/SimulateScheduledMatchesJob.php`
  - `routes/console.php`
  - `_reference_open_websoccer/websoccer/classes/MatchSimulationExecutor.class.php`

### 8) Admin-Simulationskonfiguration
- Scope:
  - Einstellbare Parameter fuer Intervall, Max-Matches, Positionsmalus, Live-Aenderungslimits
  - Optional Strategie-/Observer-Auswahl
- Akzeptanz:
  - Einstellungen sind persistiert und wirken im Lauf
  - Guardrails fuer ungueltige Werte
- Referenzen:
  - `routes/console.php`
  - `app/Services/LiveMatchTickerService.php`
  - `_reference_open_websoccer/websoccer/modules/simulation/module.xml`

### 9) Nachwirkungen nach Match vertiefen
- Scope:
  - Einheitliche Regelmatrix fuer Verletzung/Sperre/Folgespiele
  - Konsequenzen in Vertrags- und Benachrichtigungslogik anschliessen
- Akzeptanz:
  - Verfuegbarkeitszaehler verhalten sich liga- und cup-konform
  - Regressionstests fuer mehrteilige Matchserien
- Referenzen:
  - `app/Services/LiveMatchTickerService.php`
  - `tests/Feature/LiveMatchAdvancedRulesTest.php`
  - `_reference_open_websoccer/websoccer/classes/DataUpdateSimulatorObserver.class.php`

### 10) Spieler-Datenmodell vertiefen (Haupt/Second/Third + getrennte Sperren/Stats)
- Scope:
  - Positionsschema auf drei Rollen erweitern (`position_main`, `position_second`, `position_third`)
  - Sperrzaehler je Wettbewerb trennen (Liga, nationaler Pokal, internationaler Wettbewerb)
  - Saison-/Karriere-Statistiken je Wettbewerb und je Metrik sauber trennen
  - Tore und Vorlagen als strikt getrennte Kennzahlen entlang Match -> Saison -> Karriere durchziehen
- Akzeptanz:
  - Jeder Spieler kann Haupt-, Second- und Third-Position speichern/anzeigen
  - Sperren greifen nur im jeweiligen Wettbewerb (keine liga/cup/international Vermischung)
  - Tore und Vorlagen sind getrennt in UI, API und Aggregation sichtbar
- Referenzen:
  - `app/Models/Player.php`
  - `app/Models/MatchPlayerStat.php`
  - `app/Services/LiveMatchTickerService.php`
  - `_reference_open_websoccer/websoccer/install/ws3_ddl_full.sql`
  - `_reference_open_websoccer/websoccer/classes/services/PlayersDataService.class.php`

### 11) Vereins-Datenmodell vertiefen (Saisonziel/Historie/Rollen)
- Scope:
  - Erweiterte Vereinsfelder (z. B. Saisonziel, Historie, optionale Teamrollen wie Kapitaen)
  - Saisondaten und Gesamt-/Allzeitdaten konsistent trennen
  - Vereinsstatistiken vereinheitlichen (eine Quelle fuer Tabelle, Historie, Clubprofil)
- Akzeptanz:
  - Vereinsprofil bildet Saisonziel/Historie nachvollziehbar ab
  - Saison- und Allzeitwerte sind getrennt und widerspruchsfrei
  - Tabellenansicht und Clubprofil liefern identische Kernwerte
- Referenzen:
  - `app/Models/Club.php`
  - `app/Models/SeasonClubStatistic.php`
  - `app/Services/LeagueTableService.php`
  - `_reference_open_websoccer/websoccer/install/ws3_ddl_full.sql`
  - `_reference_open_websoccer/websoccer/classes/services/TeamsDataService.class.php`

### 12) Pokal-Wettbewerbe trennen (national vs. international)
- Scope:
  - Wettbewerbstypen fuer Pokale um Reichweite erweitern (`national`, `international`)
  - Separate Teilnehmerlogik (nationale Meldung vs. internationale Qualifikation)
  - Eigene Auswertungen/Statistiken je Pokalreichweite inkl. Sperrenbezug
  - Admin- und Spielansichten fuer beide Pokalklassen getrennt bedienbar machen
- Akzeptanz:
  - Nationale und internationale Pokale koennen parallel in derselben Saison laufen
  - Qualifikation fuer internationale Pokale ist regelbasiert und testbar
  - Sperren/Statistiken werden dem richtigen Pokalwettbewerb zugeordnet
- Referenzen:
  - `app/Models/Competition.php`
  - `app/Models/CompetitionSeason.php`
  - `app/Services/LiveMatchTickerService.php`
  - `_reference_open_websoccer/websoccer/install/ws3_ddl_full.sql`
  - `_reference_open_websoccer/websoccer/classes/SimulationCupMatchHelper.class.php`

### 13) Match-Processing idempotent machen
- Scope:
  - Idempotenzgrenzen fuer Start/Finish/Tabelle/Finanzbuchung definieren
  - DB-seitige Guardrails (Unique Keys, Status-Gates, optionale Verarbeitungsschluessel)
  - Retry-/Parallel-Run-sichere Verarbeitung im Scheduler/Job/Service etablieren
- Akzeptanz:
  - Ein Match erzeugt bei Mehrfachausfuehrung keine doppelten Endeffekte
  - Abrechnung und Tabellenupdate passieren pro Matchabschluss genau einmal
  - Parallel laufende Worker verursachen keine inkonsistenten Matchzustaende
- Referenzen:
  - `app/Services/LiveMatchTickerService.php`
  - `app/Jobs/SimulateScheduledMatchesJob.php`
  - `routes/console.php`
  - `app/Services/LeagueTableService.php`
  - `app/Services/FinanceCycleService.php`

### 14) Wettbewerbskontext als Core-Domain einziehen
- Scope:
  - Regelkontext vereinheitlichen: `league`, `cup_national`, `cup_international`, `friendly`
  - Kontext zentral fuer Sperren, Stats, Qualifikation und Matcherzeugung nutzbar machen
  - Regelabfragen aus Einzelservices in ein gemeinsames Domain-Modell verlagern
- Akzeptanz:
  - Jeder Matchlauf besitzt einen eindeutigen Wettbewerbskontext
  - Sperren/Statistiken werden konsequent kontextbezogen verarbeitet
  - Qualifikation fuer Wettbewerbe folgt einheitlichen, testbaren Regeln
- Referenzen:
  - `app/Models/Competition.php`
  - `app/Models/CompetitionSeason.php`
  - `app/Models/GameMatch.php`
  - `app/Services/LiveMatchTickerService.php`
  - `app/Services/SeasonProgressionService.php`

### 15) Migrations- und Backfill-Plan fuer Spieler/Verein
- Scope:
  - Stufenplan fuer Schema-Erweiterung (Positionsrollen, getrennte Sperren, getrennte Tore/Vorlagen)
  - Backfill-Strategie inkl. Mapping-Regeln und Validierungen
  - Rollback-/Sicherungsstrategie fuer produktive Datenmigrationen
- Akzeptanz:
  - Migrationen laufen ohne Datenverlust durch
  - Alte Daten sind nach Backfill vollstaendig und konsistent nutzbar
  - Vorher/Nachher-Checks fuer Kernmetriken sind automatisiert vorhanden
- Referenzen:
  - `database/migrations/2026_02_12_161755_create_clubs_table.php`
  - `database/migrations/2026_02_12_161800_create_players_table.php`
  - `database/migrations/2026_02_12_172449_optimize_simulation_database_structure.php`
  - `app/Services/PlayerClubBackfillService.php`
  - `routes/console.php`
  - `app/Models/Player.php`
  - `app/Models/Club.php`

### 16) Single Source of Truth fuer Statistiken
- Scope:
  - Zentrale Aggregationspipeline fuer Match -> Saison -> Karriere einziehen
  - Lesesichten (Club, Liga, Spieler) nur noch aus definierten Aggregaten speisen
  - Inkonsistenz-Checks und Rebuild-Jobs fuer Stat-Integritaet bereitstellen
- Akzeptanz:
  - Dieselbe Kennzahl liefert in allen Sichten denselben Wert
  - Rebuild erzeugt reproduzierbare Ergebnisse aus denselben Rohdaten
  - Keine konkurrierenden Berechnungswege fuer dieselben Metriken
- Referenzen:
  - `app/Models/MatchPlayerStat.php`
  - `app/Models/SeasonClubStatistic.php`
  - `app/Services/StatisticsAggregationService.php`
  - `app/Services/LeagueTableService.php`
  - `app/Services/LiveMatchTickerService.php`
  - `app/Http/Controllers/ClubController.php`

### 17) Deterministische Simulations-Regression
- Scope:
  - Golden-Test-Suite mit festen Seeds fuer repraesentative Matchszenarien
  - Snapshot/Vergleich relevanter Outputs (Events, Live-Actions, Player-Stats, Ergebnis)
  - Regression-Gates fuer Engine-Refactorings im Testlauf erzwingen
- Akzeptanz:
  - Gleicher Seed fuehrt reproduzierbar zu denselben Kernoutputs
  - Abweichungen werden als Regression sichtbar und diffbar
  - Kritische Gameplay-Pfade sind durch deterministische Tests abgedeckt
- Referenzen:
  - `app/Services/LiveMatchTickerService.php`
  - `tests/Feature/LiveMatchAdvancedRulesTest.php`
  - `tests/Feature/SimulateScheduledMatchesCommandTest.php`
  - `app/Models/GameMatch.php`

### 18) Betriebs-Hardening der Simulation
- Scope:
  - Last-/Parallel-Runner-Tests fuer `game:simulate-matches` (konkurrierende Worker, hohe Matchanzahl, Locking-Verhalten)
  - Crash-Recovery-Drills (Worker-Abbruch mitten im Lauf, Claim-Stale-Uebernahme, kontrollierte Fortsetzung)
  - Monitoring-/Alerting-Grundlage fuer Scheduler und Match-Claims (Kennzahlen, Fehlerquote, Stale-Claim-Haeufigkeit)
- Akzeptanz:
  - Simulationslaeufe bleiben unter Parallelitaet konsistent ohne Doppelverarbeitung
  - Haengende oder abgestuerzte Runner fuehren nicht zu dauerhaften Blockaden
  - Betriebskennzahlen machen Stau/Fehler fruehzeitig sichtbar und auswertbar
- Referenzen:
  - `app/Jobs/SimulateScheduledMatchesJob.php`
  - `routes/console.php`
  - `tests/Feature/SimulateScheduledMatchesCommandTest.php`
  - `app/Services/SimulationSettingsService.php`
