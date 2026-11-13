<?php
// Exemple de configuration locale.
// Copier ce fichier en "config.local.php" et mettre ses propres identifiants.
// config.local.php est dans le .gitignore : il ne part jamais sur GitHub.

putenv('SMTP_USER=identifiant_mailtrap');
putenv('SMTP_PASS=mot_de_passe_mailtrap');

// si MySQL n'est pas sur le port par défaut (3306)
// putenv('DB_PORT=3307');
