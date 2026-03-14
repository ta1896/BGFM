# Transfers & Leihen

Das Transfer- und Leihsystem ermöglicht es Managern, Spieler anzubieten, zu kaufen und auszuleihen.

## Transfermarkt

1. **Angebot erstellen**: Ein Manager stellt einen Spieler mit einem Mindestpreis auf den Markt (`TransferListing`).
2. **Gebot abgeben**: Andere Manager können Gebote (`TransferBid`) platzieren.
3. **Angebot annehmen / ablehnen**: Der anbietende Manager entscheidet, welches Gebot er annimmt.
4. **Transfer abschließen**: Nach Annahme wird der Spieler dem neuen Verein zugewiesen und das Budget angepasst.

```
POST /transfers/listings                  → Angebot erstellen
POST /transfers/listings/{id}/bids        → Gebot abgeben
POST /transfers/listings/{id}/accept/{bid} → Transfer abschließen
```

## Leihmarkt

Der Leihmarkt funktioniert analog zum Transfermarkt, jedoch mit einem Leihzeitraum und einer optionalen **Kaufoption**.

```
POST /loans/listings                     → Leihangebot erstellen
POST /loans/{loan}/option/exercise       → Kaufoption ausüben
POST /loans/{loan}/option/decline        → Kaufoption ablehnen
```

## Verträge

Spielerverträge werden in der Tabelle `contracts` verwaltet. Manager können Verträge über `/contracts` einsehen und Verlängerungen anfordern:

```
GET  /contracts                    → Übersicht aller Spielerverträge
POST /contracts/{player}/renew     → Vertragsverlängerung anstoßen
```
