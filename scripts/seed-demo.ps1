docker compose exec -T app php artisan migrate --force
if ($LASTEXITCODE -ne 0) { exit $LASTEXITCODE }

docker compose exec -T app php artisan db:seed --force
if ($LASTEXITCODE -ne 0) { exit $LASTEXITCODE }

Write-Output "Demo-Daten erfolgreich eingespielt."
