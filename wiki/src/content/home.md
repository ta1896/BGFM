# OpenWS Wiki – Projektübersicht

**OpenWS / NewGen** ist eine Laravel-basierte Reimplementierung von [Open WebSoccer](https://github.com/ihofmann/open-websoccer), dem bekannten Open-Source Fußball-Manager. Ziel ist vollständige Funktionsparität – jedoch ohne Premium-/Payment-Funktionen – auf einem modernen Stack (Laravel + Inertia.js + React + Tailwind).

## Zieldefinition

> Funktionsparität zu Open WebSoccer (nicht nur UI), umgesetzt auf Laravel + Tailwind + MySQL, ohne Premium/Payment-Umfang.

## Aktueller Stand

| Bereich | Status |
|---|---|
| Auth + Rollen + ACP | ✅ Vorhanden |
| Core-Tabellen (Liga/Saison/Spiel/Transfer/Training/Finanzen) | ✅ Vorhanden |
| Vereine / Spieler / Aufstellung | ✅ Vorhanden |
| Welle 1 – Core Gameplay | 🟡 In Progress |
| Welle 2 – Team Ops | ✅ Abgeschlossen |
| Welle 3 – Erweiterte Systeme | 🟡 In Progress |
| Welle 4 – Community | ⬜ Ausstehend |
| Welle 5 – Plattform | ⬜ Ausstehend |

## Technische Leitlinien

- Business-Regeln aus OWS werden fachlich übernommen, aber Laravel-konform modelliert.
- **Keine 1:1 Portierung** von altem PHP-Code; stattdessen saubere Service-/Domain-Schicht.
- Jede Welle endet mit: Migrationen + Seed-Daten, Feature-Tests, Bedienbarer UI, ACP-Management.

## Navigation

- [Parity Plan – Welle 1](/parity/welle-1)
- [Gap Backlog P0](/backlog/p0/simulation-engine)
- [Umsetzungsstand P0](/status/p0)
