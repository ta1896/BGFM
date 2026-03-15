docker compose exec -T app php artisan migrate:fresh --seed --force
if ($LASTEXITCODE -ne 0) { exit $LASTEXITCODE }

Write-Output "Demo-Daten auf frischer Datenbank erfolgreich eingespielt."
