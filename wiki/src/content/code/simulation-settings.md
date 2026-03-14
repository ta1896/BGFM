# SimulationSettingsService – Code-Erklärung

`app/Services/SimulationSettingsService.php`

Dieser Service verwaltet alle zur Laufzeit konfigurierbaren Parameter der Simulations-Engine. Einstellungen werden in der Tabelle `simulation_settings` persistiert und gecacht.

## Wie man es benutzt

### Einen Wert lesen

```php
use App\Services\SimulationSettingsService;

$settings = app(SimulationSettingsService::class);

// Einzelnen Wert lesen (mit Fallback)
$limit = $settings->schedulerDefaultLimit(); // z.B. 0 = kein Limit

// Oder per generischem Getter
$value = $settings->get('simulation.scheduler.interval_minutes', 1);
```

### Einen Wert setzen

```php
$settings->set('simulation.scheduler.interval_minutes', 5);
// → Wird in DB persistiert und sofort als Config-Override aktiv
```

### Alle Admin-Settings auf einmal aktualisieren (z.B. aus einem Admin-Formular)

```php
// In AdminGeneralSimulationSettingsController:
$settings->updateFromAdminPayload($request->validated());
```

## Konfigurierbare Schlüssel

| Schlüssel | Typ | Standard | Bedeutung |
|---|---|---|---|
| `simulation.scheduler.interval_minutes` | `int` | `1` | Mindestabstand zwischen Sim-Läufen |
| `simulation.scheduler.default_limit` | `int` | `0` | Max. Spiele pro Lauf (0 = unbegrenzt) |
| `simulation.position_fit.main` | `float` | `1.00` | Stärke-Faktor auf Hauptposition |
| `simulation.position_fit.second` | `float` | `0.92` | Stärke-Faktor auf Nebenposition |
| `simulation.position_fit.third` | `float` | `0.84` | Stärke-Faktor auf Third-Position |
| `simulation.position_fit.foreign` | `float` | `0.76` | Stärke-Faktor auf Fremdposition |
| `simulation.position_fit.foreign_gk` | `float` | `0.55` | Stärke-Faktor GK auf Fremdpos. |
| `simulation.live_changes.planned_substitutions.max_per_club` | `int` | `5` | Max. geplante Wechsel pro Verein |
| `simulation.observers.match_finished.enabled` | `bool` | `true` | Observer-Pipeline an/aus |
| `simulation.observers.match_finished.settle_match_finance` | `bool` | `true` | Finanzabrechnung nach Match |

## Wie Caching funktioniert

Die Settings werden nur bei Änderung aus der DB gelesen; sonst kommt der Wert aus dem Laravel-Cache:

```php
// Intern: Cache-Key ist 'simulation_settings.runtime_overrides'
$settings = Cache::rememberForever(self::CACHE_KEY, fn() =>
    $this->fetchSettingsFromDatabase()
);

// Nach einem set() oder persistMany() wird der Cache invalidiert:
Cache::forget(self::CACHE_KEY);
```

> [!CAUTION]
> Ändere Simulationsparameter niemals direkt in der DB, ohne danach den Cache zu leeren (`php artisan cache:clear`), da sonst veraltete Werte aktiv bleiben.
