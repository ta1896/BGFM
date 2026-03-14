# Authentifizierung

OpenWS unterstützt zwei Wege zur Anmeldung: klassisches Passwort-Login und moderne Passkeys (WebAuthn / FIDO2).

## Laravel Fortify

Das Basis-Auth-System basiert auf **Laravel Fortify**, das die folgenden Features bereitstellt:
- Login / Logout
- Registrierung
- Passwort-Zurücksetzen
- E-Mail-Verifizierung

Die zugehörigen Routen werden automatisch in `routes/auth.php` registriert.

## Passkeys (WebAuthn)

OpenWS unterstützt **passwortlose Anmeldung via Passkey** (Biometrie, Hardware-Keys) über das Paket `laragear/webauthn`.

```php
// routes/web.php – Routen werden automatisch registriert
\Laragear\WebAuthn\Http\Routes::routes();
```

Passkeys werden in der Tabelle `webauthn_credentials` gespeichert.

## Benutzerrollen

Das System kennt zwei Rollen:
- **User / Manager**: Kann einen Verein verwalten, spielen und auf normale Funktionen zugreifen.
- **Admin**: Hat zusätzlichen Zugang zum ACP (Admin Control Panel) unter `/acp`.

```php
// Middleware-Prüfung (app/Http/Middleware/IsAdmin.php)
if (!$request->user()->is_admin) {
    abort(403);
}
```
