# Match-Processing idempotent machen

**Priorität:** P0 · **Status:** ✅ Erledigt

## Scope

- Idempotenzgrenzen für Start/Finish/Tabelle/Finanzbuchung definieren
- DB-seitige Guardrails (Unique Keys, Status-Gates, Verarbeitungsschlüssel)
- Retry-/Parallel-Run-sichere Verarbeitung etablieren

## Akzeptanz

- Ein Match erzeugt bei Mehrfachausführung keine doppelten Endeffekte
- Abrechnung und Tabellenupdate passieren pro Matchabschluss genau einmal
- Parallele Worker verursachen keine inkonsistenten Matchzustände

## Umsetzung

- Start/Finish mit DB-Lock + Status-Gate gegen Doppelwirkung bei Retry/Parallel-Run abgesichert.
- Post-Processing-Schritte über `match_processing_steps` (unique `match_id + step`) exakt-einmal-geschützt.
- Observer für Stats/Availability/Competition/Finance laufen idempotent.

## Referenzen

- `app/Services/LiveMatchTickerService.php`
- `app/Jobs/SimulateScheduledMatchesJob.php`
- `app/Services/LeagueTableService.php`
- `app/Services/FinanceCycleService.php`
