<?php

    // Fonction de nettoyage des données saisies dans le formulaire
    function valide_donnees($donnees) {
        $donnees = trim($donnees); // suppression des espaces inutiles
        $donnees = stripslashes($donnees); // suppression des antislashes
        $donnees = strip_tags($donnees); // suppression des balises HTML et PHP
        $donnees = htmlspecialchars($donnees); // sécurise l'affichage contre le XSS

        return $donnees;
    }
?>





















