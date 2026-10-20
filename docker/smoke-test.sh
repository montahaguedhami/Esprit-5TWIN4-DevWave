#!/bin/sh
# Test de fumée de la plateforme Docker : santé, assets, connexion par rôle, isolation des rôles.
#   sh docker/smoke-test.sh                       (après docker compose up -d)
#   BASE=http://localhost:9090 sh docker/smoke-test.sh
BASE=${BASE:-http://localhost:8088}
MDP=${DEMO_PASSWORD:-password123}
J=$(mktemp -d)
echecs=0

verifier() { # libellé attendu obtenu
    if [ "$2" = "$3" ]; then echo "  ok    $1 ($3)"; else echo "  ÉCHEC $1 : attendu $2, obtenu $3"; echecs=$((echecs + 1)); fi
}

connexion() { # email mot_de_passe → URL de redirection
    rm -f "$J/c"
    jeton=$(curl -s -c "$J/c" -b "$J/c" "$BASE/login" | grep -o 'name="_token" value="[^"]*"' | head -1 | sed 's/.*value="//;s/"//')
    curl -s -o /dev/null -w '%{redirect_url}' -c "$J/c" -b "$J/c" \
        --data-urlencode "_token=$jeton" --data-urlencode "email=$1" --data-urlencode "password=$2" "$BASE/login"
}

code() { curl -s -o /dev/null -w '%{http_code}' -b "$J/c" "$BASE$1"; }

echo "Test de fumée sur $BASE"
verifier "santé /up" 200 "$(code /up)"
css=$(curl -s "$BASE/login" | grep -o '/build/assets/[^"]*\.css' | head -1)
verifier "CSS compilé servi" 200 "$( [ -n "$css" ] && code "$css" || echo absent)"
verifier "mauvais mot de passe refusé" "$BASE/login" "$(connexion admin@aquasecure.tn mauvais-mot-de-passe)"

verifier "connexion administrateur" "$BASE/admin/dashboard" "$(connexion admin@aquasecure.tn "$MDP")"
verifier "  tableau de bord admin" 200 "$(code /admin/dashboard)"
verifier "connexion gestionnaire" "$BASE/manager/dashboard" "$(connexion gestionnaire@aquasecure.tn "$MDP")"
verifier "  interventions" 200 "$(code /manager/interventions)"
verifier "  planning" 200 "$(code /manager/planning)"
verifier "  statistiques maintenance" 200 "$(code /manager/maintenance/statistiques)"
verifier "  accès admin interdit" 403 "$(code /admin/dashboard)"
verifier "connexion technicien" "$BASE/technician/dashboard" "$(connexion amira@aquasecure.tn "$MDP")"
verifier "  mes interventions" 200 "$(code /technician/interventions)"
verifier "connexion citoyen" "$BASE/citizen/dashboard" "$(connexion citoyen@aquasecure.tn "$MDP")"
verifier "  accès gestionnaire interdit" 403 "$(code /manager/dashboard)"

rm -rf "$J"
if [ "$echecs" -gt 0 ]; then echo "$echecs vérification(s) en échec"; exit 1; fi
echo "Toutes les vérifications sont passées"
