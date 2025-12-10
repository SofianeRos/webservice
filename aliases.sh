# Charger les variables d'environnement depuis .env si le fichier existe
if [ -f .env ]; then
  set -a
  source .env 2>/dev/null || {
    export $(grep -v '^#' .env | grep -v '^$' | grep -v '^[[:space:]]*$' | xargs)
  }
  set +a
fi

# Noms des containers (avec valeurs par défaut si .env n'existe pas)
APACHE_CONTAINER="${APACHE_CONTAINER:-apache_webservice}"
MARIADB_CONTAINER="${MARIADB_CONTAINER:-mariadb_webservice}"

# alias pour installer une librairie composer
alias ccomposer='docker compose exec ${APACHE_CONTAINER} composer'
# alias pour utiliser le wizard symfony
alias cconsole='docker compose exec ${APACHE_CONTAINER} symfony console'

# alias pour entrer dans le container Apache (interactif avec -it)
alias capache='docker compose exec -it ${APACHE_CONTAINER} bash'

# alias pour entrer dans le container MariaDB (interactif avec -it)
alias cmariadb='docker compose exec -it ${MARIADB_CONTAINER} bash'

# alias pour exporter un snap de la base de données
alias db-export='docker compose exec ${MARIADB_CONTAINER} /docker-entrypoint-initdb.d/backup.sh'

# alias pour importer un snap de la base de données
alias db-import='docker compose exec ${MARIADB_CONTAINER} /docker-entrypoint-initdb.d/restore.sh'