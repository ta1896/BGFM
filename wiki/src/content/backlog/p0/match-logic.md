# Match-Logik verfeinern

**Priorität:** P0 · **Status:** ✅ Erledigt

## Scope

- Wahrscheinlichkeiten für Pass/Tackle/Foul/Freistoß/Ecke/Elfer zentralisieren
- Konfigurierbare Balancewerte in `config/`
- Eindeutige Ereignisketten je Sequenz

## Akzeptanz

- Regelparameter ohne Codeänderung anpassbar
- Deterministische Tests für Kernpfade

## Umsetzung

- Kernwahrscheinlichkeiten und Formeln zentral in `config/simulation.php` konfigurierbar gemacht.

## Referenzen

- `app/Services/LiveMatchTickerService.php`
- `_reference_open_websoccer/websoccer/classes/DefaultSimulationStrategy.class.php`
