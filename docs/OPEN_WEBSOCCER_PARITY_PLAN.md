# Open WebSoccer -> Laravel/Tailwind Parity Plan

Ziel: Funktionsparitaet zu Open WebSoccer (nicht nur UI), umgesetzt auf Laravel + Tailwind + MySQL, ohne Premium/Payment-Umfang.

## Aktueller Stand

- Auth + Rollen + ACP vorhanden
- Core-Tabellen fuer Liga/Saison/Spiel/Transfer/Training/Finanzen vorhanden
- Vereine/Spieler/Aufstellung-Basismodule vorhanden

## Gesamtumfang (Referenzmodule)

Quelle: `_reference_open_websoccer/websoccer/modules`

### Core Gameplay

- `core`
- `leagues`
- `season`
- `matches`
- `simulation`
- `tables`
- `statistics`
- `formation`
- `players`
- `clubs`

### Markt/Teammanagement

- `transfermarket`
- `transferoffers`
- `transfers`
- `lending`
- `training`
- `trainingcamp`
- `sponsor`
- `stadium`
- `stadiumenvironment`
- `youth`
- `nationalteams`
- `teamofday`

### User/Sozial/Kommunikation

- `users`
- `profile`
- `messages`
- `notifications`
- `news`
- `shoutbox`
- `actionlogs`
- `moneytransactions`
- `userabsence`
- `usersonline`
- `userbadges`
- `halloffame`

### Plattform/Erweiterungen

- `frontend`
- `frontendads`
- `office`
- `help`
- `rss`
- `languageswitcher`
- `socialrecommendations`
- `termsandconditions`
- `generator`
- `webjobexecution`
- `clubslogo`
- `clubsrename`
- `freeclubs`
- `cancellation`
- `playerssearch`
- `alltimetable`
- `randomevents`
- `fireplayer`
- `firemanagers`

### SSO/Auth (im Scope)

- `joomlalogin`
- `formauthentication`
- `userauthentication`
- `userregistration`

### Nicht im Scope (bewusst ausgeschlossen)

- `premium`
- `premiumpaypal`
- `premiummicropayment`
- `premiumsofortcom`
- `facebook` (Social Login)
- `googleplus` (Social Login)
- `wordpresslogin` (Social Login)
- `gravatar` (Social Profile)

## Umsetzungswellen

### Welle 1 (Core 1:1)

- Liga- und Saison-Engine
- Spielplan/Spieltag/Live-Simulation + Matchreport
- Tabelle/Statistik/Office-Dashboard

Status: `in progress` (grosstenteils umgesetzt)
- Fixture-Generator vorhanden
- Match-Simulation + Matchcenter mit Events/Spielerstatistiken vorhanden
- Tabellenberechnung vorhanden
- Dashboard um naechstes Spiel + Inbox erweitert
- Automatischer Spieltag-Runner (`game:process-matchday`) vorhanden
- Saisonabschluss mit Auf-/Abstieg zwischen Ligastufen vorhanden
- CPU-Teams setzen vor Simulation automatische Aufstellung/Taktik

### Welle 2 (Team Ops)

- Transfers + Angebote + Leihe
- Training + Trainingslager
- Sponsor + Stadion + Stadionumfeld

Status: `done` (inkl. Optimierungen)
- Transfermarkt (Listing/Gebot/Annahme) vorhanden
- Trainingseinheiten (Planung + Effekte anwenden) vorhanden
- Leihmarkt (Listing/Gebot/Annahme + Leihrueckkehr) vorhanden
- Leih-Kaufoption (ziehen/ablehnen) vorhanden
- Vertragsverlaengerungen vorhanden
- Transferfenster-Regeln (Sommer/Winter) vorhanden
- Sponsorenmodul (Angebote, Vertragsabschluss, Bonus/weekly payout) vorhanden
- Stadion/Stadionumfeld (Basiswerte + Upgrade-Projekte) vorhanden
- Trainingslager (Planung + automatische Effekte) vorhanden
- Matchday-Finanzabrechnung (Einnahmen/Ausgaben je Spiel) vorhanden

### Welle 3 (Erweiterte Systeme)

- Jugendbereich
- Nationalteams
- Team of the Day
- Random Events

Status: `in progress` (Start umgesetzt, Jugend weiterhin ausgenommen)
- Nationalteams (Kaderansicht + Admin-Refresh) vorhanden
- Team of the Day (Generierung + Historie) vorhanden
- Random Events (Vorlagen, Trigger, Anwendung, Auto-Trigger im Matchday-Runner) vorhanden

### Welle 4 (Community)

- Nachrichten
- News
- Shoutbox
- Benachrichtigungen
- User-Badges / Hall of Fame / Online Users

### Welle 5 (Plattform, ohne Premium/Payment)

- SSO-Integrationen (ohne Social Login)
- Ads / RSS / Hilfesystem / Restmodule

## Technische Leitlinien fuer 1:1

- Business-Regeln aus OWS werden fachlich uebernommen, aber Laravel-konform modelliert.
- Keine 1:1 Portierung von altem PHP-Code; stattdessen saubere Service-/Domain-Schicht.
- Jede Welle endet mit:
  - Migrationen + Seed-Daten
  - Feature-Tests
  - Bedienbare UI (responsive)
  - ACP-Management fuer Admins

## Naechster Umsetzungsschritt

Welle 3 starten mit (ohne Jugend):

1. Nationalteams
2. Team of the Day
3. Random Events
