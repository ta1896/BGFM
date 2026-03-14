# CompetitionContextService – Code-Erklärung

`app/Services/CompetitionContextService.php`

Dieser Service ist die **einzige Quelle der Wahrheit** für den Wettbewerbskontext eines Spiels. Er bestimmt, ob ein Match zur Liga, einem nationalen Pokal, einem internationalen Pokal oder einem Freundschaftsspiel gehört.

## Die vier Kontexte

```php
CompetitionContextService::LEAGUE           = 'league'
CompetitionContextService::CUP_NATIONAL     = 'cup_national'
CompetitionContextService::CUP_INTERNATIONAL = 'cup_international'
CompetitionContextService::FRIENDLY         = 'friendly'
```

## Wie man es benutzt

```php
use App\Services\CompetitionContextService;
use App\Models\GameMatch;

$ctxService = app(CompetitionContextService::class);
$match = GameMatch::find($id);

// Kontext eines Spiels ermitteln
$context = $ctxService->forMatch($match);
// → 'league', 'cup_national', 'cup_international', oder 'friendly'

// Bequeme Boolean-Helfer
$ctxService->isLeague($match);           // true/false
$ctxService->isCup($match);              // national oder international
$ctxService->isNationalCup($match);      // nur nationaler Pokal
$ctxService->isInternationalCup($match); // nur internationaler Pokal
$ctxService->isFriendly($match);         // Freundschaftsspiel

// Kontext persistieren (schreibt 'competition_context' in die matches-Tabelle)
$ctxService->persistForMatch($match);
```

## Auflösungslogik (Prioritäten)

```
forMatch(GameMatch $match)
    │
    ├── 1. Prüfe $match->competition_context (gespeicherter Wert)
    │      → wenn gültig: sofort zurückgeben
    │
    ├── 2. Prüfe $match->type === 'league'   → 'league'
    │
    ├── 3. Prüfe $match->type === 'friendly' → 'friendly'
    │
    └── 4. Prüfe competition.scope
           ├── scope = 'international' → 'cup_international'
           ├── scope = 'national'      → 'cup_national'
           └── Fallback: country_id vorhanden?
                   ├── ja  → 'cup_national'
                   └── nein → 'cup_international'
```

## Warum dieser Service wichtig ist

Der Kontext steuert übergreifende Spielregeln:

```php
// Sperren sind kontextspezifisch – eine Liga-Sperre gilt nicht im Pokal:
if ($ctxService->isLeague($match)) {
    $player->suspension_league_games_remaining--;
}
if ($ctxService->isNationalCup($match)) {
    $player->suspension_cup_national_games_remaining--;
}

// Statistiken werden je Kontext separat aggregiert:
// player_league_competition_statistics, player_cup_national_competition_statistics, etc.
```

> [!IMPORTANT]
> Alle Services, die kontextabhängig arbeiten (Sperren, Statistiken, Finanz-Hooks, Cup-Progression), müssen `CompetitionContextService::forMatch()` verwenden und dürfen **nicht** direkt auf `$match->type` prüfen.
