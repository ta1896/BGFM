# Wettbewerbskontext als Core-Domain

**Priorität:** P0 · **Status:** ✅ Erledigt

## Scope

- Regelkontext vereinheitlichen: `league`, `cup_national`, `cup_international`, `friendly`
- Kontext zentral für Sperren, Stats, Qualifikation und Matcherzeugung nutzbar machen
- Regelabfragen aus Einzelservices in ein gemeinsames Domain-Modell verlagern

## Akzeptanz

- Jeder Matchlauf besitzt einen eindeutigen Wettbewerbskontext
- Sperren/Statistiken werden konsequent kontextbezogen verarbeitet
- Qualifikation für Wettbewerbe folgt einheitlichen, testbaren Regeln

## Umsetzung

- Match-Ebene um `competition_context` erweitert (`league`, `cup_national`, `cup_international`, `friendly`) inkl. Backfill.
- Zentraler Resolver `CompetitionContextService` als Regelquelle eingeführt.
- Kernservices (LiveTicker, Competition-Observer, Cup-Progression, Fixture/Friendly-Erzeugung) auf den zentralen Kontext ausgerichtet.

## Referenzen

- `app/Models/Competition.php`
- `app/Models/CompetitionSeason.php`
- `app/Models/GameMatch.php`
- `app/Services/LiveMatchTickerService.php`
