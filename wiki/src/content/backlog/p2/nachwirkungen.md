# Nachwirkungen nach Match vertiefen

**Priorität:** P2 · **Status:** ✅ Erledigt (2 Teile)

## Scope

- Einheitliche Regelmatrix für Verletzung/Sperre/Folgespiele
- Konsequenzen in Vertrags- und Benachrichtigungslogik anschließen

## Akzeptanz

- Verfügbarkeitszähler verhalten sich liga- und cup-konform
- Regressionstests für mehrteilige Matchserien

## Umsetzung

### Teil 1 – Regelmatrix + Notifications/Vertrag
- Verfügbarkeitsfolgen laufen über eine zentrale, konfigurierbare Regelmatrix in `simulation.aftermath.*`.
- Post-Match-Nachwirkungen werden zentral über `MatchAftermathService` ausgeführt.
- Vertragsfolgen: Bei ausfallenden Spielern mit nahem Vertragsende wird eine Vertragswarnung erzeugt.
- Tests: `tests/Feature/MatchAftermathConsequencesTest.php`

### Teil 2 – Gelbkarten-Staffelung + Saisonreset
- Kontextgetrennte Gelbkarten-Konten pro Spieler eingeführt.
- Konfigurierbare Gelb-Schwellen/Sperrdauer in `simulation.aftermath.yellow_cards.*`.
- Saisonwechsel setzt Gelbkarten-Konten optional zurück (`reset_on_season_rollover`).

## Referenzen

- `app/Services/LiveMatchTickerService.php`
- `tests/Feature/MatchAftermathConsequencesTest.php`
- `tests/Feature/SeasonProgressionTest.php`
