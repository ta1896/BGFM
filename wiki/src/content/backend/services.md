# Services

Services kapseln komplexe Business-Logik, die nicht direkt in Controller gehört. Sie liegen in `app/Services/`.

## Wichtige Services

### `MatchSimulationService`
Der Kern des Spielsystems. Berechnet den Verlauf und das Ergebnis eines Spiels.

```php
// Vereinfachtes Beispiel
class MatchSimulationService
{
    public function simulate(Match $match): array
    {
        $homeLineup = $match->homeLineup;
        $awayLineup = $match->awayLineup;

        // Übergibt an die Moment-by-Moment Engine
        $actions = $this->engine->run($homeLineup, $awayLineup);

        return $actions; // Array von Ticker-Events
    }
}
```

### `SimulationSettingsService`
Liest globale Einstellungen für die Simulation aus der Datenbank (z.B. Tor-Wahrscheinlichkeiten, Karten-Risiken).

```php
$settings = app(SimulationSettingsService::class)->get();
$goalProbability = $settings->goal_probability; // z.B. 0.12
```

### `PlayerDevelopmentService`
Berechnet Spielerwachstum nach Trainingseinheiten und Spielen.

## Konventionen

- Services werden via **Dependency Injection** in Controller übergeben oder per `app(ServiceClass::class)` aufgerufen.
- Services sollten **zustandslos** sein (keine Daten zwischen Aufrufen speichern).
- Komplexe Logik sollte in **Sub-Services** aufgeteilt werden (z.B. `Match/ActionGeneratorService`, `Match/LineupRatingService`).
