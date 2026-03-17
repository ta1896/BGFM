<div align="center">

# NewGen

### Browser football manager built with Laravel, React, MySQL, Redis, and Docker

NewGen is a modern web-based football management game focused on live match presentation, club building, squad development, finances, transfers, and long-term progression.

<p>
  <a href="#feature-snapshot"><strong>Features</strong></a> ·
  <a href="#quick-start"><strong>Quick Start</strong></a> ·
  <a href="#demo-accounts"><strong>Demo Accounts</strong></a> ·
  <a href="https://discord.gg/aSNkPYgHDJ"><strong>Discord</strong></a>
</p>

[![Laravel](https://img.shields.io/badge/Laravel-12-FF2D20?logo=laravel&logoColor=white)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?logo=php&logoColor=white)](https://php.net)
[![React](https://img.shields.io/badge/React-19-61DAFB?logo=react&logoColor=0b0f14)](https://react.dev)
[![Vite](https://img.shields.io/badge/Vite-7-646CFF?logo=vite&logoColor=white)](https://vitejs.dev)
[![MySQL](https://img.shields.io/badge/MySQL-8-4479A1?logo=mysql&logoColor=white)](https://mysql.com)
[![Redis](https://img.shields.io/badge/Redis-Queue%20%2F%20Cache-DC382D?logo=redis&logoColor=white)](https://redis.io)
[![Docker](https://img.shields.io/badge/Docker-Compose-2496ED?logo=docker&logoColor=white)](https://docker.com)

</div>

---

## Community

Join the Discord community to share feedback, discuss features, and follow development.

- Discord: https://discord.gg/aSNkPYgHDJ

---

## Why NewGen

NewGen aims to combine the long-term depth of a football manager with a stronger browser-first presentation layer:

- live match center with ticker, highlights, score hero, and timeline
- club, player, and lineup management in a modern UI
- finances, transfers, loans, contracts, and sponsor systems
- training, infrastructure, youth, and club progression systems
- CPU-driven clubs and automated matchday processing
- admin tooling for simulation tuning and data management

---

## Feature Snapshot

| Area | Highlights |
| --- | --- |
| Match Center | Live ticker, highlights tab, scorer summary, club/player links, minute display, team-colored events |
| Club Management | Club pages, squad structure, budgets, sponsor logic, stadium and infrastructure |
| Player Layer | Player profiles, squad status, club association, lineups, match participation |
| Simulation | Matchday runner, CPU decisions, season logic, configurable simulation settings |
| Admin / Ops | ACP, Horizon, Reverb, queue workers, scheduler, Docker-based local setup |

---

## At A Glance

| Built For | Core Strength |
| --- | --- |
| Browser-first football management | Live presentation, readable UI, long-term progression systems |

---

## Tech Stack

```text
Backend      Laravel 12, PHP 8.2+
Frontend     Inertia.js, React 19, Vite
Database     MySQL 8
State / Jobs Redis, Horizon, queues, scheduler
Realtime     Laravel Reverb
Infra        Docker Compose, Nginx
```

---

## Project Structure

```text
app/
  Http/Controllers/         Page controllers and actions
  Models/                   Domain models like Club, Player, Match
  Services/                 Simulation, finances, progression, support systems
modules/                    Additional feature modules
resources/js/               Inertia React frontend
resources/views/            Root Blade app shell
routes/                     Web and console routes
database/                   Migrations, factories, seeders
docker/                     Nginx config and Docker-related files
public/                     Public assets and built frontend files
docs/                       Product notes, plans, and concept documents
```

---

## Quick Start

Get the project running locally with Docker and Vite.

### Requirements

- Docker Desktop
- Node.js + npm

### 1. Configure environment

```bash
cp .env.example .env
```

### 2. Start the containers

```bash
docker compose up -d
```

### 3. Install frontend dependencies

```bash
npm install
```

### 4. Prepare the application

```bash
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --seed
```

### 5. Build the frontend

```bash
npm run build
```

Open the app:

```text
http://localhost
```

---

## Local Development

### Frontend dev server

```bash
npm run dev
```

### Useful Laravel commands

```bash
docker compose exec app php artisan migrate
docker compose exec app php artisan optimize:clear
docker compose exec app php artisan horizon:status
docker compose exec app php artisan reverb:start --debug
```

### Match / simulation commands

```bash
docker compose exec app php artisan game:process-matchday
docker compose exec app php artisan game:process-matchday --competition-season=1
docker compose exec app php artisan game:rebuild-statistics --all
docker compose exec app php artisan game:backfill-player-club-model --dry-run
```

### Tests

```bash
composer test
npm run test:run
npm run test:backend
```

---

## Demo Accounts

| Role | Email | Password |
| --- | --- | --- |
| User | `test.manager@openws.local` | `password` |
| Admin | `test.admin@openws.local` | `password` |

---

## Important Routes

| Area | Route |
| --- | --- |
| Dashboard | `/dashboard` |
| Matches | `/matches` |
| Match Center | `/matches/{id}` |
| Players | `/players` |
| Clubs | `/clubs/{id}` |
| Lineups | `/lineups` |
| Admin Control Panel | `/acp` |
| Horizon | `/horizon` |

---

## Deployment Notes

The project is already structured for Docker-based deployment with:

- app
- nginx
- mysql
- redis
- scheduler
- queue / horizon
- reverb

Before deploying, verify:

- production `.env` values
- queue and scheduler processes
- websocket / Reverb setup
- public asset delivery from `/storage/...`
- frontend build output in `public/build`

---

## Environment Notes

Typical local values:

```env
APP_URL=http://localhost
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=sail
DB_PASSWORD=password
FILESYSTEM_DISK=local
```

If you work with uploaded public assets, make sure your environment serves `/storage/...` correctly.

---

## Contributing

When changing gameplay systems, prefer updating both sides of the feature:

- backend logic and data flow
- frontend presentation and user interaction

For larger feature work, keep supporting notes in [`docs/`](docs/).

---

<div align="center">

Built for a stronger browser-first football management experience.

</div>
