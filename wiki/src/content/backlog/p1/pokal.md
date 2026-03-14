# Pokal-Regelwerk vertiefen

**Priorität:** P1 · **Status:** ✅ Erledigt (3 Teile)

## Scope

- Erweiterte Gewinnerermittlung (Hin-/Rückspiel, Auswärtstor)
- Robuste Folgerundenbildung inkl. ungerader Fälle
- Optionale Finanz-/Achievement-Hooks

## Akzeptanz

- Cup-Progression bleibt stabil bei Sonderfällen
- End-to-end Tests für mehrere Cup-Runden

## Umsetzung

### Teil 1 – Folgerunden-Stabilität
- Cup-Folgerundenbildung robust bei ungerader Siegerzahl: automatisches Freilos-Match.
- Rundenbenennung: `Finale`, `Halbfinale`, `Viertelfinale`, `Achtelfinale`, sonst `Cup Runde X`.
- Tests: `tests/Feature/CupProgressionServiceTest.php`

### Teil 2 – Hin-/Rückspiel + Aggregate
- Optionale Hin-/Rückspiel-Generierung pro Cup-Runde (`simulation.cup.two_legged.*`).
- Gewinnerermittlung je Tie über Aggregate, optional Auswärtstore und Decider-/Penalty-Fallback.

### Teil 3 – Finanz-/Achievement-Hooks
- Idempotente Cup-Prämien für Rundenfortschritt und Titelgewinn über `cup_reward_logs`.
- Manager-Benachrichtigung für Pokal-Erfolge (`cup_achievement`).
- Konfigurierbar über `simulation.cup.rewards.*`.

## Referenzen

- `app/Services/LiveMatchTickerService.php`
- `tests/Feature/CupProgressionServiceTest.php`
