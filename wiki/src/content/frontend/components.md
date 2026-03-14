# Komponenten

Wiederverwendbare React-Komponenten liegen in `resources/js/Components/`. Seiten-spezifische Unterkomponenten können auch direkt neben der Seite in einem Unterordner liegen.

## Design-System

Alle UI-Elemente verwenden **Tailwind CSS** mit einem konsistenten Dark-Theme:

| Token | Farbe | Verwendung |
|---|---|---|
| `bg-[#0f172a]` | Dunkelblau | Haupthintergrund |
| `bg-slate-900` | Sidebar, Cards | Sekundärer Hintergrund |
| `bg-slate-800` | Input, Hover | Tertiärer Hintergrund |
| `text-cyan-400` | Akzent / Aktiv | Hervorhebungen, aktive Links |
| `border-slate-800` | `0.5` Opacity | Trennlinien, Card-Rahmen |

## Animationen

Das Projekt nutzt **Framer Motion** für Animationen:

```jsx
import { motion, AnimatePresence } from 'framer-motion';

// Einfaches Einblenden
<motion.div
    initial={{ opacity: 0, y: -10 }}
    animate={{ opacity: 1, y: 0 }}
    exit={{ opacity: 0, y: 10 }}
>
    ...
</motion.div>
```

## Icons

Icons stammen aus der **Phosphor Icons** Bibliothek:

```jsx
import { Users, Trophy, Calendar } from '@phosphor-icons/react';

<Users size={20} weight="bold" className="text-cyan-400" />
```

## Konventionen

- Komponenten nutzen **keine** direkt definierten `style` Props für zentrale Design-Entscheidungen, sondern Tailwind-Klassen.
- Klassen werden bei bedingtem Styling via Template-Literals zusammengesetzt: `` `${isActive ? 'text-white' : 'text-slate-400'}` ``
