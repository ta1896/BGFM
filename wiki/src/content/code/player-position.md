# PlayerPositionService – Code-Erklärung

`app/Services/PlayerPositionService.php`

Dieser Service berechnet, wie gut ein Spieler auf einer bestimmten Position in der Aufstellung passt. Das Ergebnis ist ein **Fit-Faktor** (0.0 bis 1.0), der auf die Spielerstärke multipliziert wird.

## Das Positionsschema

Positionen werden in vier Gruppen zusammengefasst:

| Gruppe | Positionen |
|---|---|
| `GK` | TW, GK |
| `DEF` | LV, IV, RV, LWB, RWB |
| `MID` | LM, ZM, RM, DM, OM, LAM, ZOM, RAM |
| `FWD` | LS, MS, RS, ST, LW, RW, LF, RF, HS |

## Wie man es benutzt

```php
use App\Services\PlayerPositionService;

$service = app(PlayerPositionService::class);

// Gruppe einer Position ermitteln
$group = $service->groupFromPosition('ZM'); // → 'MID'
$group = $service->groupFromPosition('ST'); // → 'FWD'
$group = $service->groupFromPosition('TW'); // → 'GK'

// Einfacher Fit-Faktor (nur Hauptposition)
$factor = $service->fitFactor('ZM', 'DM');  // → 1.0 (beide MID)
$factor = $service->fitFactor('ZM', 'ST');  // → 0.76 (Fremdposition)

// Vollständiges 3-Positions-Profil (main/second/third)
$factor = $service->fitFactorWithProfile(
    positionMain:   'ZM',  // Hauptposition
    positionSecond: 'DM',  // Nebenposition
    positionThird:  null,  // Third-Position
    slot:           'OM'   // Zugewiesene Slot-Position
);
// → 1.0 (OM ist MID, genau wie ZM)
```

## Fit-Faktoren

Die Standardwerte kommen aus `config/simulation.php`, können aber im ACP überschrieben werden:

```php
// config/simulation.php
'position_fit' => [
    'main'       => 1.00,  // Spieler spielt auf Hauptposition  → volle Stärke
    'second'     => 0.92,  // Spieler spielt auf Nebenposition → -8%
    'third'      => 0.84,  // Spieler spielt auf Third-Position → -16%
    'foreign'    => 0.76,  // Völlig falsche Positionsgruppe    → -24%
    'foreign_gk' => 0.55,  // GK auf Feldposition (oder umgekehrt) → -45%
],
```

## Beispiel: Feldspieler als Torwart

```php
// Ein Stürmer (ST) spielt als Torwart (GK-Slot)
$factor = $service->fitFactorWithProfile('ST', null, null, 'TW');
// → 0.55 ('foreign_gk'), weil entweder Position oder Slot GK ist
```

> [!TIP]
> Der Fit-Faktor wird in der `teamStrength()`-Berechnung des `MatchSimulationService` automatisch angewendet. Es gibt keinen separaten Aufruf nötig – der Service ist als Dependency in die Simulation injiziert.
