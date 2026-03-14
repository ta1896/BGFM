# Layout & Navigation

Das Frontend-Layout ist in `resources/js/Layouts/` definiert.

## `AuthenticatedLayout.jsx`

Das Haupt-Layout für alle eingeloggten Seiten. Es besteht aus:

- **Sidebar** (links, fixiert): Enthält die Navigationsgruppen, Vereins-Selektor, und User-Footer.
- **Header** (oben, sticky): Zeigt den aktuellen Seitennamen und Notification-Bell.
- **Main Content Area** (rechts): Rendert die jeweilige Seite als `children`.

```jsx
// Verwendung auf einer Seite
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

export default function Dashboard() {
    return (
        <AuthenticatedLayout>
            {/* Seiteninhalt hier */}
        </AuthenticatedLayout>
    );
}
```

## Navigation (Sidebar)

Die Navigation ist in gruppierten Menüs organisiert (`Büro`, `Team`, `Wettbewerb`, `Markt`).

```jsx
// Struktur eines Menüeintrags
{ route: 'players.index', label: 'Kader', active: 'players.*', icon: Users }
```

- **`route`**: Name der Laravel-Route für den Link-href
- **`active`**: Muster zum Erkennen des aktiven Zustands (unterstützt `.*` Wildcard)
- **`icon`**: Phosphor Icons Komponente

## Mobile

Auf mobilen Geräten ist die Sidebar ausgeblendet und kann über ein Hamburger-Menü (oben links) geöffnet werden. Ein Backdrop-Overlay schließt sie beim Klick.
