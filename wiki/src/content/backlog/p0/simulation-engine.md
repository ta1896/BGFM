# Simulations-Engine modularisieren

**Priorität:** P0 · **Status:** ✅ Erledigt

## Scope

- Tick-Orchestrierung in eigenen Executor auslagern
- Aktionslogik in Strategy kapseln
- Nachgelagerte Effekte (Stats/Finanzen/Tabellen) als Observer-Pipeline

## Akzeptanz

- `LiveMatchTickerService` deutlich entlastet
- Integrationstests für Pipeline-Reihenfolge vorhanden

## Umsetzung

- `MatchSimulationExecutor`, `DefaultSimulationStrategy` und Observer-Pipeline produktiv eingebunden.
- Kernwahrscheinlichkeiten und Formeln zentral in `config/simulation.php`.

## Referenzen

- `app/Services/LiveMatchTickerService.php`
- `_reference_open_websoccer/websoccer/classes/MatchSimulationExecutor.class.php`
- `_reference_open_websoccer/websoccer/classes/Simulator.class.php`
