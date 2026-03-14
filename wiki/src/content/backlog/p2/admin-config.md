# Admin-Simulationskonfiguration

**Priorität:** P2 · **Status:** ✅ Erledigt (2 Teile)

## Scope

- Einstellbare Parameter für Intervall, Max-Matches, Positionsmalus, Live-Änderungslimits
- Optional Strategie-/Observer-Auswahl

## Akzeptanz

- Einstellungen sind persistiert und wirken im Lauf
- Guardrails für ungültige Werte

## Umsetzung

### Teil 1 – Persistente Runtime-Settings
- Persistente Tabelle `simulation_settings` mit Runtime-Override eingeführt.
- ACP-Dashboard um Admin-Form für Scheduler-Intervall/Limit/Typen, Claim-Timeout, Positionsfaktoren.
- `game:simulate-matches` nutzt persistierte Default-Werte und Intervall-Guard inkl. `--force`-Bypass.
- Tests: `tests/Feature/AdminSimulationSettingsTest.php`

### Teil 2 – Granulare Observer-Auswahl
- Post-Match-Observer im ACP einzeln schaltbar (Stats-Rebuild, Competition-Stats, Availability, Finance-Settlement).
- Observer-Pipeline respektiert Runtime-Settings in stabiler Reihenfolge.
- Tests: `tests/Feature/MatchFinishedObserverPipelineOrderTest.php`

## Referenzen

- `app/Services/SimulationSettingsService.php`
- `routes/console.php`
