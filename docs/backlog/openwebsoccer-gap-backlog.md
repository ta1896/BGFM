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
| P1 | Pokal-Regelwerk vertiefen | Hin-/Rueckspiel, Auswaertstor/Turnierpfade, robustere Folgerundenlogik |
| P1 | Vereins-Datenmodell vertiefen | Mehr Vereinsdetails (Saisonziel/Historie/Rollen) und klare Saison-vs.-Gesamttrennung |
| P1 | Pokal-Wettbewerbe trennen (national/international) | Nationale und internationale Cup-Wettbewerbe mit separaten Regeln, Qualifikation und Auswertungen |
| P1 | Live-State-Persistenz erweitern | Mehr Simulationstate fuer sauberes Resume und Debugging |
| P1 | Cron/Queue-Sicherheit fuer Simulation | Keine Doppelberechnung bei Parallelitaet, bessere Fehlerrecovery |
| P2 | Admin-Simulationskonfiguration | Intervall, Limits, Regelparameter, Strategie/Observer steuerbar |
| P2 | Nachwirkungen nach Match vertiefen | Sperren/Verletzungen/Vertragsfolgen einheitlich und testbar |

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
