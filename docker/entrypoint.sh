#!/bin/sh
# Démarrage du conteneur : attente de la base, migrations, données de démo, caches Laravel
set -e
cd /var/www/html

if [ -z "$APP_KEY" ]; then
    echo "[entrypoint] ERREUR : APP_KEY n'est pas défini (voir docker-compose.yml)." >&2
    exit 1
fi

echo "[entrypoint] Attente de la base ${DB_HOST}:${DB_PORT:-3306}…"
essais=0
until php -r '
    try {
        new PDO("mysql:host=".getenv("DB_HOST").";port=".(getenv("DB_PORT") ?: 3306).";dbname=".getenv("DB_DATABASE"),
                getenv("DB_USERNAME"), getenv("DB_PASSWORD"));
    } catch (Throwable $e) { exit(1); }' 2>/dev/null
do
    essais=$((essais + 1))
    if [ "$essais" -ge 60 ]; then
        echo "[entrypoint] ERREUR : base de données injoignable." >&2
        exit 1
    fi
    sleep 2
done

if [ "${RUN_MIGRATIONS:-true}" = "true" ]; then
    php artisan migrate --force
fi

# Données de démonstration : seulement au premier démarrage (table users vide)
if [ "${SEED_DEMO_DATA:-true}" = "true" ]; then
    utilisateurs=$(php -r '
        $pdo = new PDO("mysql:host=".getenv("DB_HOST").";port=".(getenv("DB_PORT") ?: 3306).";dbname=".getenv("DB_DATABASE"),
                       getenv("DB_USERNAME"), getenv("DB_PASSWORD"));
        echo $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();')
    if [ "$utilisateurs" = "0" ]; then
        echo "[entrypoint] Base vide : chargement des données de démonstration…"
        php artisan db:seed --force
    fi
fi

php artisan optimize

exec "$@"
