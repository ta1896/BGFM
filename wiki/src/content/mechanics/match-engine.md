# Match Engine

Die **MatchSimulationService**-Pipeline ist das Herzstück von OpenWS. Sie berechnet Spielverläufe und generiert Live-Ticker-Events.

## Ablauf

```
AdminController::processMatchday()
        │
        ▼
ProcessMatchdayJob (Queue Job)
        │
        ▼
MatchSimulationService::simulate(Match)
        │
        ├── LineupRatingService       → Ermittelt Team-Stärken
        ├── TacticModifierService     → Wendet Taktik-Modifikatoren an
        └── ActionGeneratorService    → Generiert Minute-für-Minute Events
                │
                ├── Torchance / Tor
                ├── Gelbe / Rote Karte
                ├── Verletzung
                └── Generische Aktion
```

## Events (Ticker-Aktionen)

Jede Aktion wird in der Tabelle `match_actions` gespeichert:

| Feld | Typ | Beschreibung |
|---|---|---|
| `type` | `varchar` | `goal`, `card`, `injury`, `substitution`, `generic` |
| `minute` | `int` | Spielminute |
| `team` | `varchar` | `home` oder `away` |
| `player_id` | `bigint` | Hauptbeteiligter Spieler |
| `metadata` | `json` | Zusatzdaten (Kartenfarbe, Assistgeber etc.) |

## Live-Modus

Im Live-Modus verarbeitet der Server die Aktionen in Echtzeit (simulierter Zeitablauf). Das Frontend pollt alle paar Sekunden den aktuellen State:

```
GET /matches/{match}/live/state
→ Gibt alle bisherigen Actions und den aktuellen Spielstand zurück
```
