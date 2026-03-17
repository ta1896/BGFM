# Websoccer Infografiken

Diese Datei fasst die strategische Richtung von NewGen / Websoccer in 3 GitHub-tauglichen Infografiken zusammen.

Sie ist bewusst so aufgebaut, dass sie:

- direkt in GitHub sauber lesbar ist
- als schnelle Produktuebersicht funktioniert
- intern fuer Planung, Diskussion und Priorisierung nutzbar ist

---

## 1. Produkt-Map

Diese Grafik zeigt die 10 Kernkonzepte als zusammenhaengendes System statt als lose Featureliste.

```mermaid
graph TD
    A[Websoccer Vision]

    A --> B[Dynamic Manager Career]
    A --> C[Board / Media / Fans]
    A --> D[Dressing Room / Hierarchy]
    A --> E[Club Philosophy]
    A --> F[Living Football World]
    A --> G[Youth Academy]
    A --> H[Transfer Market]
    A --> I[Matchday Core Loop]
    A --> J[Season Identity / History]
    A --> K[Club Infrastructure]

    B --> C
    B --> F
    C --> D
    C --> I
    D --> I
    D --> H
    E --> G
    E --> H
    E --> I
    F --> J
    F --> B
    G --> H
    G --> J
    H --> C
    H --> I
    I --> J
    K --> G
    K --> H
    K --> I
```

### Aussage

- Das Spiel sollte nicht aus isolierten Modulen bestehen.
- Die staerkste Richtung ist ein zusammenhaengendes Manager-Oekosystem.
- Matchday, Karriere, Transfers, Jugend und Clubentwicklung greifen direkt ineinander.

---

## 2. Roadmap-Logik

Diese Grafik zeigt, wie die Themen nach Umsetzungslogik priorisiert werden koennen.

```mermaid
flowchart LR
    A[Quick Wins]
    B[Mid-Term Features]
    C[Big Bets]

    A --> A1[Matchday as Emotional Core Loop]
    A --> A2[Cleaner Live Storytelling]
    A --> A3[Season Memory / Recaps]
    A --> A4[Stronger Core UX Identity]

    B --> B1[Club Philosophy]
    B --> B2[Dressing Room / Hierarchy]
    B --> B3[Board / Media / Fans]

    C --> C1[Dynamic Manager Career]
    C --> C2[Strategic Transfer Market]
    C --> C3[Living World + Youth + Infrastructure]

    A --> B
    B --> C
```

### Aussage

- Erst die Kernschleife staerken.
- Danach Management-Tiefe erweitern.
- Danach die grossen Langzeitsysteme ausrollen.

### Priorisierungsprinzip

| Phase | Fokus | Warum |
| --- | --- | --- |
| Quick Wins | Match-Erlebnis und UX | Hoher Impact bei vergleichsweise geringerem Risiko |
| Mid-Term | Manager-Tiefe | Macht Entscheidungen relevanter und Clubs unterscheidbarer |
| Big Bets | Langzeit-Identitaet | Schafft echte Produkt-DNA und Langzeitbindung |

---

## 3. Manager-Kernschleife

Diese Grafik zeigt, wie sich ein starker Spielrhythmus anfuehlen sollte.

```mermaid
flowchart TD
    A[Pre-Match]
    B[Matchday]
    C[Post-Match]
    D[Club Management]
    E[Season Progress]

    A --> A1[Opponent Analysis]
    A --> A2[Lineup / Tactics]
    A --> A3[Expectations / Pressure]

    B --> B1[Live Match Center]
    B --> B2[Ticker / Highlights]
    B --> B3[Decisions / Adjustments]

    C --> C1[Result]
    C --> C2[Morale / Fan Reaction]
    C --> C3[Media / Board Consequences]

    D --> D1[Transfers]
    D --> D2[Training]
    D --> D3[Youth]
    D --> D4[Infrastructure]

    E --> E1[Table / Objectives]
    E --> E2[Reputation]
    E --> E3[Storylines]

    A --> B --> C --> D --> E
    E --> A
```

### Aussage

- Der Spieltag ist das emotionale Zentrum.
- Alles andere sollte diese Schleife staerken, nicht ersetzen.
- Wenn diese Loop stark ist, traegt sie fast das gesamte Spiel.

---

## Kurzfazit

Wenn man die gesamte Produktidee in 3 Saetze herunterbricht:

1. Websoccer sollte sich wie eine lebendige Fussballwelt anfuehlen, nicht nur wie ein Verwaltungsmenue.
2. Der Spieltag muss emotional und visuell das Herz des Spiels sein.
3. Karriere, Transfers, Jugend und Clubidentitaet muessen langfristig zu einer grossen Managerreise verschmelzen.

---

## Empfehlte Nutzung auf GitHub

Diese Datei eignet sich gut fuer:

- `README`-Verlinkung
- interne Produktdiskussionen
- Issue-Planung
- Roadmap-Threads
- Discord-Posts mit GitHub-Link

### Sinnvolle Verlinkungen

- [`WEBSOCCER_10_KONZEPTE.md`](/c:/Users/akden/Documents/NewGen/docs/WEBSOCCER_10_KONZEPTE.md)
- [`README.md`](/c:/Users/akden/Documents/NewGen/README.md)
