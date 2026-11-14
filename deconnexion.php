<?php
// on passe par fonctions.php pour utiliser les sessions enregistrées en base
require_once 'includes/fonctions.php';
$_SESSION = [];
session_destroy();
header('Location: /index.php');
exit;
