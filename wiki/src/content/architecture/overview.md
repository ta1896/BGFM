# Architektur-Übersicht

OpenWS ist ein Full-Stack-Webanwendung, die nach dem klassischen MVC-Muster aufgebaut ist und durch moderne Reaktivität mit Inertia.js erweitert wird.

## Grundprinzip

Das System folgt dem **Laravel + Inertia.js + React** Pattern (auch "Laravel Stack" oder "LIRA" genannt):

```
Browser (React SPA)
       ↕ HTTP (Inertia Protocol)
Laravel (Backend Controller)
       ↕ Eloquent ORM
MySQL Datenbank
```

Durch Inertia.js fungiert das Laravel-Backend als vollwertiger Server-Side-Renderer ohne eigene API-Schicht. React-Komponenten erhalten ihre Daten als Props direkt vom Controller.

## Hauptkomponenten

| Schicht | Technologie | Zweck |
|---|---|---|
| Frontend | React 18 + Inertia.js | UI, Seiten, State |
| Styling | Tailwind CSS | Utility-first CSS Framework |
| Backend | Laravel 11 | Routing, Business Logic |
| Datenbank | MySQL / MariaDB | Datenpersistenz |
| Auth | Laravel Fortify + WebAuthn | Passkey-Authentifizierung |
| Assets | Vite | Bundling, HMR |

## Datenfluß (typische Anfrage)

1. Browser navigiert zu `/matches`
2. Laravel routet zu `LeagueController@matches`
3. Controller lädt Daten via Eloquent (z.B. `Match::with('homeClub', 'awayClub')->get()`)
4. Controller gibt `inertia('Matches/Index', $data)` zurück
5. Inertia.js rendert die React-Seite `Pages/Matches/Index.jsx` mit den Daten als Props
6. Weitere Navigationen laufen als XHR-Requests (kein Full-Page-Reload)
