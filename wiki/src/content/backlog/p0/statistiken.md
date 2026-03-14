# Single Source of Truth – Statistiken

**Priorität:** P0 · **Status:** ✅ Erledigt

## Scope

- Zentrale Aggregationspipeline für Match → Saison → Karriere einziehen
- Lesesichten (Club, Liga, Spieler) nur noch aus definierten Aggregaten speisen
- Inkonsistenz-Checks und Rebuild-Jobs für Stat-Integrität bereitstellen

## Akzeptanz

- Dieselbe Kennzahl liefert in allen Sichten denselben Wert
- Rebuild erzeugt reproduzierbare Ergebnisse aus denselben Rohdaten
- Keine konkurrierenden Berechnungswege für dieselben Metriken

## Umsetzung

- Zentrale Aggregation in `StatisticsAggregationService` als gemeinsame Quelle.
- `LeagueTableService`, `PlayerCompetitionStatsService`, `ClubController` greifen auf dieselbe Aggregationslogik zu.
- Rebuild/Audit-Command `game:rebuild-statistics` für Reparaturläufe bereitgestellt.

## Referenzen

- `app/Models/MatchPlayerStat.php`
- `app/Models/SeasonClubStatistic.php`
- `app/Services/StatisticsAggregationService.php`
- `app/Services/LeagueTableService.php`
