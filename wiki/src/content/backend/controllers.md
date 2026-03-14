# Controller

Controller befinden sich in `app/Http/Controllers/` und verarbeiten eingehende HTTP-Requests. Sie geben in der Regel eine Inertia-Response zurück.

## Struktur eines typischen Controllers

```php
namespace App\Http\Controllers;

use App\Models\Match;
use Inertia\Inertia;
use Illuminate\Http\Request;

class MatchCenterController extends Controller
{
    /**
     * Gibt eine einzelne Match-Seite zurück.
     */
    public function show(Match $match)
    {
        // Daten laden
        $match->load(['homeClub', 'awayClub', 'actions']);

        // React-Seite mit Props rendern
        return Inertia::render('Matches/Show', [
            'match' => $match,
        ]);
    }
}
```

## Namensgebung & Zuständigkeiten

- **Resourceful Controllers** (z.B. `PlayerController`) folgen den 7 Standard-Methoden von Laravel: `index`, `create`, `store`, `show`, `edit`, `update`, `destroy`.
- **Feature-Controller** (z.B. `MatchCenterController`) bündeln zusammengehörige Aktionen, auch wenn sie kein vollständiges Resource sind.
- **Admin/** Unterordner enthält alle Controller, die ausschließlich für Administratoren zugänglich sind.

## Middleware

Routen werden über Middleware geschützt:

```php
// Nur für eingeloggte User mit Verein
Route::middleware(['auth', 'has.club.or.admin'])->group(function () {
    Route::get('/matches', [LeagueController::class, 'matches']);
});

// Nur für Admins
Route::middleware(['auth', 'admin'])->prefix('acp')->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index']);
});
```
