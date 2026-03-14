# Inertia.js Setup

Inertia.js ist die Brücke zwischen dem Laravel-Backend und den React-Frontend-Seiten. Es ermöglicht eine SPA-ähnliche Navigation ohne eine separate REST-API bauen zu müssen.

## Wie es funktioniert

- **Server-Side**: Der Controller gibt statt einer klassischen Blade-View eine Inertia-Response zurück.
- **Client-Side**: Inertia fängt alle Link-Klicks ab, macht XHR-Requests im Hintergrund und tauscht nur die aktive Seite (React-Komponente) aus, ohne die Seite neu zu laden.

## Seite rendern (Backend)

```php
// In einem Laravel Controller
return Inertia::render('Players/Index', [
    'players' => PlayerResource::collection($players),
    'filters' => $request->only(['search', 'position']),
]);
```

## Seite empfangen (Frontend)

```jsx
// resources/js/Pages/Players/Index.jsx
export default function PlayersIndex({ players, filters }) {
    return (
        <AuthenticatedLayout>
            <PlayerList players={players.data} />
        </AuthenticatedLayout>
    );
}
```

## Globale Props (Shared Props)

Manche Daten werden für jede Seite benötigt (z.B. Auth-Status, aktiver Verein). Diese werden in `HandleInertiaRequests.php` als shared props definiert:

```php
public function share(Request $request): array {
    return [
        'auth' => ['user' => $request->user(), 'isAdmin' => ...],
        'activeClub' => ...,
        'flash' => ['status' => session('status')],
    ];
}
```

Sie stehen dann auf jeder Seite über `usePage().props` zur Verfügung.
