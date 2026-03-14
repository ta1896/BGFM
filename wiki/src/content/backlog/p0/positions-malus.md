# Positions-Malus differenziert

**Priorität:** P0 · **Status:** ✅ Erledigt

## Scope

- Unterscheidung Hauptposition, Nebenposition, Fremdposition
- Maluswerte konfigurierbar machen
- Live- und Stat-Bewertung konsistent auf denselben Faktor stützen

## Akzeptanz

- Positionseffekt im Matchcenter nachvollziehbar
- Tests decken alle Positionsklassen ab

## Umsetzung

- `position_main/second/third`-Profil und konsistente Fit-Faktoren in Live/Team/Stats/UI eingebunden.
- Kontextgetrennte Sperrzähler (`league/cup_national/cup_international/friendly`) eingeführt.

## Referenzen

- `app/Services/PlayerPositionService.php`
- `app/Services/LiveMatchTickerService.php`
