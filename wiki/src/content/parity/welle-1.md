# Welle 1 – Core Gameplay

**Status:** 🟡 In Progress (größtenteils umgesetzt)

Ziel dieser Welle ist die 1:1-Umsetzung der Kern-Spielmechaniken aus Open WebSoccer.

## Referenzmodule (OWS)

- `core` · `leagues` · `season` · `matches` · `simulation`
- `tables` · `statistics` · `formation` · `players` · `clubs`

## Umgesetzte Features

- ✅ Fixture-Generator vorhanden
- ✅ Match-Simulation + Matchcenter mit Events/Spielerstatistiken
- ✅ Tabellenberechnung vorhanden
- ✅ Dashboard um nächstes Spiel + Inbox erweitert
- ✅ Automatischer Spieltag-Runner (`game:process-matchday`)
- ✅ Saisonabschluss mit Auf-/Abstieg zwischen Ligastufen
- ✅ CPU-Teams setzen vor Simulation automatische Aufstellung/Taktik

## Offene Punkte

Siehe [Gap Backlog P0](/backlog/p0/simulation-engine) für ausstehende Verbesserungen an der Simulations-Engine, Match-Logik und Live-Eingriffen.
