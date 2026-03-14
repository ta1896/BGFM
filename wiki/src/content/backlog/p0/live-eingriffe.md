# Live-Eingriffe mit geplanten Wechseln

**Priorität:** P0 · **Status:** ✅ Erledigt

## Scope

- Wechselpläne mit Minute + Matchzustand (Führung/Remis/Rückstand)
- Intervall- und Grenzvalidierung für späte Änderungen
- UI für geplante Live-Aktionen

## Akzeptanz

- Geplante Wechsel werden zur passenden Minute verlässlich ausgeführt
- Unzulässige Änderungen werden sauber abgefangen

## Umsetzung

- Persistente Wechselpläne inkl. Minute/Bedingung, UI im Matchcenter, regelkonforme Ausführung implementiert.

## Referenzen

- `app/Http/Controllers/MatchCenterController.php`
- `resources/views/leagues/matchcenter.blade.php`
- `_reference_open_websoccer/websoccer/classes/actions/SaveMatchChangesController.class.php`
