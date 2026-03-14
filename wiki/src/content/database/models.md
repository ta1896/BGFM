# Eloquent Models

Alle Datenbankentitäten sind als Eloquent Models in `app/Models/` definiert.

## Wichtige Models

### `Club`
```php
// app/Models/Club.php
class Club extends Model {
    public function user(): BelongsTo { ... }
    public function players(): HasMany { ... }
    public function homeMatches(): HasMany { ... }
    public function awayMatches(): HasMany { ... }
    public function activeLineup(): HasOne { ... }
}
```

### `Player`
```php
// app/Models/Player.php
class Player extends Model {
    protected $casts = [
        'attributes' => 'array', // JSON-Spalte mit Einzelwerten
    ];
    public function club(): BelongsTo { ... }
    public function contracts(): HasMany { ... }
}
```

### `Match` (komplex)
Das `Match`-Model enthält die Ergebnisse und verknüpft die beiden Clubs sowie alle zugehörigen Aktionen (Ticker).

```php
// app/Models/Match.php
class Match extends Model {
    public function homeClub(): BelongsTo { ... }
    public function awayClub(): BelongsTo { ... }
    public function actions(): HasMany { ... } // MatchAction
    public function homeLineup(): ?Lineup { ... }
    public function awayLineup(): ?Lineup { ... }
}
```

## Konventionen

- **Casting**: Komplexe Daten (z.B. Spielerattribute) werden als JSON in der DB gespeichert und via `$casts` automatisch als Array/Objekt geladen.
- **Scopes**: Häufige Abfragefilter werden als lokale Scopes definiert (z.B. `scopeFinished()`, `scopeLive()`).
- **Accessors / Mutators**: Berechnete Felder (z.B. `getOverallAttribute()`) sind als Accessors definiert, nicht als echte DB-Spalten.
