#!/bin/sh
# Vérification de la supervision : cibles Prometheus, métriques, règles d'alerte, provisionnement Grafana.
#   docker compose --profile supervision up -d && sh docker/supervision-check.sh
PROM=${PROMETHEUS_URL:-http://localhost:9090}
GRAF=${GRAFANA_URL:-http://localhost:3000}
GRAF_AUTH=${GRAFANA_AUTH:-admin:admin}
CIBLES_ATTENDUES=${CIBLES_ATTENDUES:-5}
echecs=0

verifier() { # libellé condition(0/1)
    if [ "$2" = "0" ]; then echo "  ok    $1"; else echo "  ÉCHEC $1"; echecs=$((echecs + 1)); fi
}

echo "Supervision : Prometheus $PROM, Grafana $GRAF"

# 1. Toutes les cibles collectées (jusqu'à 2 minutes : premier passage toutes les 15 s)
essais=0
while :; do
    cibles=$(curl -s -m 5 "$PROM/api/v1/targets?state=active")
    up=$(printf '%s' "$cibles" | grep -o '"health":"up"' | wc -l | tr -d ' ')
    [ "$up" -ge "$CIBLES_ATTENDUES" ] && break
    essais=$((essais + 1))
    [ "$essais" -ge 24 ] && break
    sleep 5
done
verifier "cibles Prometheus actives : $up / $CIBLES_ATTENDUES" "$([ "$up" -ge "$CIBLES_ATTENDUES" ]; echo $?)"
printf '%s' "$cibles" | grep -o '"lastError":"[^"]\+"' | sed 's/^/        /'

requete() { curl -s -m 5 --data-urlencode "query=$1" "$PROM/api/v1/query"; }

# 2. Métriques de l'application et de la base réellement collectées
verifier "aquasecure_database_up = 1" "$(requete 'aquasecure_database_up' | grep -q '"value":\[[0-9.]*,"1"\]'; echo $?)"
verifier "mysql_up = 1 (mysqld-exporter)" "$(requete 'mysql_up' | grep -q '"value":\[[0-9.]*,"1"\]'; echo $?)"
verifier "sondes HTTP /up et /login en succès" "$(requete 'sum(probe_success)' | grep -q '"value":\[[0-9.]*,"2"\]'; echo $?)"
verifier "métriques métier présentes (interventions)" "$(requete 'count(aquasecure_interventions_total)' | grep -q '"result":\[{'; echo $?)"

# 3. Règles d'alerte chargées
regles=$(curl -s -m 5 "$PROM/api/v1/rules" | grep -o '"type":"alerting"' | wc -l | tr -d ' ')
verifier "règles d'alerte chargées : $regles" "$([ "$regles" -ge 7 ]; echo $?)"

# 4. Grafana : santé, source Prometheus et tableau de bord provisionnés
verifier "Grafana opérationnel" "$(curl -s -m 5 "$GRAF/api/health" | grep -q '"database": *"ok"'; echo $?)"
verifier "source de données Prometheus provisionnée" "$(curl -s -m 5 -u "$GRAF_AUTH" "$GRAF/api/datasources/uid/prometheus" | grep -q '"type":"prometheus"'; echo $?)"
verifier "tableau de bord « Supervision AquaSecure » provisionné" "$(curl -s -m 5 -u "$GRAF_AUTH" "$GRAF/api/dashboards/uid/aquasecure" | grep -q '"title":"Supervision AquaSecure"'; echo $?)"
verifier "requête Grafana → Prometheus fonctionnelle" "$(curl -s -m 10 -u "$GRAF_AUTH" "$GRAF/api/datasources/uid/prometheus/resources/api/v1/query?query=up" | grep -q '"status":"success"'; echo $?)"

if [ "$echecs" -gt 0 ]; then echo "$echecs vérification(s) en échec"; exit 1; fi
echo "Supervision opérationnelle"
