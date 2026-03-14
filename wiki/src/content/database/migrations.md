# Migrations

Alle Datenbankmigrationen befinden sich im Verzeichnis `database/migrations/`. Laravel führt sie in chronologischer Reihenfolge aus.

## Wichtige Befehle

```bash
# Alle ausstehenden Migrationen ausführen
php artisan migrate

# Migrationen zurücksetzen und neu ausführen (+ Seeders)
php artisan migrate:fresh --seed

# Status der Migrationen anzeigen
php artisan migrate:status

# Neue Migration erstellen
php artisan make:migration create_example_table
```

## Konventionen

- **Namensgebung**: `create_<tablename>_table` für neue Tabellen, `add_<column>_to_<table>_table` für neue Spalten.
- **Reihenfolge**: Tabellen mit Fremdschlüsseln müssen nach den referenzierten Tabellen migriert werden.
- **Rollback**: Jede `up()`-Methode muss eine entsprechende `down()`-Methode haben, die die Änderung rückgängig macht.

## Seeders

Seeders in `database/seeders/` befüllen die Datenbank mit Testdaten.

```bash
# Alle Seeders ausführen
php artisan db:seed

# Spezifischen Seeder ausführen
php artisan db:seed --class=ClubSeeder
```
