# Vite & Gourmand

Projet ECF - Développeur Web et Web Mobile

C'est un site pour le traiteur Vite & Gourmand à Bordeaux. On peut voir les menus, les filtrer, créer un compte et commander.
Il y a aussi un espace pour les employés (commandes, menus, plats, horaires, avis) et un espace admin (comptes employés et statistiques).

## Technos utilisées

- HTML, CSS, Bootstrap 5, JavaScript
- PHP 8 avec PDO
- MySQL (base relationnelle)
- MongoDB (pour les statistiques)
- PHPMailer pour les mails
- Chart.js pour le graphique
- Docker + fly.io pour le déploiement

## Ce qu'il faut avoir installé

- PHP 8.1 minimum avec les extensions pdo_mysql et mongodb
- Composer
- MySQL ou MariaDB (moi j'utilise XAMPP)
- MongoDB + mongosh (ou un cluster gratuit sur MongoDB Atlas)
- un compte Mailtrap pour tester les mails (c'est gratuit)

## Installer le projet en local

1. Cloner le projet

```
git clone https://github.com/widmaiervincent-dot/vite-et-gourmand.git
cd vite-et-gourmand
```

2. Installer les dépendances avec composer

```
composer install
```

3. Créer la base de donnée

Importer les 2 fichiers SQL dans cet ordre (avec phpMyAdmin ou en ligne de commande) :

```
mysql -u root -p < sql/creation_bdd.sql
mysql -u root -p < sql/donnees.sql
```

4. Initialiser MongoDB

```
mongosh "mongodb://localhost:27017/vite_gourmand" sql/mongo_init.js
```

5. La config

Tout est dans `config/config.php`. Par defaut c'est MySQL en `root` sans mot de passe sur localhost, comme XAMPP.
Pour les identifiants Mailtrap, copier `config/config.local.exemple.php` en `config/config.local.php` et mettre les siens dedans
(ce fichier n'est pas envoyé sur GitHub).

Sinon on peut utiliser des variables d'environnement :

- DB_HOST, DB_PORT, DB_NAME, DB_USER, DB_PASS -> MySQL
- MONGO_URI, MONGO_DB -> MongoDB
- SMTP_HOST, SMTP_PORT, SMTP_USER, SMTP_PASS -> les mails
- MAIL_ENTREPRISE -> l'adresse qui reçoit les messages du formulaire de contact
- URL_SITE -> l'adresse du site (pour les liens dans les mails)

6. Lancer le site

```
php -S localhost:8000
```

et aller sur http://localhost:8000

## Les comptes pour tester

| Rôle | Mail | Mot de passe |
|---|---|---|
| Admin | jose@vite-gourmand.fr | Admin@Jose2026 |
| Employé | julie@vite-gourmand.fr | Employe@Julie26 |
| Utilisateur | client.test@mail.fr | Client@Test2026 |

Le compte admin est créé directement dans le fichier SQL parce qu'on ne doit pas pouvoir créer un admin depuis le site.

## Les dossiers

```
api/                  -> le fichier appelé en JavaScript pour filtrer les menus
config/               -> connexion à la BDD et à MongoDB
css/                  -> le style
espace_admin/         -> pages de l'admin
espace_employe/       -> pages des employés
espace_utilisateur/   -> pages du client
images/               -> images des menus
includes/             -> header, footer, fonctions et mails
js/                   -> les scripts
sql/                  -> scripts de la base de données
docs/                 -> la documentation en PDF
```

## Sécurité

- requêtes préparées avec PDO (contre les injections SQL)
- htmlspecialchars sur ce qui est affiché (contre le XSS)
- mots de passe hachés avec password_hash
- token CSRF dans les formulaires
- vérification du rôle sur chaque page privée
- le prix est recalculé en PHP
- vérification des images uploadées
- les dossiers config, includes et sql sont bloqués avec le .htaccess

## Git

- `main` : la version en ligne
- `develop` : la branche de développement
- `feature/...` : une branche pour chaque fonctionnalité, créée depuis develop et mergée dans develop quand c'est testé

## Déploiement sur fly.io

```
fly launch
fly secrets set DB_HOST=... DB_USER=... DB_PASS=... DB_NAME=vite_gourmand MONGO_URI=... SMTP_USER=... SMTP_PASS=...
fly deploy
```

La base MySQL est sur Aiven et MongoDB sur MongoDB Atlas (offres gratuites).
Toutes les étapes sont expliquées dans la documentation technique.

## Documentation

Dans le dossier `docs/` :

- documentation_technique.pdf
- gestion_de_projet.pdf
- charte_graphique.pdf
- manuel_utilisation.pdf
- copie_ecf.pdf
- maquettes_figma/ : les maquettes en SVG à importer dans Figma
- sources_word/ : les documents en Word pour pouvoir les modifier
- diagrammes/ : les diagrammes
