# Spieler-Datenmodell vertiefen

**Priorität:** P0 · **Status:** ✅ Erledigt

## Scope

- Positionsschema auf drei Rollen erweitern (`position_main`, `position_second`, `position_third`)
- Sperrzähler je Wettbewerb trennen (Liga, nationaler Pokal, internationaler Wettbewerb)
- Saison-/Karriere-Statistiken je Wettbewerb und je Metrik sauber trennen
- Tore und Vorlagen als strikt getrennte Kennzahlen durchziehen

## Akzeptanz

- Jeder Spieler kann Haupt-, Second- und Third-Position speichern/anzeigen
- Sperren greifen nur im jeweiligen Wettbewerb
- Tore und Vorlagen sind getrennt in UI, API und Aggregation sichtbar

## Umsetzung

- Kontextgetrennte Sperrzähler eingeführt.
- Saison- und Karriere-Player-Statistik je Wettbewerbskontext in `player_*_competition_statistics` eingeführt.
- Tore und Vorlagen bleiben getrennt von Match bis Aggregat.

## Referenzen

- `app/Models/Player.php`
- `app/Models/MatchPlayerStat.php`
- `app/Services/LiveMatchTickerService.php`
