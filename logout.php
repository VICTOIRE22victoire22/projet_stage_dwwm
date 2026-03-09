<?php

//Démarrer une cession PHP
session_start();

// Détruit les variables d'une session
// session_unset();

//Détruire la cession
session_destroy();

//Redirection vers la page de connexion
header("Location: login.php?msg=1");
exit();

?>