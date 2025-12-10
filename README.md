# Recipe Docker - PHP/Symfony

Configuration Docker professionnelle pour un projet PHP/Symfony avec Apache, PHP 8.4 et MariaDB.

## 🚀 Stack Technique

- **PHP** : 8.4 avec Apache (mod_rewrite activé)
- **Base de données** : MariaDB 11.3
- **Extensions PHP** : GD, Intl, MySQLi, PDO, PDO_MySQL, Opcache
- **Outils** : Composer 2, Symfony CLI (dernière version), Node.js (via NVM - dernière version), Xdebug

## 📋 Prérequis

- Docker Engine 20.10+
- Docker Compose 2.0+
- Git

## 🏗️ Structure du Projet

\`\`\`
.
├── apache/
│   ├── Dockerfile          # Image Apache/PHP personnalisée
│   └── custom-php.ini      # Configuration PHP personnalisée
├── db/
│   ├── backup.sh           # Script de sauvegarde
│   ├── restore.sh          # Script de restauration
│   └── init.sql            # Scripts SQL d'initialisation (optionnel)
├── www/                    # Code source de l'application
├── docker-compose.yml      # Configuration Docker Compose
├── .env                    # Configuration locale (ignoré par Git)
├── .env.example            # Modèle de configuration
├── .htaccess              # Configuration Apache
├── aliases.sh             # Aliases pour faciliter l'utilisation
└── README.md              # Ce fichier
\`\`\`

## 🚦 Démarrage Rapide

### 1. Configuration de l'environnement

Le fichier \`.env\` a été généré automatiquement avec vos paramètres. Vous pouvez le modifier si nécessaire.

**⚠️ Important** : Le fichier \`.env\` est automatiquement ignoré par Git. Ne commitez **JAMAIS** le fichier \`.env\` dans Git car il contient des informations sensibles.

### 2. Construction et démarrage

\`\`\`bash
# Construire les images et démarrer les containers
docker compose up -d --build

# Vérifier l'état des containers
docker compose ps

# Voir les logs
docker compose logs -f
\`\`\`

### 3. Accès aux services

- **Application web** : http://localhost:8083
- **MariaDB** : localhost:3308
  - Utilisateur root : \`root\` / Mot de passe : défini dans \`.env\`
  - Utilisateur : défini dans \`.env\`

### 4. Charger les aliases

\`\`\`bash
source aliases.sh
\`\`\`

### Commandes utiles

#### Avec les aliases (plus rapide)

\`\`\`bash
# Composer (installation de dépendances)
ccomposer install
ccomposer require package/name
# Symfony Console
cconsole cache:clear
cconsole doctrine:migrations:migrate
# Accéder aux containers
capache    # Entrer dans le container Apache
cmariadb   # Entrer dans le container MariaDB

# Base de données
db-export  # Sauvegarder la base de données
db-import  # Restaurer la base de données
\`\`\`

#### Sans aliases (avec docker compose exec)

\`\`\`bash
# Composer
docker compose exec apache_webservice composer install
docker compose exec apache_webservice composer require package/name

# Accéder aux containers
docker compose exec -it apache_webservice bash
docker compose exec -it mariadb_webservice bash

# Base de données
docker compose exec mariadb_webservice /docker-entrypoint-initdb.d/backup.sh
docker compose exec mariadb_webservice /docker-entrypoint-initdb.d/restore.sh
\`\`\`

## 🎯 Configuration Symfony

### Installation d'un nouveau projet Symfony

```bash
# Charger les aliases (si pas déjà fait)
source aliases.sh

# Créer un nouveau projet Symfony 8 directement dans www (depuis la racine)
ccomposer create-project symfony/skeleton:"8.0.x" ./

# Installer les dépendances supplémentaires
ccomposer require symfony/orm-pack
ccomposer require symfony/maker-bundle --dev

# Déplacer le fichier .htaccess dans le dossier public de Symfony
mv .htaccess www/public/.htaccess
```

### Commandes Symfony principales

#### Avec les aliases (recommandé)

```bash
# Cache
cconsole cache:clear
cconsole cache:warmup

# Base de données
cconsole doctrine:database:create
cconsole doctrine:migrations:migrate
cconsole doctrine:migrations:status
cconsole doctrine:schema:update --force

# Génération de code
cconsole make:controller NomDuController
cconsole make:entity NomDeLEntity
cconsole make:form NomDuForm
cconsole make:command NomDeLaCommande

# Debug et informations
cconsole debug:router
cconsole debug:container
cconsole debug:autowiring
cconsole about
```

#### Sans aliases (avec docker compose exec)

```bash
# Cache
docker compose exec apache_webservice symfony console cache:clear
docker compose exec apache_webservice symfony console cache:warmup

