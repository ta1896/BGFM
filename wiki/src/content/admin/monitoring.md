# Monitoring & Debug

Das **Monitoring & Debug Center** ist ein internes Admin-Tool unter `/acp/monitoring` zur Überwachung und Fehlerbehebung des Systems.

## Bereiche

### Logs (`/acp/monitoring/logs`)
Zeigt die Laravel-Applikationslogs. Nützlich zur Fehlerdiagnose.

```bash
# Logs manuell leeren (auch über die UI möglich)
php artisan log:clear
```

### Analysis (`/acp/monitoring/analysis`)
Analysiert Performance-Metriken, z.B. langsame Datenbankabfragen oder Memory-Verbrauch.

### Lab (`/acp/monitoring/lab`)
Ermöglicht das Durchführen von Test-Simulationen mit konfigurierbaren Parametern, ohne dass echte Spieltage beeinflusst werden.

### Internals (`/acp/monitoring/internals`)
Zeigt interne Config-Werte, Queue-Status und Cache-Informationen.

### Scheduler (`/acp/monitoring/scheduler`)
Übersicht über geplante Tasks (Laravel Scheduler), z.B. automatische Erstellung zufälliger Events.

## Reparatur-Funktionen

| Aktion | Endpoint | Beschreibung |
|---|---|---|
| Cache leeren | `POST /acp/monitoring/clear-cache` | Leert den Laravel-Cache |
| Reparieren | `POST /acp/monitoring/repair` | Korrigiert inkonsistente DB-Zustände |
