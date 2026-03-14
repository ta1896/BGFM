# Ordnerstruktur

Eine Übersicht über die wichtigsten Verzeichnisse des Projekts.

```
NewGen/
├── app/
│   ├── Http/
│   │   ├── Controllers/        # Request-Handler (Inertia-Responses)
│   │   │   └── Admin/          # Admin-only Controller
│   │   └── Middleware/         # Auth, Club-Check Middleware
│   ├── Models/                 # Eloquent Models (DB-Entitäten)
│   └── Services/               # Business Logic
│       └── Match/              # Match-Engine Services
│
├── database/
│   ├── migrations/             # DB-Schema Definitionen
│   └── seeders/                # Test-Daten
│
├── resources/
│   ├── js/
│   │   ├── Components/         # Wiederverwendbare React-Komponenten
│   │   ├── Layouts/            # AuthenticatedLayout, GuestLayout
│   │   └── Pages/              # Inertia-Seiten (1:1 zu Routes)
│   │       ├── Dashboard.jsx
│   │       ├── Matches/
│   │       ├── Players/
│   │       └── ...
│   └── views/
│       └── app.blade.php       # Einziges Blade-Template (Shell)
│
├── routes/
│   ├── web.php                 # Alle Routen
│   └── auth.php                # Auth-Routen (Fortify)
│
└── wiki/                       # Diese Dokumentation (separates Projekt)
```

## Wichtige Konventionen

- **Seiten** liegen immer unter `resources/js/Pages/` und entsprechen direkt der Route.
- **Wiederverwendbare Komponenten** kommen in `resources/js/Components/`.
- **Business Logic** gehört in einen `Service` im `app/Services/` Verzeichnis, nicht direkt in den Controller.
