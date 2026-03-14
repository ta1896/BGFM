# Datenbankschema

OpenWS verwendet eine relationale Datenbank (MySQL / MariaDB). Hier ist eine Übersicht der zentralen Tabellen und ihrer Beziehungen.

## Kern-Entitäten

### `users`
| Spalte | Typ | Beschreibung |
|---|---|---|
| `id` | `bigint` | Primärschlüssel |
| `name` | `varchar` | Benutzername |
| `email` | `varchar` | E-Mail-Adresse (einzigartig) |
| `password` | `varchar` | Gehashtes Passwort |

### `clubs`
| Spalte | Typ | Beschreibung |
|---|---|---|
| `id` | `bigint` | Primärschlüssel |
| `name` | `varchar` | Vereinsname |
| `user_id` | `bigint` | FK → `users` (Manager) |
| `budget` | `decimal` | Vereinsbudget |
| `stadium_capacity` | `int` | Stadionkapazität |

### `players`
| Spalte | Typ | Beschreibung |
|---|---|---|
| `id` | `bigint` | Primärschlüssel |
| `club_id` | `bigint` | FK → `clubs` |
| `name` | `varchar` | Spielername |
| `overall` | `int` | Gesamtbewertung (1-99) |
| `position` | `varchar` | Position (z.B. `ST`, `CB`) |
| `condition` | `int` | Frische / Kondition (0-100) |

### `matches`
| Spalte | Typ | Beschreibung |
|---|---|---|
| `id` | `bigint` | Primärschlüssel |
| `home_club_id` | `bigint` | FK → `clubs` |
| `away_club_id` | `bigint` | FK → `clubs` |
| `home_score` | `int` | Tore Heimteam |
| `away_score` | `int` | Tore Auswärtsteam |
| `status` | `varchar` | `scheduled`, `live`, `finished` |
| `played_at` | `datetime` | Spieltermin |

## Beziehungsdiagramm (vereinfacht)

```
users ──< clubs ──< players
              │
              └──< matches (als home_club / away_club)
                       │
                       └──< match_actions (Ticker-Einträge)
```
