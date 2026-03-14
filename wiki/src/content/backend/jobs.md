# Jobs & Queues

Laravel Jobs ermöglichen es, zeitintensive Prozesse – wie die Simulation eines kompletten Spieltags – asynchron im Hintergrund auszuführen.

## Wie Jobs funktionieren

1. Ein Job wird **dispatched** (in die Queue gelegt)
2. Ein **Queue Worker** läuft im Hintergrund und verarbeitet Jobs aus der Queue
3. Das Frontend kann den Status über Polling oder Broadcsting abfragen

```bash
# Queue Worker starten (muss dauerhaft laufen)
php artisan queue:work --tries=3
```

## Wichtige Jobs

### `ProcessMatchdayJob`
Wird vom Admin ausgelöst und simuliert alle Spiele eines Spieltags.

```php
// Dispatch aus dem AdminSimulationController
ProcessMatchdayJob::dispatch($competitionSeason)->onQueue('simulation');
```

### `ApplyRandomEventJob`
Verarbeitet zufällige Ereignisse (Verletzungen, Skandale, Transfers) für einzelne Vereine.

## Queue-Konfiguration

Die Queue-Verbindung wird in `.env` definiert:

```env
QUEUE_CONNECTION=database   # Empfohlen für Produktion
# oder
QUEUE_CONNECTION=sync       # Für lokale Entwicklung (synchron, kein Worker nötig)
```

> [!CAUTION]
> Mit `QUEUE_CONNECTION=sync` werden Jobs synchron ausgeführt und blockieren den Request. Nur für die lokale Entwicklung verwenden!
