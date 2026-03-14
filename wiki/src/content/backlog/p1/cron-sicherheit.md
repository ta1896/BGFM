# Cron/Queue-Sicherheit

**Priorität:** P1 · **Status:** ✅ Erledigt (2 Teile)

## Scope

- Datenbankseitiges Locking pro Match (statt nur Scheduler-Overlap-Schutz)
- Sichere Fehlerbehandlung und Wiederanlauf
- Monitoring-Felder für Lastlauf/Retry

## Akzeptanz

- Keine Doppel-Simulation unter Parallelität
- Lasttests für konkurrierende Runner vorhanden

## Umsetzung

### Teil 1 – DB-Claiming + Recovery-Monitoring
- Simulations-Runner claimt Matches atomar per DB-Lock (`live_processing_token`, `live_processing_started_at`).
- Aktive Claims werden respektiert, stale Claims können übernommen werden.
- Monitoring-Felder für Laufhistorie/Fehler pro Match eingeführt.
- Fehlerpfad setzt Match kontrolliert auf `live_paused`.

### Teil 2 – Globaler Runner-Lock + Parallel-Run-Guard
- Globaler Scheduler-Runner-Lock über Cache-Lock (`simulation:scheduler:runner`) verhindert konkurrierende Läufe.
- Lock-TTL als Runtime-Setting steuerbar (`simulation.scheduler.runner_lock_seconds`).

## Referenzen

- `app/Jobs/SimulateScheduledMatchesJob.php`
- `routes/console.php`
- `tests/Feature/SimulateScheduledMatchesCommandTest.php`