# Base de données
docker compose exec apache_webservice symfony console doctrine:database:create
docker compose exec apache_webservice symfony console doctrine:migrations:migrate
docker compose exec apache_webservice symfony console doctrine:migrations:status
docker compose exec apache_webservice symfony console doctrine:schema:update --force

# Génération de code
docker compose exec apache_webservice symfony console make:controller NomDuController
docker compose exec apache_webservice symfony console make:entity NomDeLEntity
docker compose exec apache_webservice symfony console make:form NomDuForm
docker compose exec apache_webservice symfony console make:command NomDeLaCommande

# Debug et informations
docker compose exec apache_webservice symfony console debug:router
docker compose exec apache_webservice symfony console debug:container
docker compose exec apache_webservice symfony console debug:autowiring
docker compose exec apache_webservice symfony console about
```
## 🔒 Sécurité

### Bonnes pratiques implémentées

✅ **Réseau isolé** : Les services communiquent via un réseau Docker privé  
✅ **Healthchecks** : Vérification automatique de la santé des containers  
✅ **Variables d'environnement** : Mots de passe configurables via \`.env\`  
✅ **Limites de ressources** : Contrôle de la mémoire et CPU  
✅ **Versions fixées** : Images Docker versionnées pour la reproductibilité  
✅ **.dockerignore** : Exclusion des fichiers inutiles du contexte de build  

### Recommandations de sécurité

1. **Toujours utiliser \`.env.example\` comme modèle** : Copiez-le en \`.env\` et modifiez les valeurs
2. **Ne jamais commiter le fichier \`.env\`** dans Git (déjà configuré dans \`.gitignore\`)
3. **Utiliser des mots de passe forts** en production
4. **Limiter l'exposition des ports** en production (utiliser un reverse proxy)
5. **Désactiver Xdebug** en production (modifier le Dockerfile)
6. **Vérifier que \`.env\` est bien ignoré** : \`git status\` ne doit pas lister \`.env\`

## 📊 Gestion de la Base de Données

### Sauvegarde

\`\`\`bash
# Via alias
db-export

# Ou directement
docker compose exec mariadb_webservice /docker-entrypoint-initdb.d/backup.sh
\`\`\`

Le fichier de sauvegarde sera créé dans \`./db/init.sql\` sur l'hôte.

### Restauration

\`\`\`bash
# Via alias
db-import

# Ou directement
docker compose exec mariadb_webservice /docker-entrypoint-initdb.d/restore.sh
\`\`\`

### Scripts SQL d'initialisation

Placez vos scripts SQL dans le dossier \`./db/\`. Ils seront automatiquement exécutés au premier démarrage de MariaDB.

## 🐛 Débogage avec Xdebug

Xdebug est installé et configuré. Pour l'utiliser avec VSCode :

1. Décommentez les lignes dans \`apache/custom-php.ini\` :
\`\`\`ini
xdebug.client_host = host.docker.internal
xdebug.client_port = 9003
xdebug.start_with_request = yes
xdebug.idekey = VSCODE
\`\`\`

2. Configurez VSCode avec \`.vscode/launch.json\` :
\`\`\`json
{
  "version": "0.2.0",
  "configurations": [
    {
      "name": "Listen for Xdebug",
      "type": "php",
      "request": "launch",
      "port": 9003,
      "pathMappings": {
        "/var/www/html": "${workspaceFolder}/www"
      }
    }
  ]
}
\`\`\`

## ⚙️ Configuration PHP

Le fichier \`apache/custom-php.ini\` contient les paramètres personnalisés :

- Limites d'upload : 100M
- Mémoire : 256M
- Timeout d'exécution : 300s
- Timezone : Europe/Paris

Modifiez selon vos besoins.

## 📝 Notes de Production

Avant de déployer en production :

1. **Désactiver le mode debug** : \`PHP_DISPLAY_ERRORS=Off\` dans \`.env\`
2. **Désactiver Xdebug** dans le Dockerfile
3. **Utiliser un reverse proxy** (Nginx/Traefik) au lieu d'exposer directement le port 80
4. **Configurer des sauvegardes automatiques** de la base de données
5. **Utiliser HTTPS** avec un certificat SSL

## 📚 Ressources

- [Documentation Docker Compose](https://docs.docker.com/compose/)
- [Documentation PHP](https://www.php.net/docs.php)
- [Documentation MariaDB](https://mariadb.com/docs/)

## 📄 Licence

Ce template est fourni tel quel pour vos projets.

---

**Créé avec ❤️ par php-docker-generator**