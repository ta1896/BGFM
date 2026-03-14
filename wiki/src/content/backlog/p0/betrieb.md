# Betriebs-Hardening der Simulation

**Priorität:** P0 · **Status:** ✅ Erledigt (3 Teile)

## Scope

- Last-/Parallel-Runner-Tests für `game:simulate-matches`
- Crash-Recovery-Drills (Worker-Abbruch, Claim-Stale-Übernahme)
- Monitoring-/Alerting-Grundlage für Scheduler und Match-Claims

## Akzeptanz

- Simulationsläufe bleiben unter Parallelität konsistent ohne Doppelverarbeitung
- Hängende oder abgestürzte Runner führen nicht zu dauerhaften Blockaden
- Betriebskennzahlen machen Stau/Fehler frühzeitig sichtbar

## Umsetzung

### Teil 1 – Run-Monitoring + Last-/Parallel-Drills
- Persistente Scheduler-Run-Logs in `simulation_scheduler_runs` eingeführt.
- `game:simulate-matches` schreibt pro Lauf strukturierte Betriebsdaten.
- Health-Command `game:simulation-health` bereitgestellt.

### Teil 2 – Crash-Recovery + Alert-Schwellen
- Stale Scheduler-Runs mit Status `running` werden beim Start eines neuen Laufs automatisch als `abandoned` markiert.
- `game:simulation-health` um konfigurierbare Alert-Schwellen erweitert inkl. `--strict` Exit-Code.
- Konfigurationsblock `simulation.scheduler.health.*` eingeführt.

### Teil 3 – Geplanter Strict-Healthcheck + Alert-Logging
- Neuer Command `game:simulation-health-check` als operativer Wrapper eingeführt.
- Bei Alarmfall schreibt der Check strukturierte Error-Logs für externes Monitoring/Alerting.
- Optionales Success-Logging und geplanter Cron-Lauf (`everyFiveMinutes`).

## Referenzen

- `app/Jobs/SimulateScheduledMatchesJob.php`
- `tests/Feature/SimulateScheduledMatchesCommandTest.php`
- `app/Services/SimulationSettingsService.php`
