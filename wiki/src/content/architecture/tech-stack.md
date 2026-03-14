# Tech Stack

Eine vollständige Übersicht aller im Projekt eingesetzten Technologien und Bibliotheken.

## Backend

| Paket | Version | Verwendung |
|---|---|---|
| Laravel | 11.x | PHP Framework, Routing, ORM |
| PHP | 8.2+ | Laufzeitumgebung |
| Inertia.js (Server) | 1.x | Adapter für React-Seiten |
| Laravel Fortify | 1.x | Authentifizierungs-Backend |
| Laragear WebAuthn | 2.x | Passkey / FIDO2 Unterstützung |
| Laravel Queues | core | Hintergrund-Jobs (Simulation) |

## Frontend

| Paket | Version | Verwendung |
|---|---|---|
| React | 18.x | UI-Bibliothek |
| Inertia.js (Client) | 1.x | SPA-Adapter for Laravel |
| Tailwind CSS | 3.x | Utility-CSS |
| Framer Motion | 11.x | Animationen |
| @phosphor-icons/react | 2.x | Ikonensatz |
| Vite | 5.x | Build-Tool & Dev-Server |

## Entwicklung & Tooling

```bash
# Backend starten
php artisan serve

# Frontend-Assets kompilieren (Entwicklung mit HMR)
npm run dev

# Hintergrund-Worker (für Simulations-Jobs)
php artisan queue:work
```
