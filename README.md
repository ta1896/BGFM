# OpenWS Laravell (Laravel + Docker + MySQL)

Basis-Architektur fuer eine Fussball-Simulation mit responsivem Dashboard im dunklen UI-Stil.

## Enthaltene Grundmodule

- Login / Register (Laravel Breeze)
- Vereine (CRUD)
- Spieler (CRUD)
- Aufstellung (CRUD + aktive Aufstellung + max. 11 Spieler)
- Basis-Berechnung (Teamstaerke-Service mit Angriff/Mittelfeld/Verteidigung/Chemie)
- Rollenmodell (`admin` / normaler User) mit ACP
- Liga-Engine: Spielplan, Spieltage, Tabelle
- Matchcenter: Simulation, Match-Events, Spielerbewertungen
- Transfermarkt: Listings, Gebote, Annahme/Abschluss
- Leihmarkt: Leihlistings, Leihgebote, Leihabschluss, automatische Leih-Rueckkehr
- Kaufoption bei Leihen: Option ziehen oder ablehnen
- Vertragsmanagement: Verlaengerungen mit neuen Vertragskonditionen
- Transferfenster-Regeln (Sommer/Winter, per ENV steuerbar)
- Sponsoren: Angebote, Vertragsabschluss, Bonus + laufende Zahlungen
- Stadion & Stadionumfeld: Kapazitaet, Infrastrukturprojekte, Upgrade-Fortschritt
- Trainingslager: Planung, Kosten, automatische Effekte im Spieltagsprozess
- Training: Sessions planen und Effekte anwenden
- Benachrichtigungen + Finanzbuchungen
- Automatischer Spieltag-Runner + Saisonabschluss (Auf-/Abstieg zwischen Ligen)
- CPU-Teams mit automatischer Aufstellung/Taktik vor Simulation

## Tech Stack

- Laravel 12
- PHP 8.5 Runtime via Laravel Sail
- MySQL 8.4
- Blade + Tailwind + Vite
- Docker Compose (`compose.yaml`)

## Architektur (Kurz)

- `app/Models/Club.php`: Verein, Budget, Reputation, Fan-Stimmung
- `app/Models/Player.php`: Kaderdaten + Leistungswerte
- `app/Models/Lineup.php`: Formation + aktive Aufstellung
- `app/Services/TeamStrengthCalculator.php`: Basis-Berechnung fuer Teamwerte
- `app/Services/SeasonProgressionService.php`: Spieltag-Lauf, Saisonabschluss, Auf-/Abstieg
- `app/Services/CpuClubDecisionService.php`: CPU-Aufstellungen und Taktik
- `app/Services/FinanceCycleService.php`: Matchday-Finanzabrechnung (Einnahmen/Ausgaben)
- `app/Services/SponsorService.php`: Sponsorangebote und Vertragslogik
- `app/Services/StadiumService.php`: Stadion-Initialisierung und Projekt-Upgrades
- `app/Services/TrainingCampService.php`: Trainingslager-Planung und Effektverarbeitung
- `app/Http/Controllers/*Controller.php`: Dashboard + CRUD fuer Module
- `resources/views/*`: Startseite, Auth, Dashboard, Modul-Views (responsive)

## Datenmodell

- `users` 1:n `clubs`
- `clubs` 1:n `players`
- `clubs` 1:n `lineups`
- `lineups` n:m `players` via `lineup_player`

## Optimierte DB-Struktur (an Open WebSoccer angelehnt)

Zusatztabellen fuer skalierbare Simulation:

- `countries`, `competitions`, `seasons`, `competition_seasons`
- `season_club_registrations`, `season_club_statistics`
- `matches`, `match_events`, `match_player_stats`
- `player_contracts`
- `transfer_listings`, `transfer_bids`
- `club_financial_transactions`
- `sponsors`, `sponsor_contracts`
- `stadiums`, `stadium_projects`
- `training_camps`
- `match_financial_settlements`
- `training_sessions`, `training_session_player`
- `game_notifications`

Erweiterte Bestandstabellen:

- `clubs`: `league_id`, `slug`, `fanbase`, `board_confidence`, `training_level`
- `players`: `preferred_foot`, `potential`, `status`, `contract_expires_on`, `last_training_at`
- `lineups`: `match_id`, `is_template`, `tactical_style`
- `lineup_player`: `is_captain`, `is_set_piece_taker`

## Starten mit Docker

1. Container starten:

```bash
./vendor/bin/sail up -d
```

2. Migrationen + Seed-Daten ausfuehren:

```bash
./vendor/bin/sail artisan migrate:fresh --seed
```

3. Frontend-Build:

```bash
npm install
npm run build
```

4. App oeffnen:

- http://localhost

## Demo-Login (Seed)

- E-Mail: `test@example.com`
- Passwort: `password`

### Admin-Login (Seed)

- E-Mail: `admin@example.com`
- Passwort: `password`

## Wichtige Routen

- `/` Startseite
- `/login`, `/register`
- `/dashboard`
- `/clubs`
- `/players`
- `/lineups`
- `/matches`, `/matches/{id}` (Matchcenter)
- `/table`
- `/transfers`
- `/loans`
- `/contracts`
- `/training`
- `/sponsors`
- `/stadium`
- `/training-camps`
- `/notifications`
- `/finances`
- `/acp` (nur Admin)

### Automatischer Spieltag (CLI)

```bash
docker compose -f compose.yaml exec -T laravel.test php artisan game:process-matchday
```

Optional fuer eine konkrete Liga-Saison:

```bash
docker compose -f compose.yaml exec -T laravel.test php artisan game:process-matchday --competition-season=1
```

## Transferfenster steuern

- ENV: `TRANSFER_WINDOW_ENFORCED=true|false`
- Fensterdefinition: `config/transfer.php`

## MySQL Zugriff

- Host (lokal): `127.0.0.1`
- Port: `3306`
- Datenbank: `laravel`
- User: `sail`
- Passwort: `password`

Beispiel via CLI:

```bash
docker compose -f compose.yaml exec mysql mysql -usail -ppassword laravel
```
