# Vereins-Datenmodell vertiefen

**Priorität:** P1 · **Status:** ✅ Erledigt (2 Teile)

## Scope

- Erweiterte Vereinsfelder (Saisonziel, Historie, optionale Teamrollen)
- Saisondaten und Gesamt-/Allzeitdaten konsistent trennen
- Vereinsstatistiken vereinheitlichen (eine Quelle für Tabelle, Historie, Clubprofil)

## Akzeptanz

- Vereinsprofil bildet Saisonziel/Historie nachvollziehbar ab
- Saison- und Allzeitwerte sind getrennt und widerspruchsfrei

## Umsetzung

### Teil 1 – Saisonziel + Historie
- Vereinsfeld `season_objective` eingeführt (Klassenerhalt/Mittelfeld/Aufstieg/Meisterschaft/Pokalrunde).
- Club-Statistik um kontextgetrennte Auswertung erweitert.
- Saisonhistorie pro Verein im Clubprofil ergänzt.
- Tests: `tests/Feature/ClubStatisticsHistoryTest.php`

### Teil 2 – Teamrollen Captain/Vize
- Vereinsrollen `captain_player_id` und `vice_captain_player_id` als Felder eingeführt.
- Rollenpflege im ACP-Clubformular integriert.
- Tests: `tests/Feature/ClubTeamRolesTest.php`

## Referenzen

- `app/Models/Club.php`
- `app/Models/SeasonClubStatistic.php`
- `app/Services/LeagueTableService.php`
