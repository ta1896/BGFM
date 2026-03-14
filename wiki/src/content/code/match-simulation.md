# Simulations-Engine – Code-Erklärung

`app/Services/MatchSimulationService.php`

Der `MatchSimulationService` ist das Herzstück von OpenWS. Er berechnet den kompletten Spielverlauf inklusive Tore, Karten, Verletzungen und Wechsel.

## Wie man es benutzt

### 1. Direktsimulation (sofortiges Ergebnis)

```php
// In einem Controller:
use App\Services\MatchSimulationService;
use App\Models\GameMatch;

$service = app(MatchSimulationService::class);
$match = GameMatch::find($matchId);

// Simuliert das Spiel und persistiert alles (Ergebnis, Events, Stats)
$finishedMatch = $service->simulate($match);

echo "Ergebnis: {$finishedMatch->home_score}:{$finishedMatch->away_score}";
```

### 2. Nur Berechnung ohne DB-Schreiben (Sandbox / Lab)

```php
// Ideal für die "Tactics Lab" Funktion im Admin:
$result = $service->calculateSimulation($match, [
    'is_sandbox' => true,
    'force_home_formation' => '4-3-3',  // Optional: Aufstellung erzwingen
]);

// $result enthält:
$result['home_score'];    // Tore Heim
$result['away_score'];    // Tore Auswärts
$result['events'];        // Array mit allen Match-Events
$result['duration_ms'];   // Berechnungsdauer
```

## Ablauf innerhalb von `simulate()`

```
simulate(GameMatch $match)
    │
    ├── calculateSimulation()       → Kernlogik, gibt Array zurück
    │       ├── resolveLineup()     → Sucht aktive Aufstellung des Clubs
    │       ├── extractPlayers()    → Zieht 11 Starter aus der Lineup
    │       ├── teamStrength()      → Berechnet Teamstärke (Overall + Taktik)
    │       ├── rollGoals()         → Würfelt Toranzahl basierend auf Stärke-Diff
    │       └── buildGoalEvents()   → Erstellt Tor-Events mit Schütze + Assistent
    │
    └── DB-Transaction              → Persistiert Ergebnis + Events + Stats
```

## Teamstärken-Berechnung

```php
private function teamStrength(Collection $players, bool $isHome, ?Lineup $lineup): float
{
    $score = ($overall * 0.4)   // Overall ist wichtigstes Kriterium
           + ($attack * 0.2)    // Schussqualität
           + ($buildUp * 0.15)  // Passspiel
           + ($defense * 0.15)  // Verteidigung
           + ($condition * 0.1) // Frische/Moral
    ;

    // Taktische Modifikatoren werden angewendet (falls Lineup vorhanden)
    if ($lineup) {
        $mods = $this->tacticalManager->getTacticalModifiers($lineup);
        $score *= (($mods['attack'] + $mods['defense'] + $mods['possession']) / 3);
    }

    // Heimvorteil: +3.5 Punkte
    if ($isHome) {
        $score += 3.5;
    }

    return $score;
}
```

## Tore würfeln

```php
private function rollGoals(float $attackStrength, float $defenseStrength): int
{
    // Erwartete Tore basierend auf Stärke-Differenz
    $expected = 1.35 + (($attackStrength - $defenseStrength) / 28);
    $expected = max(0.2, min(4.2, $expected));

    // 6 unabhängige Würfelwürfe → jeder kann ein Tor sein
    $goals = 0;
    for ($i = 0; $i < 6; $i++) {
        if ((mt_rand(1, 1000) / 1000) < ($expected / 6)) {
            $goals++;
        }
    }

    return min(8, $goals); // Maximum 8 Tore
}
```

## Wichtige Hinweise

> [!TIP]
> Verwende `calculateSimulation()` mit `is_sandbox: true` im Tactics Lab. So werden keine Match-Daten in der Datenbank verändert.

> [!NOTE]
> Die Aufstellung wird automatisch aufgelöst: Zuerst wird nach einer matchspezifischen Lineup gesucht, dann nach der aktiven Lineup des Clubs, und falls keine vorhanden ist, werden die 11 besten Spieler nach `overall` genommen.
