# Installation

Um OpenWS zu installieren, benötigst du einige Voraussetzungen auf deinem Server oder lokalen Rechner.

## Systemvoraussetzungen
- **PHP** 8.2 oder höher
- **Datenbank**: MySQL 8.0+ oder MariaDB 10.5+
- **Composer**: Für PHP Abhängigkeiten
- **Node.js & NPM**: Für Frontend Assets (Tailwind, React, Vite)

## Schritte zur Installation

1. **Repository klonen**
   \`\`\`bash
   git clone https://github.com/dein-repo/openws.git
   cd openws
   \`\`\`

2. **Abhängigkeiten installieren**
   \`\`\`bash
   composer install
   npm install
   \`\`\`

3. **Umgebungsvariablen konfigurieren**
   Kopiere die Beispiel-Konfiguration und passe die Datenbankzugangsdaten in der `.env` Datei an.
   \`\`\`bash
   cp .env.example .env
   php artisan key:generate
   \`\`\`

4. **Datenbank migrieren und Assets kompilieren**
   \`\`\`bash
   php artisan migrate --seed
   npm run build
   \`\`\`

## Nächste Schritte
Nachdem du die Installation abgeschlossen hast, kannst du dich mit den [Spielmechaniken](/mechanics/training) vertraut machen oder die [Administration](/admin/settings) konfigurieren.
