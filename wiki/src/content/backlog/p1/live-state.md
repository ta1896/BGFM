# Live-State-Persistenz erweitern

**Priorität:** P1 · **Status:** ✅ Erledigt (2 Teile)

## Scope

- Zusatzzustände speichern (Ballführer, Set-Piece-Rollen, geplante Wechsel)
- Resume-Fähigkeit verbessern
- Historisierung wichtiger Simulationszustandswechsel

## Akzeptanz

- Match kann mitten im Lauf robust wiederaufgenommen werden
- Debugbare State-Transitions je Match

## Umsetzung

### Teil 1 – Kontextfelder + Transition-History
- Team-Live-State erweitert um persistente Kontextfelder (`current_ball_carrier_player_id`, letzter Set-Piece-Taker/-Typ/-Minute).
- Historisierung in `match_live_state_transitions` eingeführt.
- Tests: `tests/Feature/LiveStatePersistenceTest.php`

### Teil 2 – Minuten-Snapshots + Plan-Transitions
- Persistente Live-Minuten-Snapshots je Match (`match_live_minute_snapshots`) eingeführt.
- Geplante Wechsel werden als eigene State-Transitions historisiert.
- Live-State-API liefert letzte Snapshots im State-Payload.

## Referenzen

- `database/migrations/2026_02_13_032000_create_match_live_state_tables.php`
- `app/Models/MatchLivePlayerState.php`
- `app/Models/MatchLiveTeamState.php`
